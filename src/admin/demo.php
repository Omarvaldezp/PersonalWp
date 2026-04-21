<?php
/**
 * Demo de Componentes del Panel de Administración
 * Esta página muestra todos los componentes UI disponibles
 */

// Simular usuario autenticado para la demo
$user = [
    'id' => 1,
    'nombre_completo' => 'Demo User',
    'rol' => 'admin'
];

$page_title = 'Demo de Componentes';
?>

<?php include 'includes/header.php'; ?>

<style>
.demo-section {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.demo-section h2 {
    margin: 0 0 1.5rem 0;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--gray-200);
    color: var(--gray-900);
}

.demo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.code-preview {
    background: var(--gray-900);
    color: #a5f3fc;
    padding: 1rem;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    font-size: 0.75rem;
    overflow-x: auto;
    margin-top: 1rem;
}
</style>

<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1>🎨 Demo de Componentes</h1>
            <button class="btn-primary" onclick="showToast('¡Esto es un toast!', 'success')">
                Probar Toast
            </button>
        </header>

        <!-- Alert de bienvenida -->
        <div class="alert alert-info alert-dismissible">
            <div class="alert-icon">ℹ️</div>
            <div class="alert-content">
                <p><strong>Bienvenido a la demo de componentes</strong></p>
                <p>Esta página muestra todos los componentes UI disponibles para el panel de administración. Puedes interactuar con cada uno.</p>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()">×</button>
        </div>

        <!-- Stats Cards -->
        <section class="demo-section">
            <h2>📊 Stats Cards (Dashboard)</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <h3>Blog Posts</h3>
                        <div class="stat-card-icon primary">📝</div>
                    </div>
                    <div class="stat-number">248</div>
                    <div class="stat-trend up">
                        ↑ 12% este mes
                    </div>
                    <a href="#" class="stat-link">Ver todos →</a>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <h3>Cursos Activos</h3>
                        <div class="stat-card-icon success">🎓</div>
                    </div>
                    <div class="stat-number">45</div>
                    <div class="stat-trend up">
                        ↑ 8% este mes
                    </div>
                    <a href="#" class="stat-link">Ver todos →</a>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <h3>Contactos Nuevos</h3>
                        <div class="stat-card-icon warning">📧</div>
                    </div>
                    <div class="stat-number">12</div>
                    <div class="stat-trend down">
                        ↓ 3% este mes
                    </div>
                    <a href="#" class="stat-link">Ver todos →</a>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <h3>Visitas Totales</h3>
                        <div class="stat-card-icon primary">👁️</div>
                    </div>
                    <div class="stat-number">15.2K</div>
                    <div class="stat-trend up">
                        ↑ 23% este mes
                    </div>
                    <a href="#" class="stat-link">Ver detalles →</a>
                </div>
            </div>
        </section>

        <!-- Alerts -->
        <section class="demo-section">
            <h2>🔔 Alerts / Notificaciones</h2>
            <div class="alert alert-info">
                <div class="alert-icon">ℹ️</div>
                <div class="alert-content">
                    <p>Este es un mensaje informativo.</p>
                </div>
            </div>

            <div class="alert alert-success">
                <div class="alert-icon">✓</div>
                <div class="alert-content">
                    <p><strong>¡Éxito!</strong> La operación se completó correctamente.</p>
                </div>
            </div>

            <div class="alert alert-warning">
                <div class="alert-icon">⚠️</div>
                <div class="alert-content">
                    <p><strong>Advertencia:</strong> Algunos cambios requieren confirmación.</p>
                </div>
            </div>

            <div class="alert alert-error">
                <div class="alert-icon">✕</div>
                <div class="alert-content">
                    <p><strong>Error:</strong> No se pudo completar la operación.</p>
                </div>
            </div>
        </section>

        <!-- Buttons -->
        <section class="demo-section">
            <h2>🔘 Botones y Acciones</h2>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <button class="btn-primary">Botón Primary</button>
                <button class="btn-secondary">Botón Secondary</button>
                <button class="btn-danger">Botón Danger</button>
                <button class="btn-primary" disabled>Disabled</button>

                <button class="btn-primary" onclick="testButtonLoading(this)">
                    Probar Loading
                </button>

                <button class="btn-icon" title="Editar">✏️</button>
                <button class="btn-icon btn-danger" title="Eliminar">🗑️</button>
                <button class="btn-icon" title="Ver">👁️</button>
            </div>

            <h3 style="margin-top: 2rem; margin-bottom: 1rem;">Toast Notifications</h3>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <button class="btn-primary" onclick="showToast('Operación exitosa', 'success')">
                    Toast Success
                </button>
                <button class="btn-secondary" onclick="showToast('Error al procesar', 'error')">
                    Toast Error
                </button>
                <button class="btn-secondary" onclick="showToast('Ten cuidado con esto', 'warning')">
                    Toast Warning
                </button>
                <button class="btn-secondary" onclick="showToast('Información importante', 'info')">
                    Toast Info
                </button>
            </div>

            <h3 style="margin-top: 2rem; margin-bottom: 1rem;">Diálogos de Confirmación</h3>
            <button class="btn-danger" onclick="testConfirmDialog()">
                Probar Confirmación
            </button>
        </section>

        <!-- Data Table -->
        <section class="demo-section">
            <h2>📋 Data Table con Filtros</h2>

            <!-- Filters Bar -->
            <div class="filters-bar">
                <input type="search" placeholder="Buscar..." class="search-input">
                <select class="filter-select">
                    <option>Todas las categorías</option>
                    <option>Blockchain</option>
                    <option>Bitcoin</option>
                    <option>IA</option>
                </select>
                <select class="filter-select">
                    <option>Todos los estados</option>
                    <option>Publicado</option>
                    <option>Borrador</option>
                    <option>Archivado</option>
                </select>
                <button class="btn-secondary">Filtrar</button>
            </div>

            <!-- Table -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th sortable class="sorted-desc">Título</th>
                            <th sortable>Categoría</th>
                            <th>Estado</th>
                            <th sortable>Visitas</th>
                            <th sortable>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="post-title">
                                    <strong>Introducción a Blockchain</strong>
                                    <small>introduccion-a-blockchain</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-primary">Blockchain</span>
                            </td>
                            <td>
                                <span class="status status-publicado">publicado</span>
                            </td>
                            <td>1,234</td>
                            <td>21 Abr 2026</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" title="Editar">✏️</button>
                                    <button class="btn-icon btn-danger" title="Eliminar">🗑️</button>
                                    <button class="btn-icon" title="Ver">👁️</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="post-title">
                                    <strong>Bitcoin: El futuro del dinero</strong>
                                    <small>bitcoin-futuro-dinero</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-primary">Bitcoin</span>
                                <span class="badge">Fintech</span>
                            </td>
                            <td>
                                <span class="status status-borrador">borrador</span>
                            </td>
                            <td>523</td>
                            <td>20 Abr 2026</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" title="Editar">✏️</button>
                                    <button class="btn-icon btn-danger" title="Eliminar">🗑️</button>
                                    <button class="btn-icon" title="Ver">👁️</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="post-title">
                                    <strong>IA en los negocios modernos</strong>
                                    <small>ia-negocios-modernos</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-primary">IA</span>
                            </td>
                            <td>
                                <span class="status status-publicado">publicado</span>
                            </td>
                            <td>892</td>
                            <td>19 Abr 2026</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" title="Editar">✏️</button>
                                    <button class="btn-icon btn-danger" title="Eliminar">🗑️</button>
                                    <button class="btn-icon" title="Ver">👁️</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="post-title">
                                    <strong>Tutorial: Smart Contracts</strong>
                                    <small>tutorial-smart-contracts</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-primary">Blockchain</span>
                            </td>
                            <td>
                                <span class="status status-archivado">archivado</span>
                            </td>
                            <td>345</td>
                            <td>15 Abr 2026</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" title="Editar">✏️</button>
                                    <button class="btn-icon btn-danger" title="Eliminar">🗑️</button>
                                    <button class="btn-icon" title="Ver">👁️</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <button class="page-btn" disabled>← Anterior</button>
                <span class="page-info">Página 1 de 5 (48 total)</span>
                <button class="page-btn">Siguiente →</button>
            </div>
        </section>

        <!-- Modales y Formularios -->
        <section class="demo-section">
            <h2>📝 Modales y Formularios</h2>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <button class="btn-primary" onclick="openModal('demoModal')">
                    Abrir Modal de Formulario
                </button>
                <button class="btn-secondary" onclick="openModal('demoModalSmall')">
                    Modal Pequeño
                </button>
            </div>
        </section>

        <!-- Cards -->
        <section class="demo-section">
            <h2>🃏 Cards</h2>
            <div class="demo-grid">
                <div class="card">
                    <div class="card-header">
                        <h3>Card con Header</h3>
                    </div>
                    <div class="card-body">
                        <p>Este es el contenido de la card. Puede tener cualquier cosa dentro.</p>
                    </div>
                    <div class="card-footer">
                        <button class="btn-secondary">Acción</button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h3 style="margin-top: 0;">Card Simple</h3>
                        <p>Una card sin header ni footer, solo con contenido.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Loading States -->
        <section class="demo-section">
            <h2>⏳ Loading States</h2>

            <h3 style="margin-bottom: 1rem;">Spinner</h3>
            <div class="loading-state">
                <div class="spinner"></div>
                <p>Cargando datos...</p>
            </div>

            <h3 style="margin-top: 2rem; margin-bottom: 1rem;">Skeleton Loaders</h3>
            <div class="skeleton skeleton-title"></div>
            <div class="skeleton skeleton-text"></div>
            <div class="skeleton skeleton-text" style="width: 80%;"></div>
            <div class="skeleton skeleton-text" style="width: 60%;"></div>
            <div class="skeleton skeleton-card" style="margin-top: 1rem;"></div>
        </section>

        <!-- Breadcrumbs -->
        <section class="demo-section">
            <h2>🍞 Breadcrumbs</h2>
            <div class="breadcrumbs">
                <a href="#">Dashboard</a>
                <span class="separator">/</span>
                <a href="#">Blog</a>
                <span class="separator">/</span>
                <span class="current">Nuevo Post</span>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="demo-section">
            <h2>⚡ Quick Actions</h2>
            <div class="quick-actions">
                <h2>Acciones Rápidas</h2>
                <div class="actions-grid">
                    <a href="#" class="action-card">
                        <div class="action-card-icon">📝</div>
                        <div class="action-card-title">Nuevo Post</div>
                    </a>
                    <a href="#" class="action-card">
                        <div class="action-card-icon">🎓</div>
                        <div class="action-card-title">Nuevo Curso</div>
                    </a>
                    <a href="#" class="action-card">
                        <div class="action-card-icon">🔬</div>
                        <div class="action-card-title">Nueva Investigación</div>
                    </a>
                </div>
            </div>
        </section>

    </main>
</div>

<!-- Modal Demo - Formulario Completo -->
<div id="demoModal" class="modal">
    <div class="modal-overlay" onclick="closeModal('demoModal')"></div>
    <div class="modal-container">
        <div class="modal-header">
            <h2>Formulario de Ejemplo</h2>
            <button class="modal-close" onclick="closeModal('demoModal')">×</button>
        </div>

        <form id="demoForm" class="modal-body">
            <div class="form-group">
                <label for="titulo" class="required">Título</label>
                <input type="text"
                       id="titulo"
                       name="titulo"
                       required
                       placeholder="Ingresa un título"
                       onblur="validateField(this)">
                <span class="form-hint">El slug se generará automáticamente</span>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email"
                       id="email"
                       name="email"
                       placeholder="ejemplo@correo.com"
                       onblur="validateField(this)">
            </div>

            <div class="form-group">
                <label for="url">URL</label>
                <input type="url"
                       id="url"
                       name="url"
                       placeholder="https://ejemplo.com"
                       onblur="validateField(this)">
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion"
                          name="descripcion"
                          rows="4"
                          placeholder="Escribe una descripción..."></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="categoria">Categoría</label>
                    <select id="categoria" name="categoria">
                        <option value="">Selecciona una opción</option>
                        <option value="blockchain">Blockchain</option>
                        <option value="bitcoin">Bitcoin</option>
                        <option value="ia">Inteligencia Artificial</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha">Fecha</label>
                    <input type="date" id="fecha" name="fecha">
                </div>
            </div>

            <div class="form-group">
                <label>Tags (separados por comas)</label>
                <input type="text" placeholder="blockchain, bitcoin, cripto">
            </div>
        </form>

        <div class="modal-footer">
            <button type="button"
                    class="btn-secondary"
                    onclick="closeModal('demoModal')">
                Cancelar
            </button>
            <button type="button"
                    class="btn-primary"
                    onclick="testFormValidation()">
                Guardar
            </button>
        </div>
    </div>
</div>

<!-- Modal Demo - Pequeño -->
<div id="demoModalSmall" class="modal modal-small">
    <div class="modal-overlay" onclick="closeModal('demoModalSmall')"></div>
    <div class="modal-container">
        <div class="modal-header">
            <h3>Modal Pequeño</h3>
            <button class="modal-close" onclick="closeModal('demoModalSmall')">×</button>
        </div>
        <div class="modal-body">
            <p>Este es un modal más pequeño, ideal para confirmaciones o mensajes breves.</p>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('demoModalSmall')">
                Cerrar
            </button>
        </div>
    </div>
</div>

<script>
// Test button loading
function testButtonLoading(button) {
    setButtonLoading(button, true);
    setTimeout(() => {
        setButtonLoading(button, false);
        showToast('Carga completada', 'success');
    }, 2000);
}

// Test confirm dialog
async function testConfirmDialog() {
    const confirmed = await confirmDialog({
        title: '¿Eliminar este elemento?',
        message: 'Esta acción no se puede deshacer. El elemento será eliminado permanentemente.',
        confirmText: 'Sí, eliminar',
        cancelText: 'Cancelar',
        type: 'danger'
    });

    if (confirmed) {
        showToast('Elemento eliminado', 'success');
    } else {
        showToast('Operación cancelada', 'info');
    }
}

// Test form validation
function testFormValidation() {
    const form = document.getElementById('demoForm');

    if (validateForm(form)) {
        showToast('¡Formulario válido!', 'success');
        closeModal('demoModal');
    } else {
        showToast('Por favor corrige los errores', 'error');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
