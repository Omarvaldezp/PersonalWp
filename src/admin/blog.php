<?php
/**
 * Gestión de Blog Posts
 * CRUD completo de posts del blog
 */

require_once __DIR__ . '/../api/auth/Auth.php';

$auth = new Auth();
$auth->requireAuth();
$user = $auth->user();

$page_title = 'Blog Posts';
$extra_js = ['assets/blog.js'];
?>

<?php include 'includes/header.php'; ?>

<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1>Blog Posts</h1>
            <button class="btn-primary" onclick="openCreateModal()">
                + Nuevo Post
            </button>
        </header>

        <!-- Alert de bienvenida (se oculta después del primer uso) -->
        <div id="welcomeAlert" class="alert alert-info alert-dismissible" style="display: none;">
            <div class="alert-icon">i</div>
            <div class="alert-content">
                <p><strong>Gestión de Blog Posts</strong></p>
                <p>Aquí puedes crear, editar y eliminar posts del blog. Los cambios se reflejan inmediatamente en el sitio público.</p>
            </div>
            <button class="alert-close" onclick="dismissWelcome()">×</button>
        </div>

        <!-- Filters Bar -->
        <div class="filters-bar">
            <input type="search"
                   id="searchInput"
                   placeholder="Buscar por título o contenido..."
                   class="search-input">

            <select id="categoriaFilter" class="filter-select">
                <option value="">Todas las categorías</option>
            </select>

            <select id="estadoFilter" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="borrador">Borrador</option>
                <option value="publicado">Publicado</option>
                <option value="archivado">Archivado</option>
            </select>

            <button onclick="applyFilters()" class="btn-secondary">
                Filtrar
            </button>

            <button onclick="clearFilters()" class="btn-secondary" style="margin-left: auto;">
                Limpiar
            </button>
        </div>

        <!-- Posts Table -->
        <div class="table-container">
            <table class="data-table" id="postsTable">
                <thead>
                    <tr>
                        <th sortable data-sort="titulo">Título</th>
                        <th>Categorías</th>
                        <th sortable data-sort="estado">Estado</th>
                        <th sortable data-sort="visitas">Visitas</th>
                        <th sortable data-sort="fecha_publicacion">Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="postsTableBody">
                    <!-- Se llena dinámicamente con JavaScript -->
                </tbody>
            </table>

            <!-- Loading State -->
            <div id="loadingState" class="loading-state">
                <div class="spinner"></div>
                <p>Cargando posts...</p>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="empty-state" style="display: none;">
                <p>No hay posts todavía</p>
                <button onclick="openCreateModal()" class="btn-primary">
                    Crear primer post
                </button>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination" id="pagination"></div>
    </main>
</div>

<!-- Modal Create/Edit Post -->
<div id="postModal" class="modal">
    <div class="modal-overlay" onclick="closePostModal()"></div>
    <div class="modal-container">
        <div class="modal-header">
            <h2 id="modalTitle">Nuevo Post</h2>
            <button class="modal-close" onclick="closePostModal()">×</button>
        </div>

        <form id="postForm" class="modal-body">
            <input type="hidden" id="postId" name="id">

            <div class="form-group">
                <label for="titulo" class="required">Título</label>
                <input type="text"
                       id="titulo"
                       name="titulo"
                       required
                       maxlength="255"
                       placeholder="Título del post"
                       onblur="validateField(this)">
                <span class="form-hint">El slug se generará automáticamente desde el título</span>
            </div>

            <div class="form-group">
                <label for="slug" class="required">Slug (URL)</label>
                <input type="text"
                       id="slug"
                       name="slug"
                       required
                       maxlength="255"
                       pattern="[a-z0-9-]+"
                       placeholder="slug-del-post"
                       onblur="validateField(this)">
                <span class="form-hint">Solo minúsculas, números y guiones. Se usa en la URL del post</span>
            </div>

            <div class="form-group">
                <label for="extracto">Extracto</label>
                <textarea id="extracto"
                          name="extracto"
                          rows="3"
                          maxlength="500"
                          placeholder="Breve descripción del post (máx. 500 caracteres)"></textarea>
                <span class="form-hint">Se muestra en listados y compartidos en redes sociales</span>
            </div>

            <div class="form-group">
                <label for="contenido" class="required">Contenido</label>
                <textarea id="contenido"
                          name="contenido"
                          required
                          rows="15"
                          placeholder="Escribe el contenido del post aquí..."
                          onblur="validateField(this)"></textarea>
                <span class="form-hint">Soporte para Markdown disponible. HTML será sanitizado.</span>
            </div>

            <div class="form-group">
                <label for="imagen_portada">URL de Imagen de Portada</label>
                <input type="url"
                       id="imagen_portada"
                       name="imagen_portada"
                       placeholder="https://ejemplo.com/imagen.jpg"
                       onblur="validateField(this)">
                <span class="form-hint">URL completa de la imagen. Tamaño recomendado: 1200x630px</span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="categorias">Categorías</label>
                    <input type="text"
                           id="categorias"
                           name="categorias"
                           placeholder="Blockchain, Bitcoin, IA"
                           data-role="tagsinput">
                    <span class="form-hint">Separadas por comas</span>
                </div>

                <div class="form-group">
                    <label for="etiquetas">Etiquetas</label>
                    <input type="text"
                           id="etiquetas"
                           name="etiquetas"
                           placeholder="cripto, fintech, tecnología"
                           data-role="tagsinput">
                    <span class="form-hint">Separadas por comas</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="estado" class="required">Estado</label>
                    <select id="estado" name="estado" required>
                        <option value="borrador">Borrador</option>
                        <option value="publicado">Publicado</option>
                        <option value="archivado">Archivado</option>
                    </select>
                    <span class="form-hint">Solo posts publicados se muestran en el sitio</span>
                </div>

                <div class="form-group">
                    <label for="fecha_publicacion">Fecha de Publicación</label>
                    <input type="datetime-local"
                           id="fecha_publicacion"
                           name="fecha_publicacion">
                    <span class="form-hint">Dejar vacío para usar fecha actual</span>
                </div>
            </div>
        </form>

        <div class="modal-footer">
            <button type="button"
                    class="btn-secondary"
                    onclick="closePostModal()">
                Cancelar
            </button>
            <button type="button"
                    class="btn-primary"
                    id="savePostBtn"
                    onclick="savePost()">
                Guardar Post
            </button>
        </div>
    </div>
</div>

<!-- Modal Delete Confirmation -->
<div id="deleteModal" class="modal modal-small">
    <div class="modal-overlay" onclick="closeDeleteModal()"></div>
    <div class="modal-container">
        <div class="modal-header">
            <h3>Confirmar Eliminación</h3>
        </div>
        <div class="modal-body" style="text-align: center;">
            <div class="modal-icon" style="width: 64px; height: 64px; margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: rgba(220, 38, 38, 0.1); color: var(--danger); font-size: 2rem;">
                !
            </div>
            <p>¿Estás seguro que deseas eliminar este post?</p>
            <p><strong id="deletePostTitle"></strong></p>
            <p style="color: var(--gray-600); font-size: 0.875rem; margin-top: 1rem;">Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeDeleteModal()">
                Cancelar
            </button>
            <button class="btn-danger" id="confirmDeleteBtn" onclick="confirmDelete()">
                Eliminar Post
            </button>
        </div>
    </div>
</div>

<!-- Modal Preview Post -->
<div id="previewModal" class="modal modal-large">
    <div class="modal-overlay" onclick="closePreviewModal()"></div>
    <div class="modal-container">
        <div class="modal-header">
            <h2>Vista Previa</h2>
            <button class="modal-close" onclick="closePreviewModal()">×</button>
        </div>
        <div class="modal-body" id="previewContent">
            <!-- Se llena dinámicamente -->
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closePreviewModal()">
                Cerrar
            </button>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
