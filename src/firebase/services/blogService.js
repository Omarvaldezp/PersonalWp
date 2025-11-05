/**
 * Blog Service - Operaciones CRUD para blog posts
 */

import {
  collection,
  doc,
  getDocs,
  getDoc,
  addDoc,
  updateDoc,
  deleteDoc,
  query,
  where,
  orderBy,
  limit,
  Timestamp
} from 'firebase/firestore';
import { db } from '../firebase.js';

const COLLECTION = 'blog_posts';

/**
 * Obtener todos los posts publicados
 * @param {Object} options - Opciones de filtrado
 * @returns {Promise<Array>}
 */
export async function getAllPosts(options = {}) {
  try {
    const {
      categoria = null,
      limite = 50,
      ordenarPor = 'fecha_publicacion',
      orden = 'desc'
    } = options;

    let q = query(
      collection(db, COLLECTION),
      where('estado', '==', 'publicado')
    );

    // Filtrar por categoría
    if (categoria) {
      q = query(q, where('categorias', 'array-contains', categoria));
    }

    // Ordenar
    q = query(q, orderBy(ordenarPor, orden));

    // Límite
    if (limite) {
      q = query(q, limit(limite));
    }

    const snapshot = await getDocs(q);
    return snapshot.docs.map(doc => ({
      id: doc.id,
      ...doc.data(),
      fecha_publicacion: doc.data().fecha_publicacion?.toDate()
    }));
  } catch (error) {
    console.error('Error obteniendo posts:', error);
    throw error;
  }
}

/**
 * Obtener un post por ID
 * @param {string} postId
 * @returns {Promise<Object|null>}
 */
export async function getPostById(postId) {
  try {
    const docRef = doc(db, COLLECTION, postId);
    const docSnap = await getDoc(docRef);

    if (docSnap.exists()) {
      return {
        id: docSnap.id,
        ...docSnap.data(),
        fecha_publicacion: docSnap.data().fecha_publicacion?.toDate()
      };
    }
    return null;
  } catch (error) {
    console.error('Error obteniendo post:', error);
    throw error;
  }
}

/**
 * Obtener posts por categoría
 * @param {string} categoria
 * @param {number} limite
 * @returns {Promise<Array>}
 */
export async function getPostsByCategoria(categoria, limite = 20) {
  return getAllPosts({ categoria, limite });
}

/**
 * Buscar posts por texto
 * @param {string} searchText
 * @returns {Promise<Array>}
 */
export async function searchPosts(searchText) {
  try {
    // Nota: Firestore no tiene full-text search nativo
    // Esta es una búsqueda simple. Para búsqueda avanzada,
    // considera usar Algolia o Meilisearch (ambos tienen tier gratuito)

    const snapshot = await getDocs(
      query(
        collection(db, COLLECTION),
        where('estado', '==', 'publicado')
      )
    );

    const searchLower = searchText.toLowerCase();

    return snapshot.docs
      .map(doc => ({
        id: doc.id,
        ...doc.data(),
        fecha_publicacion: doc.data().fecha_publicacion?.toDate()
      }))
      .filter(post =>
        post.titulo.toLowerCase().includes(searchLower) ||
        post.extracto?.toLowerCase().includes(searchLower) ||
        post.categorias?.some(cat => cat.toLowerCase().includes(searchLower))
      );
  } catch (error) {
    console.error('Error buscando posts:', error);
    throw error;
  }
}

/**
 * Obtener categorías únicas
 * @returns {Promise<Array>}
 */
export async function getCategorias() {
  try {
    const snapshot = await getDocs(
      query(
        collection(db, COLLECTION),
        where('estado', '==', 'publicado')
      )
    );

    const categoriasSet = new Set();

    snapshot.docs.forEach(doc => {
      const categorias = doc.data().categorias || [];
      categorias.forEach(cat => categoriasSet.add(cat));
    });

    return Array.from(categoriasSet).sort();
  } catch (error) {
    console.error('Error obteniendo categorías:', error);
    throw error;
  }
}

/**
 * Crear nuevo post (requiere autenticación)
 * @param {Object} postData
 * @returns {Promise<string>} ID del post creado
 */
export async function createPost(postData) {
  try {
    const docRef = await addDoc(collection(db, COLLECTION), {
      titulo: postData.titulo,
      slug: postData.slug || generarSlug(postData.titulo),
      extracto: postData.extracto || '',
      contenido: postData.contenido,
      imagen_portada: postData.imagen_portada || '',
      categorias: postData.categorias || [],
      etiquetas: postData.etiquetas || [],
      estado: postData.estado || 'borrador',
      fecha_publicacion: postData.fecha_publicacion || Timestamp.now(),
      visitas: 0,
      likes: 0,
      created_at: Timestamp.now(),
      updated_at: Timestamp.now()
    });

    return docRef.id;
  } catch (error) {
    console.error('Error creando post:', error);
    throw error;
  }
}

/**
 * Actualizar post (requiere autenticación)
 * @param {string} postId
 * @param {Object} updates
 * @returns {Promise<void>}
 */
export async function updatePost(postId, updates) {
  try {
    const docRef = doc(db, COLLECTION, postId);
    await updateDoc(docRef, {
      ...updates,
      updated_at: Timestamp.now()
    });
  } catch (error) {
    console.error('Error actualizando post:', error);
    throw error;
  }
}

/**
 * Eliminar post (requiere autenticación)
 * @param {string} postId
 * @returns {Promise<void>}
 */
export async function deletePost(postId) {
  try {
    await deleteDoc(doc(db, COLLECTION, postId));
  } catch (error) {
    console.error('Error eliminando post:', error);
    throw error;
  }
}

/**
 * Incrementar contador de visitas
 * @param {string} postId
 * @returns {Promise<void>}
 */
export async function incrementViews(postId) {
  try {
    const docRef = doc(db, COLLECTION, postId);
    const docSnap = await getDoc(docRef);

    if (docSnap.exists()) {
      const currentViews = docSnap.data().visitas || 0;
      await updateDoc(docRef, {
        visitas: currentViews + 1
      });
    }
  } catch (error) {
    console.error('Error incrementando visitas:', error);
    // No lanzar error, es una operación secundaria
  }
}

/**
 * Generar slug desde título
 * @param {string} titulo
 * @returns {string}
 */
function generarSlug(titulo) {
  return titulo
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
}
