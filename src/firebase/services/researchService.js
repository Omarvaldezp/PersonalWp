/**
 * Research Service - Operaciones para investigaciones académicas
 */

import {
  collection,
  getDocs,
  getDoc,
  doc,
  query,
  where,
  orderBy,
  limit
} from 'firebase/firestore';
import { db } from '../firebase.js';

const COLLECTION = 'investigaciones';

export async function getAllResearch(options = {}) {
  try {
    const {
      tipo = null,
      categoria = null,
      destacado = null,
      limite = 50
    } = options;

    let q = collection(db, COLLECTION);

    if (tipo) {
      q = query(q, where('tipo', '==', tipo));
    }

    if (categoria) {
      q = query(q, where('categorias', 'array-contains', categoria));
    }

    if (destacado !== null) {
      q = query(q, where('destacado', '==', destacado));
    }

    q = query(q, orderBy('fecha_publicacion', 'desc'));

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
    console.error('Error obteniendo investigaciones:', error);
    throw error;
  }
}

export async function getResearchById(id) {
  try {
    const docRef = doc(db, COLLECTION, id);
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
    console.error('Error obteniendo investigación:', error);
    throw error;
  }
}

export async function getCategorias() {
  try {
    const snapshot = await getDocs(collection(db, COLLECTION));
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
