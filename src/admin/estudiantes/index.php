<?php
/**
 * Admin: Lista de Estudiantes
 */

require_once __DIR__ . '/../../api/auth/Auth.php';

$auth = new Auth();
$auth->requireAuthPage();
$user = $auth->user();

$page_title = 'Estudiantes';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="admin-container">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1>👥 Estudiantes</h1>
            <div>
                <button class="btn-secondary" onclick="exportarExcel()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>
                <button class="btn-primary" onclick="window.location.href='crear.php'">
                    <i class="fas fa-plus"></i> Nuevo Estudiante
                </button>
            </div>
        </header>

        <!-- Stats Cards -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px;" id="statsCards">
            <div class="stat-card">
                <h3>Total Estudiantes</h3>
                <p class="stat-number" id="stat-total">-</p>
            </div>
            <div class="stat-card">
                <h3>En Cursos Activos</h3>
                <p class="stat-number" id="stat-activos">-</p>
            </div>
            <div class="stat-card">
                <h3>Maestría Titulada</h3>
                <p class="stat-number" id="stat-titulada">-</p>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-bar" style="margin-bottom:20px;display:flex;gap:10px;">
            <input type="search" id="searchInput" placeholder="Buscar por nombre o email..." class="search-input" style="flex:2;">
            <select id="gradoFilter" class="filter-select" style="flex:1;">
                <option value="">Todos los grados</option>
                <option value="Maestría en curso">Maestría en curso</option>
                <option value="Maestría concluida (sin título)">Maestría concluida</option>
                <option value="Maestría titulada">Maestría titulada</option>
            </select>
            <select id="cursoFilter" class="filter-select" style="flex:1;">
                <option value="">Todos los cursos</option>
            </select>
        </div>

        <!-- Tabla -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Matrícula</th>
                        <th>Grado</th>
                        <th>Cursos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="estudiantesTable">
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;">
                            Cargando...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="pagination" id="pagination"></div>
    </main>
</div>

<script>
let currentPage = 1;
let totalPages = 1;
let currentFilters = {};

async function cargarEstudiantes(page = 1) {
    try {
        const params = new URLSearchParams({ page, limit: 20, ...currentFilters });
        const response = await fetch(`/api/controllers/estudiantes.php?${params}`);
        const data = await response.json();

        if (data.success) {
            currentPage = data.data.pagination.page;
            totalPages = data.data.pagination.pages;

            renderTable(data.data.items);
            renderPagination();
        }
    } catch (error) {
        console.error('Error cargando estudiantes:', error);
        showToast('Error al cargar estudiantes', 'error');
    }
}

function renderTable(estudiantes) {
    const tbody = document.getElementById('estudiantesTable');

    if (estudiantes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:40px;">No hay estudiantes registrados</td></tr>';
        return;
    }

    tbody.innerHTML = estudiantes.map(e => `
        <tr>
            <td>${e.id}</td>
            <td><strong>${escapeHtml(e.nombre)}</strong></td>
            <td>${escapeHtml(e.correo)}</td>
            <td>${escapeHtml(e.matricula || '-')}</td>
            <td><span class="badge">${escapeHtml(e.grado_academico || '-')}</span></td>
            <td>-</td>
            <td>
                <button class="btn-icon" onclick="verDetalle(${e.id})" title="Ver perfil">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn-icon" onclick="editarEstudiante(${e.id})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function renderPagination() {
    const container = document.getElementById('pagination');
    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';
    for (let i = 1; i <= totalPages; i++) {
        html += `<button class="page-btn ${i === currentPage ? 'active' : ''}"
                        onclick="cargarEstudiantes(${i})">${i}</button>`;
    }
    container.innerHTML = html;
}

async function cargarStats() {
    try {
        const response = await fetch('/api/controllers/estudiantes.php?stats=true');
        const data = await response.json();

        if (data.success) {
            const stats = data.data;
            document.getElementById('stat-total').textContent = stats.total;
            document.getElementById('stat-activos').textContent = stats.inscritos_cursos_activos;

            const titulada = stats.por_grado.find(g => g.grado_academico === 'Maestría titulada');
            document.getElementById('stat-titulada').textContent = titulada ? titulada.count : 0;
        }
    } catch (error) {
        console.error('Error cargando stats:', error);
    }
}

function verDetalle(id) {
    window.location.href = `detalle.php?id=${id}`;
}

function editarEstudiante(id) {
    // TODO: Implementar edición
    showToast('Función de edición próximamente', 'info');
}

function exportarExcel() {
    showToast('Exportación próximamente', 'info');
}

// Filtros
document.getElementById('searchInput').addEventListener('input', debounce(function(e) {
    currentFilters.search = e.target.value;
    cargarEstudiantes(1);
}, 500));

document.getElementById('gradoFilter').addEventListener('change', function(e) {
    if (e.target.value) {
        currentFilters.grado = e.target.value;
    } else {
        delete currentFilters.grado;
    }
    cargarEstudiantes(1);
});

document.getElementById('cursoFilter').addEventListener('change', function(e) {
    if (e.target.value) {
        currentFilters.curso_instancia_id = e.target.value;
    } else {
        delete currentFilters.curso_instancia_id;
    }
    cargarEstudiantes(1);
});

// Helpers
function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showToast(message, type = 'success') {
    alert(message); // TODO: Implementar toast notification
}

// Cargar al iniciar
cargarEstudiantes();
cargarStats();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
