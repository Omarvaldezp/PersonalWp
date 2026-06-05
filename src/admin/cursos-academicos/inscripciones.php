<?php
/**
 * Admin: Gestión de Inscripciones
 */

require_once __DIR__ . '/../api/auth/Auth.php';

$auth = new Auth();
$auth->requireAuthPage();
$user = $auth->user();

$curso_id = isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : null;
if (!$curso_id) {
    header('Location: index.php');
    exit;
}

$page_title = 'Gestión de Inscripciones';
$extra_css = ['assets/admin-tables.css'];
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="admin-container">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <div>
                <button class="btn-secondary" onclick="history.back()">
                    <i class="fas fa-arrow-left"></i> Volver
                </button>
                <h1 id="cursoName">Inscripciones</h1>
            </div>
            <div>
                <button class="btn-primary" onclick="mostrarModalInscribir()">
                    <i class="fas fa-plus"></i> Inscribir Estudiantes
                </button>
            </div>
        </header>

        <!-- Stats -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px;">
            <div class="stat-card">
                <h3>Total Inscritos</h3>
                <p class="stat-number" id="stat-total">-</p>
            </div>
            <div class="stat-card">
                <h3>Activos</h3>
                <p class="stat-number" id="stat-activos">-</p>
            </div>
            <div class="stat-card">
                <h3>Promedio</h3>
                <p class="stat-number" id="stat-promedio">-</p>
            </div>
        </div>

        <!-- Tabla de inscritos -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Matrícula</th>
                        <th>Estado</th>
                        <th>Calificación</th>
                        <th>Fecha Inscripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="inscritosTable">
                    <tr>
                        <td colspan="8" style="text-align:center;padding:40px;">Cargando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Modal: Inscribir Estudiantes -->
<div id="modalInscribir" class="modal">
    <div class="modal-overlay" onclick="cerrarModal()"></div>
    <div class="modal-container">
        <div class="modal-header">
            <h2>Inscribir Estudiantes</h2>
            <button class="modal-close" onclick="cerrarModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="search" id="searchEstudiantes" placeholder="Buscar estudiante..."
                   style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;margin-bottom:15px;">

            <div id="listaEstudiantes" style="max-height:400px;overflow-y:auto;border:1px solid #ddd;border-radius:5px;padding:10px;">
                <p style="text-align:center;color:#666;">Cargando...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-primary" onclick="inscribirSeleccionados()">
                <i class="fas fa-check"></i> Inscribir Seleccionados
            </button>
        </div>
    </div>
</div>

<script>
const cursoId = <?= $curso_id ?>;
let estudiantesDisponibles = [];
let estudiantesSeleccionados = [];

async function cargarCurso() {
    try {
        const response = await fetch(`/api/controllers/cursos-instancias.php?id=${cursoId}`);
        const data = await response.json();

        if (data.success) {
            document.getElementById('cursoName').textContent = `Inscripciones - ${data.data.nombre}`;
        }
    } catch (error) {
        console.error('Error cargando curso:', error);
    }
}

async function cargarInscritos() {
    try {
        const response = await fetch(`/api/controllers/inscripciones.php?curso_instancia_id=${cursoId}`);
        const data = await response.json();

        if (data.success) {
            renderTabla(data.data);
            cargarStats();
        }
    } catch (error) {
        console.error('Error cargando inscritos:', error);
    }
}

function renderTabla(inscritos) {
    const tbody = document.getElementById('inscritosTable');

    if (inscritos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:40px;">No hay estudiantes inscritos. <button onclick="mostrarModalInscribir()" class="btn-primary">Inscribir estudiantes</button></td></tr>';
        return;
    }

    tbody.innerHTML = inscritos.map(i => `
        <tr>
            <td>${i.id}</td>
            <td><strong>${escapeHtml(i.estudiante_nombre)}</strong></td>
            <td>${escapeHtml(i.estudiante_correo)}</td>
            <td>${escapeHtml(i.estudiante_matricula || '-')}</td>
            <td><span class="badge">${i.estado}</span></td>
            <td>${i.calificacion_final ? `<strong>${i.calificacion_final}</strong>` : '-'}</td>
            <td style="font-size:0.9em;">${new Date(i.fecha_inscripcion).toLocaleDateString()}</td>
            <td>
                <button class="btn-icon" onclick="desinscribir(${i.id})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

async function cargarStats() {
    try {
        const response = await fetch(`/api/controllers/inscripciones.php?curso_instancia_id=${cursoId}&stats=true`);
        const data = await response.json();

        if (data.success) {
            const stats = data.data;
            document.getElementById('stat-total').textContent = stats.total;

            const activos = stats.por_estado.find(e => e.estado === 'activo');
            document.getElementById('stat-activos').textContent = activos ? activos.count : 0;

            document.getElementById('stat-promedio').textContent = stats.promedio_calificaciones || '-';
        }
    } catch (error) {
        console.error('Error cargando stats:', error);
    }
}

async function mostrarModalInscribir() {
    // Cargar estudiantes no inscritos
    try {
        const response = await fetch('/api/controllers/estudiantes.php?limit=100');
        const data = await response.json();

        if (data.success) {
            estudiantesDisponibles = data.data.items;
            renderListaEstudiantes(estudiantesDisponibles);
            document.getElementById('modalInscribir').style.display = 'block';
        }
    } catch (error) {
        console.error('Error cargando estudiantes:', error);
    }
}

function renderListaEstudiantes(estudiantes) {
    const container = document.getElementById('listaEstudiantes');

    if (estudiantes.length === 0) {
        container.innerHTML = '<p style="text-align:center;color:#666;">No hay estudiantes disponibles</p>';
        return;
    }

    container.innerHTML = estudiantes.map(e => `
        <label style="display:block;padding:10px;border-bottom:1px solid #eee;cursor:pointer;">
            <input type="checkbox" value="${e.id}" onchange="toggleEstudiante(${e.id})">
            <strong>${escapeHtml(e.nombre)}</strong> - ${escapeHtml(e.correo)}
        </label>
    `).join('');
}

function toggleEstudiante(id) {
    const index = estudiantesSeleccionados.indexOf(id);
    if (index > -1) {
        estudiantesSeleccionados.splice(index, 1);
    } else {
        estudiantesSeleccionados.push(id);
    }
}

async function inscribirSeleccionados() {
    if (estudiantesSeleccionados.length === 0) {
        alert('Selecciona al menos un estudiante');
        return;
    }

    try {
        const response = await fetch('/api/controllers/inscripciones.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                curso_instancia_id: cursoId,
                estudiante_ids: estudiantesSeleccionados
            })
        });

        const data = await response.json();

        if (data.success) {
            alert(`Estudiantes inscritos: ${data.data.inscritos}`);
            cerrarModal();
            cargarInscritos();
            estudiantesSeleccionados = [];
        } else {
            alert(data.message || 'Error al inscribir estudiantes');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al inscribir estudiantes');
    }
}

async function desinscribir(inscripcionId) {
    if (!confirm('¿Eliminar esta inscripción?')) return;

    try {
        const response = await fetch(`/api/controllers/inscripciones.php?id=${inscripcionId}`, {
            method: 'DELETE'
        });

        const data = await response.json();

        if (data.success) {
            alert('Inscripción eliminada');
            cargarInscritos();
        } else {
            alert(data.message || 'Error al eliminar');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al eliminar inscripción');
    }
}

function cerrarModal() {
    document.getElementById('modalInscribir').style.display = 'none';
}

document.getElementById('searchEstudiantes').addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    const filtered = estudiantesDisponibles.filter(est =>
        est.nombre.toLowerCase().includes(search) ||
        est.correo.toLowerCase().includes(search)
    );
    renderListaEstudiantes(filtered);
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Cargar al iniciar
cargarCurso();
cargarInscritos();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
