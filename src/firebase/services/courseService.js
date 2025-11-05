/**
 * Course Service - Operaciones CRUD para cursos
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

const COLLECTION = 'cursos';

/**
 * Obtener todos los cursos activos
 * @param {Object} options - Opciones de filtrado
 * @returns {Promise<Array>}
 */
export async function getAllCourses(options = {}) {
  try {
    const {
      nivel = null,
      modalidad = null,
      categoria = null,
      destacado = null,
      limite = 50
    } = options;

    let q = query(
      collection(db, COLLECTION),
      where('activo', '==', true)
    );

    // Filtros
    if (nivel) {
      q = query(q, where('nivel', '==', nivel));
    }

    if (modalidad) {
      q = query(q, where('modalidad', '==', modalidad));
    }

    if (categoria) {
      q = query(q, where('categorias', 'array-contains', categoria));
    }

    if (destacado !== null) {
      q = query(q, where('destacado', '==', destacado));
    }

    // Ordenar por calificación
    q = query(q, orderBy('calificacion', 'desc'));

    if (limite) {
      q = query(q, limit(limite));
    }

    const snapshot = await getDocs(q);
    return snapshot.docs.map(doc => ({
      id: doc.id,
      ...doc.data(),
      fecha_inicio: doc.data().fecha_inicio?.toDate(),
      fecha_fin: doc.data().fecha_fin?.toDate()
    }));
  } catch (error) {
    console.error('Error obteniendo cursos:', error);
    throw error;
  }
}

/**
 * Obtener curso por ID
 * @param {string} courseId
 * @returns {Promise<Object|null>}
 */
export async function getCourseById(courseId) {
  try {
    const docRef = doc(db, COLLECTION, courseId);
    const docSnap = await getDoc(docRef);

    if (docSnap.exists()) {
      return {
        id: docSnap.id,
        ...docSnap.data(),
        fecha_inicio: docSnap.data().fecha_inicio?.toDate(),
        fecha_fin: docSnap.data().fecha_fin?.toDate()
      };
    }
    return null;
  } catch (error) {
    console.error('Error obteniendo curso:', error);
    throw error;
  }
}

/**
 * Obtener cursos destacados
 * @param {number} limite
 * @returns {Promise<Array>}
 */
export async function getFeaturedCourses(limite = 5) {
  return getAllCourses({ destacado: true, limite });
}

/**
 * Obtener categorías de cursos
 * @returns {Promise<Array>}
 */
export async function getCategorias() {
  try {
    const snapshot = await getDocs(
      query(collection(db, COLLECTION), where('activo', '==', true))
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
 * Crear nuevo curso
 * @param {Object} courseData
 * @returns {Promise<string>}
 */
export async function createCourse(courseData) {
  try {
    const docRef = await addDoc(collection(db, COLLECTION), {
      titulo: courseData.titulo,
      slug: courseData.slug || generarSlug(courseData.titulo),
      descripcion_corta: courseData.descripcion_corta || '',
      descripcion_completa: courseData.descripcion_completa || '',
      imagen_portada: courseData.imagen_portada || '',
      nivel: courseData.nivel || 'principiante',
      duracion_horas: courseData.duracion_horas || 0,
      precio: courseData.precio || 0,
      moneda: courseData.moneda || 'MXN',
      modalidad: courseData.modalidad || 'online',
      categorias: courseData.categorias || [],
      habilidades_aprendidas: courseData.habilidades_aprendidas || [],
      requisitos: courseData.requisitos || [],
      temario: courseData.temario || [],
      destacado: courseData.destacado || false,
      activo: courseData.activo !== undefined ? courseData.activo : true,
      cupo_maximo: courseData.cupo_maximo || null,
      inscritos: 0,
      calificacion: 0,
      numero_resenas: 0,
      fecha_inicio: courseData.fecha_inicio || null,
      fecha_fin: courseData.fecha_fin || null,
      created_at: Timestamp.now(),
      updated_at: Timestamp.now()
    });

    return docRef.id;
  } catch (error) {
    console.error('Error creando curso:', error);
    throw error;
  }
}

/**
 * Actualizar curso
 * @param {string} courseId
 * @param {Object} updates
 * @returns {Promise<void>}
 */
export async function updateCourse(courseId, updates) {
  try {
    const docRef = doc(db, COLLECTION, courseId);
    await updateDoc(docRef, {
      ...updates,
      updated_at: Timestamp.now()
    });
  } catch (error) {
    console.error('Error actualizando curso:', error);
    throw error;
  }
}

/**
 * Eliminar curso
 * @param {string} courseId
 * @returns {Promise<void>}
 */
export async function deleteCourse(courseId) {
  try {
    await deleteDoc(doc(db, COLLECTION, courseId));
  } catch (error) {
    console.error('Error eliminando curso:', error);
    throw error;
  }
}

function generarSlug(titulo) {
  return titulo
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
}
