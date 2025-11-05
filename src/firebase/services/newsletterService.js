/**
 * Newsletter Service - Suscripciones al newsletter
 */

import {
  collection,
  addDoc,
  getDocs,
  query,
  where,
  Timestamp
} from 'firebase/firestore';
import { db } from '../firebase.js';

const COLLECTION = 'newsletter_suscriptores';

/**
 * Suscribirse al newsletter
 * @param {Object} subscriberData
 * @returns {Promise<string>} ID de la suscripción
 */
export async function subscribe(subscriberData) {
  try {
    // Verificar si el email ya está suscrito
    const q = query(
      collection(db, COLLECTION),
      where('email', '==', subscriberData.email),
      where('activo', '==', true)
    );

    const snapshot = await getDocs(q);

    if (!snapshot.empty) {
      throw new Error('Este email ya está suscrito');
    }

    // Crear nueva suscripción
    const docRef = await addDoc(collection(db, COLLECTION), {
      email: subscriberData.email,
      nombre: subscriberData.nombre || '',
      intereses: subscriberData.intereses || [],
      activo: true,
      confirmado: false,
      created_at: Timestamp.now()
    });

    return docRef.id;
  } catch (error) {
    console.error('Error en suscripción:', error);
    throw error;
  }
}
