// Configuración de Firebase
// IMPORTANTE: Estos valores son públicos y es normal que estén en el código
// La seguridad se maneja con las reglas de Firestore, NO ocultando estas keys

const firebaseConfig = {
  apiKey: "TU_API_KEY_AQUI",
  authDomain: "TU_PROJECT_ID.firebaseapp.com",
  projectId: "TU_PROJECT_ID",
  storageBucket: "TU_PROJECT_ID.appspot.com",
  messagingSenderId: "TU_MESSAGING_SENDER_ID",
  appId: "TU_APP_ID"
};

export default firebaseConfig;

/*
 * INSTRUCCIONES PARA OBTENER TU CONFIGURACIÓN:
 *
 * 1. Ve a: https://console.firebase.google.com/
 * 2. Crea un nuevo proyecto o selecciona uno existente
 * 3. Click en el ícono de engranaje ⚙️ > "Configuración del proyecto"
 * 4. En la sección "Tus apps", click en el ícono </> (Web)
 * 5. Registra tu app con el nombre "OmarValdez Web"
 * 6. Copia el objeto firebaseConfig que te muestra
 * 7. Pega los valores arriba
 * 8. ¡Listo!
 *
 * NOTA: Es SEGURO que estos valores sean públicos
 * La seguridad se controla con las reglas de Firestore
 */
