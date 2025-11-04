// Entry point para tu webapp
console.log('WebApp iniciada correctamente!');

// Aquí puedes agregar tu lógica JavaScript moderna
class App {
    constructor() {
        this.init();
    }

    init() {
        console.log('Inicializando aplicación...');
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Agregar event listeners aquí
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM cargado');
        });
    }
}

// Inicializar la app
const app = new App();

export default app;
