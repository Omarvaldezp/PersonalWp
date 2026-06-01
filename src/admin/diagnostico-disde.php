<?php
/**
 * Panel Admin - Diagnóstico DISDE
 * Visualización de respuestas y analytics
 */

require_once __DIR__ . '/../api/auth/Auth.php';

$auth = new Auth();
$auth->requireAuthPage();
$user = $auth->user();

$page_title = 'Diagnóstico DISDE - Respuestas';
$extra_css = ['assets/admin-tables.css'];
?>

<?php include 'includes/header.php'; ?>

<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1>📊 Diagnóstico DISDE</h1>
            <div>
                <button class="btn-secondary" onclick="exportarExcel()">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
                <button class="btn-primary" onclick="mostrarAnalytics()">
                    <i class="fas fa-chart-bar"></i> Ver Analytics
                </button>
            </div>
        </header>

        <!-- Stats Cards -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px;" id="statsCards">
            <div class="stat-card">
                <h3>Total Respuestas</h3>
                <p class="stat-number" id="stat-total">-</p>
            </div>
            <div class="stat-card">
                <h3>Promedio Conocimiento</h3>
                <p class="stat-number" id="stat-avg">-</p>
            </div>
            <div class="stat-card">
                <h3>Emails Enviados</h3>
                <p class="stat-number" id="stat-emails">-</p>
            </div>
            <div class="stat-card">
                <h3>Últimas 24h</h3>
                <p class="stat-number" id="stat-24h">-</p>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-bar" style="margin-bottom:20px;">
            <input type="search" id="searchInput" placeholder="Buscar por nombre o email..." class="search-input">
            <select id="gradoFilter" class="filter-select">
                <option value="">Todos los grados</option>
                <option value="Maestría en curso">Maestría en curso</option>
                <option value="Maestría concluida (sin título)">Maestría concluida</option>
                <option value="Maestría titulada">Maestría titulada</option>
                <option value="Otro">Otro</option>
            </select>
            <select id="puntajeFilter" class="filter-select">
                <option value="">Todos los puntajes</option>
                <option value="alto">Alto (10-12)</option>
                <option value="medio">Medio (6-9)</option>
                <option value="bajo">Bajo (0-5)</option>
            </select>
        </div>

        <!-- Tabla de respuestas -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Grado</th>
                        <th>Puntaje</th>
                        <th>Fecha</th>
                        <th>Email Enviado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="respuestasTable">
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

<!-- Modal: Ver Detalle -->
<div id="detalleModal" class="modal modal-large">
    <div class="modal-overlay" onclick="closeDetalleModal()"></div>
    <div class="modal-container">
        <div class="modal-header">
            <h2>Detalle de Respuesta</h2>
            <button class="modal-close" onclick="closeDetalleModal()">×</button>
        </div>
        <div class="modal-body" id="detalleContent">
            <!-- Se llena dinámicamente -->
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeDetalleModal()">Cerrar</button>
        </div>
    </div>
</div>

<!-- Modal: Analytics -->
<div id="analyticsModal" class="modal modal-large">
    <div class="modal-overlay" onclick="closeAnalyticsModal()"></div>
    <div class="modal-container">
        <div class="modal-header">
            <h2>📊 Analytics Agregado</h2>
            <button class="modal-close" onclick="closeAnalyticsModal()">×</button>
        </div>
        <div class="modal-body" id="analyticsContent">
            <!-- Se llena dinámicamente -->
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeAnalyticsModal()">Cerrar</button>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let totalPages = 1;
let allRespuestas = [];

async function cargarRespuestas(page = 1) {
    try {
        const response = await fetch(`/api/controllers/diagnostico-disde.php?page=${page}&limit=20`);
        const data = await response.json();

        if (data.success) {
            allRespuestas = data.data.items;
            currentPage = data.data.pagination.page;
            totalPages = data.data.pagination.pages;

            renderTable(allRespuestas);
            renderPagination();
            await cargarStats();
        }
    } catch (error) {
        console.error('Error cargando respuestas:', error);
        showToast('Error al cargar respuestas', 'error');
    }
}

function renderTable(respuestas) {
    const tbody = document.getElementById('respuestasTable');

    if (respuestas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:40px;">No hay respuestas registradas</td></tr>';
        return;
    }

    tbody.innerHTML = respuestas.map(r => `
        <tr>
            <td>${r.id}</td>
            <td>${escapeHtml(r.nombre)}</td>
            <td>${escapeHtml(r.correo)}</td>
            <td><span class="badge">${escapeHtml(r.grado_academico || '-')}</span></td>
            <td><strong style="color:${getPuntajeColor(r.conocimiento_total)}">${r.conocimiento_total}/12</strong></td>
            <td>${formatFecha(r.created_at)}</td>
            <td>${r.email_enviado ? '<span style="color:#16a34a;">✓ Enviado</span>' : '<span style="color:#dc2626;">✗ Pendiente</span>'}</td>
            <td>
                <button class="btn-icon" onclick="verDetalle(${r.id})" title="Ver detalle">
                    <i class="fas fa-eye"></i>
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
                        onclick="cargarRespuestas(${i})">${i}</button>`;
    }
    container.innerHTML = html;
}

async function cargarStats() {
    try {
        const response = await fetch('/api/controllers/diagnostico-disde.php?analytics=true');
        const data = await response.json();

        if (data.success) {
            const stats = data.data;
            document.getElementById('stat-total').textContent = stats.total_respuestas;
            document.getElementById('stat-avg').textContent = stats.promedio_conocimiento + '/12';
            document.getElementById('stat-emails').textContent = stats.emails_enviados;

            // Calcular últimas 24h
            const hoy = stats.respuestas_por_dia.find(r => r.fecha === new Date().toISOString().split('T')[0]);
            document.getElementById('stat-24h').textContent = hoy ? hoy.count : 0;
        }
    } catch (error) {
        console.error('Error cargando stats:', error);
    }
}

async function verDetalle(id) {
    try {
        const response = await fetch(`/api/controllers/diagnostico-disde.php?id=${id}`);
        const data = await response.json();

        if (data.success) {
            const r = data.data;
            const resultados = JSON.parse(r.resultados_por_area);
            const respuestas = JSON.parse(r.respuestas_completas);

            let html = `
                <div style="background:#f8f9fa;padding:20px;border-radius:8px;margin-bottom:20px;">
                    <h3>Información del Participante</h3>
                    <p><strong>Nombre:</strong> ${escapeHtml(r.nombre)}</p>
                    <p><strong>Email:</strong> ${escapeHtml(r.correo)}</p>
                    <p><strong>Grado:</strong> ${escapeHtml(r.grado_academico)}</p>
                    <p><strong>Carrera:</strong> ${escapeHtml(r.carrera_licenciatura)}</p>
                    <p><strong>Área de formación:</strong> ${escapeHtml(r.area_formacion)}</p>
                    <p><strong>Institución:</strong> ${escapeHtml(r.institucion || 'No especificada')}</p>
                    <p><strong>Fecha:</strong> ${formatFecha(r.created_at)}</p>
                </div>

                <h3>Puntaje Total: <span style="color:#2563eb;font-size:1.5em;">${r.conocimiento_total}/12</span></h3>

                <h3 style="margin-top:30px;">Resultados por Área:</h3>
                <table class="data-table" style="margin-top:15px;">
                    <thead>
                        <tr>
                            <th>Área</th>
                            <th>Conocimiento</th>
                            <th>Autopercepción</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${resultados.map(area => `
                            <tr>
                                <td>${area.area}</td>
                                <td>${area.conocimiento}</td>
                                <td>${area.autopercepcion}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;

            document.getElementById('detalleContent').innerHTML = html;
            document.getElementById('detalleModal').classList.add('active');
        }
    } catch (error) {
        console.error('Error cargando detalle:', error);
        showToast('Error al cargar detalle', 'error');
    }
}

async function mostrarAnalytics() {
    try {
        const response = await fetch('/api/controllers/diagnostico-disde.php?analytics=true');
        const data = await response.json();

        if (data.success) {
            const stats = data.data;

            let html = `
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px;">
                    <div class="stat-card">
                        <h4>Total Respuestas</h4>
                        <p style="font-size:2em;font-weight:bold;color:#2563eb;">${stats.total_respuestas}</p>
                    </div>
                    <div class="stat-card">
                        <h4>Promedio Conocimiento</h4>
                        <p style="font-size:2em;font-weight:bold;color:#059669;">${stats.promedio_conocimiento}/12</p>
                    </div>
                    <div class="stat-card">
                        <h4>Emails Enviados</h4>
                        <p style="font-size:2em;font-weight:bold;color:#7c3aed;">${stats.emails_enviados}</p>
                    </div>
                </div>

                <h3>Distribución de Puntajes</h3>
                <table class="data-table">
                    <thead><tr><th>Puntaje</th><th>Cantidad</th><th>%</th></tr></thead>
                    <tbody>
                        ${stats.distribucion_puntajes.map(p => `
                            <tr>
                                <td><strong>${p.conocimiento_total}/12</strong></td>
                                <td>${p.count}</td>
                                <td>${Math.round((p.count / stats.total_respuestas) * 100)}%</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>

                <h3 style="margin-top:30px;">Distribución por Grado Académico</h3>
                <table class="data-table">
                    <thead><tr><th>Grado</th><th>Cantidad</th></tr></thead>
                    <tbody>
                        ${stats.distribucion_grados.map(g => `
                            <tr>
                                <td>${escapeHtml(g.grado_academico)}</td>
                                <td>${g.count}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;

            document.getElementById('analyticsContent').innerHTML = html;
            document.getElementById('analyticsModal').classList.add('active');
        }
    } catch (error) {
        console.error('Error cargando analytics:', error);
        showToast('Error al cargar analytics', 'error');
    }
}

function closeDetalleModal() {
    document.getElementById('detalleModal').classList.remove('active');
}

function closeAnalyticsModal() {
    document.getElementById('analyticsModal').classList.remove('active');
}

function exportarExcel() {
    window.open('/api/controllers/diagnostico-disde.php?export=excel', '_blank');
}

function getPuntajeColor(puntaje) {
    if (puntaje >= 10) return '#16a34a';
    if (puntaje >= 6) return '#f59e0b';
    return '#dc2626';
}

function formatFecha(fecha) {
    return new Date(fecha).toLocaleString('es-MX');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Filtros
document.getElementById('searchInput').addEventListener('input', function() {
    aplicarFiltros();
});

document.getElementById('gradoFilter').addEventListener('change', function() {
    aplicarFiltros();
});

document.getElementById('puntajeFilter').addEventListener('change', function() {
    aplicarFiltros();
});

function aplicarFiltros() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const grado = document.getElementById('gradoFilter').value;
    const puntaje = document.getElementById('puntajeFilter').value;

    let filtered = allRespuestas.filter(r => {
        const matchSearch = !search ||
            r.nombre.toLowerCase().includes(search) ||
            r.correo.toLowerCase().includes(search);

        const matchGrado = !grado || r.grado_academico === grado;

        let matchPuntaje = true;
        if (puntaje === 'alto') matchPuntaje = r.conocimiento_total >= 10;
        if (puntaje === 'medio') matchPuntaje = r.conocimiento_total >= 6 && r.conocimiento_total < 10;
        if (puntaje === 'bajo') matchPuntaje = r.conocimiento_total < 6;

        return matchSearch && matchGrado && matchPuntaje;
    });

    renderTable(filtered);
}

// Cargar al inicio
cargarRespuestas();
</script>

<?php include 'includes/footer.php'; ?>
