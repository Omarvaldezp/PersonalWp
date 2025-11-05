/**
 * Contact Service - Manejo de formulario de contacto
 */

import { collection, addDoc, Timestamp } from 'firebase/firestore';
import { db } from '../firebase.js';

const COLLECTION = 'contactos';

/**
 * Enviar mensaje de contacto
 * @param {Object} contactData
 * @returns {Promise<string>} ID del mensaje
 */
export async function sendContactMessage(contactData) {
  try {
    const docRef = await addDoc(collection(db, COLLECTION), {
      nombre: contactData.nombre,
      email: contactData.email,
      telefono: contactData.telefono || '',
      empresa: contactData.empresa || '',
      asunto: contactData.asunto || '',
      mensaje: contactData.mensaje,
      tipo: contactData.tipo || 'general',
      estado: 'nuevo',
      created_at: Timestamp.now()
    });

    return docRef.id;
  } catch (error) {
    console.error('Error enviando mensaje:', error);
    throw error;
  }
}
