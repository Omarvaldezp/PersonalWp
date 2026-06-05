<?php
/**
 * Admin: Lista de Cursos Académicos (Instancias)
 */

require_once __DIR__ . '/../../api/auth/Auth.php';

$auth = new Auth();
$auth->requireAuthPage();
$user = $auth->user();

$page_title = 'Cursos Académicos';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="admin-container">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1>🎓 Cursos Académicos</h1>
            <div>
                <button class="btn-primary" onclick="window.location.href='crear.php'">
                    <i class="fas fa-plus"></i> Nuevo Curso
                </button>
            </div>
        </header>

        <!-- Filtros -->
        <div class="filters-bar" style="margin-bottom:20px;display:flex;gap:10px;">
            <input type="search" id="searchInput" placeholder="Buscar por nombre o código..." class="search-input" style="flex:2;">
            <select id="estadoFilter" class="filter-select" style="flex:1;">
                <option value="">Todos los estados</option>
                <option value="planificado">Planificado</option>
                <option value="activo" selected>Activo</option>
                <option value="finalizado">Finalizado</option>
                <option value="cancelado">Cancelado</option>
            </select>
            <select id="periodoFilter" class="filter-select" style="flex:1;">
                <option value="">Todos los periodos</option>
            </select>
        </div>

        <!-- Tabla -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Código</th>
                        <th>Periodo</th>
                        <th>Fechas</th>
                        <th>Inscritos</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="cursosTable">
                    <tr>
                        <td colspan="8" style="text-align:center;padding:40px;">
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
let currentFilters = { estado: 'activo' };

async function cargarCursos(page = 1) {
    try {
        const params = new URLSearchParams({ page, limit: 20, ...currentFilters });
        const response = await fetch(`/api/controllers/cursos-instancias.php?${params}`);
        const data = await response.json();

        if (data.success) {
            currentPage = data.data.pagination.page;
            totalPages = data.data.pagination.pages;

            renderTable(data.data.items);
            renderPagination();
        }
    } catch (error) {
        console.error('Error cargando cursos:', error);
    }
}

function renderTable(cursos) {
    const tbody = document.getElementById('cursosTable');

    if (cursos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:40px;">No hay cursos registrados. <a href="crear.php">Crear primer curso</a></td></tr>';
        return;
    }

    tbody.innerHTML = cursos.map(c => {
        const estadoColors = {
            'planificado': '#6b7280',
            'activo': '#16a34a',
            'finalizado': '#2563eb',
            'cancelado': '#dc2626'
        };

        return `
        <tr>
            <td>${c.id}</td>
            <td><strong>${escapeHtml(c.nombre)}</strong></td>
            <td><code>${escapeHtml(c.codigo)}</code></td>
            <td>${escapeHtml(c.periodo || '-')}</td>
            <td style="font-size:0.9em;">
                ${new Date(c.fecha_inicio).toLocaleDateString()}<br>
                <span style="color:#666;">al</span> ${new Date(c.fecha_fin).toLocaleDateString()}
            </td>
            <td><strong>${c.total_inscritos}</strong></td>
            <td><span class="badge" style="background:${estadoColors[c.estado]};">${c.estado}</span></td>
            <td>
                <button class="btn-icon" onclick="gestionarInscripciones(${c.id})" title="Inscripciones">
                    <i class="fas fa-users"></i>
                </button>
                <button class="btn-icon" onclick="editarCurso(${c.id})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
            </td>
        </tr>
    `}).join('');
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
                        onclick="cargarCursos(${i})">${i}</button>`;
    }
    container.innerHTML = html;
}

async function cargarPeriodos() {
    try {
        const response = await fetch('/api/controllers/cursos-instancias.php?periodos=true');
        const data = await response.json();

        if (data.success && data.data.length > 0) {
            const select = document.getElementById('periodoFilter');
            data.data.forEach(p => {
                const option = document.createElement('option');
                option.value = p.periodo;
                option.textContent = p.periodo;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error cargando periodos:', error);
    }
}

function gestionarInscripciones(cursoId) {
    window.location.href = `inscripciones.php?curso_id=${cursoId}`;
}

function editarCurso(id) {
    window.location.href = `crear.php?id=${id}`;
}

// Filtros
document.getElementById('searchInput').addEventListener('input', debounce(function(e) {
    currentFilters.search = e.target.value;
    cargarCursos(1);
}, 500));

document.getElementById('estadoFilter').addEventListener('change', function(e) {
    if (e.target.value) {
        currentFilters.estado = e.target.value;
    } else {
        delete currentFilters.estado;
    }
    cargarCursos(1);
});

document.getElementById('periodoFilter').addEventListener('change', function(e) {
    if (e.target.value) {
        currentFilters.periodo = e.target.value;
    } else {
        delete currentFilters.periodo;
    }
    cargarCursos(1);
});

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

// Cargar al iniciar
cargarCursos();
cargarPeriodos();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
