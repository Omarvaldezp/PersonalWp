// =============================================
// Supabase Configuration
// OmarValdez.com
// =============================================

// INSTRUCCIONES:
// 1. Crea una cuenta gratis en https://supabase.com
// 2. Crea un nuevo proyecto
// 3. Ve a Settings > API y copia tu URL y anon key
// 4. Reemplaza los valores de abajo

const SUPABASE_URL = 'https://mogmgtnkyazpsgslucrx.supabase.co';
const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im1vZ21ndG5reWF6cHNnc2x1Y3J4Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzA4NDI0MTIsImV4cCI6MjA4NjQxODQxMn0.msfLGeM2tlAHlL101tA4tFiGqmoenHcgJWN_cUl3lKc';

// Inicializar cliente Supabase
const db = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

// =============================================
// API Functions — Blog Posts
// =============================================

async function getPosts(category = 'all', limit = 10) {
    let query = db
        .from('posts')
        .select('*')
        .eq('published', true)
        .order('created_at', { ascending: false })
        .limit(limit);

    if (category !== 'all') {
        query = query.eq('category', category);
    }

    const { data, error } = await query;
    if (error) { console.error('Error fetching posts:', error); return []; }
    return data;
}

async function getPostBySlug(slug) {
    const { data, error } = await db
        .from('posts')
        .select('*')
        .eq('slug', slug)
        .eq('published', true)
        .single();

    if (error) { console.error('Error fetching post:', error); return null; }
    return data;
}

// =============================================
// API Functions — Courses
// =============================================

async function getCourses() {
    const { data, error } = await db
        .from('courses')
        .select('*')
        .eq('active', true)
        .order('sort_order');

    if (error) { console.error('Error fetching courses:', error); return []; }
    return data;
}

// =============================================
// API Functions — Research
// =============================================

async function getResearch(category = 'all') {
    let query = db
        .from('research')
        .select('*')
        .eq('active', true)
        .order('year', { ascending: false });

    if (category !== 'all') {
        query = query.eq('category', category);
    }

    const { data, error } = await query;
    if (error) { console.error('Error fetching research:', error); return []; }
    return data;
}

// =============================================
// API Functions — Contact Form
// =============================================

async function submitContact(formData) {
    const { data, error } = await db
        .from('contacts')
        .insert([{
            name: formData.name,
            email: formData.email,
            type: formData.type,
            message: formData.message
        }]);

    if (error) { console.error('Error submitting contact:', error); return false; }
    return true;
}

// =============================================
// API Functions — Newsletter
// =============================================

async function subscribeNewsletter(email) {
    const { data, error } = await db
        .from('subscribers')
        .insert([{ email, active: true }]);

    // Código 23505 = email duplicado (ya está suscrito), lo tratamos como éxito
    if (error) {
        if (error.code === '23505') return true;
        console.error('Error subscribing:', error);
        return false;
    }
    return true;
}

// =============================================
// API Functions — Site Stats
// =============================================

async function getSiteStats() {
    const { data, error } = await db
        .from('site_stats')
        .select('*')
        .order('sort_order');

    if (error) { console.error('Error fetching stats:', error); return []; }
    return data;
}

// =============================================
// API Functions — Image Upload (Supabase Storage)
// =============================================

async function uploadImage(file, folder = 'blog') {
    const fileExt = file.name.split('.').pop();
    const fileName = `${folder}/${Date.now()}-${Math.random().toString(36).substr(2, 9)}.${fileExt}`;

    const { data, error } = await db.storage
        .from('images')
        .upload(fileName, file, {
            cacheControl: '3600',
            upsert: false
        });

    if (error) { console.error('Error uploading image:', error); return null; }

    // Get public URL
    const { data: urlData } = db.storage.from('images').getPublicUrl(fileName);
    return urlData.publicUrl;
}

async function deleteImage(imageUrl) {
    if (!imageUrl) return;
    // Extract path from URL
    const parts = imageUrl.split('/storage/v1/object/public/images/');
    if (parts.length < 2) return;
    const path = parts[1];

    const { error } = await db.storage.from('images').remove([path]);
    if (error) console.error('Error deleting image:', error);
}

// =============================================
// API Functions — Post Reactions (Like/Dislike)
// =============================================

async function reactToPost(postId, type) {
    // type: 'like' o 'dislike'
    //
    // Antes esto leía el contador y luego escribía valor+1 con .update().
    // Tenía dos problemas: las políticas RLS no permiten que un visitante
    // escriba en posts, así que el voto nunca se guardaba y el lector veía
    // un agradecimiento falso; y aun con permiso, dos votos simultáneos
    // perdían uno, porque entre la lectura y la escritura cabe otro voto.
    //
    // Ahora lo resuelve la función reaccionar_post de la base, que suma de
    // forma atómica y es la única vía por la que un visitante puede tocar
    // la tabla posts.
    const { data, error } = await db.rpc('reaccionar_post', {
        post_id: postId,
        tipo: type
    });

    if (error) { console.error('Error al registrar la reacción:', error); return null; }
    return data && data[0] ? data[0] : null;
}

// Exportar funciones
window.API = {
    getPosts,
    getPostBySlug,
    getCourses,
    getResearch,
    submitContact,
    subscribeNewsletter,
    getSiteStats,
    uploadImage,
    deleteImage,
    reactToPost
};
