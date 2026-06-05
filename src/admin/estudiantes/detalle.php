<?php
/**
 * Admin: Perfil de Estudiante
 */

require_once __DIR__ . '/../../api/auth/Auth.php';

$auth = new Auth();
$auth->requireAuthPage();
$user = $auth->user();

$estudiante_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$estudiante_id) {
    header('Location: index.php');
    exit;
}

$page_title = 'Perfil de Estudiante';
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
                <h1 id="studentName">Perfil de Estudiante</h1>
            </div>
            <div>
                <button class="btn-secondary" onclick="exportarPDF()">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </button>
                <button class="btn-primary" onclick="editarEstudiante()">
                    <i class="fas fa-edit"></i> Editar
                </button>
            </div>
        </header>

        <div id="loading" style="text-align:center;padding:40px;">
            <p>Cargando...</p>
        </div>

        <div id="content" style="display:none;">
            <!-- Información Personal -->
            <div class="card" style="margin-bottom:20px;">
                <h2>Información Personal</h2>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-top:15px;">
                    <div>
                        <label><strong>Nombre:</strong></label>
                        <p id="nombre">-</p>
                    </div>
                    <div>
                        <label><strong>Email:</strong></label>
                        <p id="correo">-</p>
                    </div>
                    <div>
                        <label><strong>Matrícula:</strong></label>
                        <p id="matricula">-</p>
                    </div>
                    <div>
                        <label><strong>Teléfono:</strong></label>
                        <p id="telefono">-</p>
                    </div>
                </div>
            </div>

            <!-- Información Académica -->
            <div class="card" style="margin-bottom:20px;">
                <h2>Información Académica</h2>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-top:15px;">
                    <div>
                        <label><strong>Grado Académico:</strong></label>
                        <p id="grado_academico">-</p>
                    </div>
                    <div>
                        <label><strong>Carrera:</strong></label>
                        <p id="carrera_licenciatura">-</p>
                    </div>
                    <div>
                        <label><strong>Área de Formación:</strong></label>
                        <p id="area_formacion">-</p>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-top:15px;">
                    <div>
                        <label><strong>Institución (Licenciatura):</strong></label>
                        <p id="institucion_licenciatura">-</p>
                    </div>
                    <div>
                        <label><strong>Institución (Maestría):</strong></label>
                        <p id="institucion_maestria">-</p>
                    </div>
                </div>
            </div>

            <!-- Cursos Inscritos -->
            <div class="card" style="margin-bottom:20px;">
                <h2>Cursos Inscritos</h2>
                <div id="cursos-container" style="margin-top:15px;">
                    <p style="color:#666;">No hay cursos registrados</p>
                </div>
            </div>

            <!-- Cuestionarios -->
            <div class="card" style="margin-bottom:20px;">
                <h2>Cuestionarios Respondidos</h2>
                <div id="cuestionarios-container" style="margin-top:15px;">
                    <p style="color:#666;">No hay cuestionarios registrados</p>
                </div>
            </div>

            <!-- Notas -->
            <div class="card">
                <h2>Notas Adicionales</h2>
                <div id="notas-container" style="margin-top:15px;">
                    <p id="notas" style="color:#666;">Sin notas</p>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
.card {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.card h2 {
    margin: 0 0 15px 0;
    color: #1B365D;
    font-size: 1.3em;
}
.card label {
    color: #666;
    font-size: 0.9em;
}
.card p {
    margin: 5px 0 0 0;
    font-size: 1em;
}
.curso-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
    margin-bottom: 10px;
    border-left: 4px solid #1B365D;
}
</style>

<script>
const estudianteId = <?= $estudiante_id ?>;

async function cargarEstudiante() {
    try {
        const response = await fetch(`/api/controllers/estudiantes.php?id=${estudianteId}`);
        const data = await response.json();

        if (data.success) {
            mostrarEstudiante(data.data);
        } else {
            alert('Error al cargar estudiante');
            history.back();
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar estudiante');
        history.back();
    }
}

function mostrarEstudiante(estudiante) {
    // Título
    document.getElementById('studentName').textContent = estudiante.nombre;

    // Información personal
    document.getElementById('nombre').textContent = estudiante.nombre;
    document.getElementById('correo').textContent = estudiante.correo;
    document.getElementById('matricula').textContent = estudiante.matricula || 'No asignada';
    document.getElementById('telefono').textContent = estudiante.telefono || 'No registrado';

    // Información académica
    document.getElementById('grado_academico').textContent = estudiante.grado_academico || '-';
    document.getElementById('carrera_licenciatura').textContent = estudiante.carrera_licenciatura || '-';
    document.getElementById('area_formacion').textContent = estudiante.area_formacion || '-';
    document.getElementById('institucion_licenciatura').textContent = estudiante.institucion_licenciatura || '-';
    document.getElementById('institucion_maestria').textContent = estudiante.institucion_maestria || '-';

    // Cursos
    if (estudiante.cursos && estudiante.cursos.length > 0) {
        const cursosHtml = estudiante.cursos.map(c => `
            <div class="curso-item">
                <div style="display:flex;justify-content:space-between;align-items:start;">
                    <div>
                        <h3 style="margin:0;font-size:1.1em;color:#1B365D;">${escapeHtml(c.nombre)}</h3>
                        <p style="margin:5px 0;color:#666;">
                            <strong>Código:</strong> ${escapeHtml(c.codigo)} |
                            <strong>Periodo:</strong> ${escapeHtml(c.periodo || '-')}
                        </p>
                        <p style="margin:5px 0;color:#666;">
                            ${new Date(c.fecha_inicio).toLocaleDateString()} - ${new Date(c.fecha_fin).toLocaleDateString()}
                        </p>
                    </div>
                    <div style="text-align:right;">
                        ${c.calificacion_final ? `
                            <p style="margin:0;font-size:1.5em;font-weight:bold;color:${c.aprobado ? '#16a34a' : '#dc2626'};">
                                ${c.calificacion_final}
                            </p>
                            <span class="badge" style="background:${c.aprobado ? '#16a34a' : '#dc2626'};">
                                ${c.aprobado ? 'Aprobado' : 'Reprobado'}
                            </span>
                        ` : '<span class="badge" style="background:#6b7280;">En curso</span>'}
                    </div>
                </div>
            </div>
        `).join('');
        document.getElementById('cursos-container').innerHTML = cursosHtml;
    }

    // Cuestionarios
    if (estudiante.cuestionarios && estudiante.cuestionarios.length > 0) {
        const cuestionariosHtml = estudiante.cuestionarios.map(q => `
            <div class="curso-item">
                <h3 style="margin:0;font-size:1.1em;color:#1B365D;">${escapeHtml(q.nombre)}</h3>
                <p style="margin:5px 0;color:#666;">
                    <strong>Puntaje:</strong> ${q.puntaje}/12 |
                    <strong>Fecha:</strong> ${new Date(q.fecha).toLocaleDateString()}
                </p>
            </div>
        `).join('');
        document.getElementById('cuestionarios-container').innerHTML = cuestionariosHtml;
    }

    // Notas
    if (estudiante.notas) {
        document.getElementById('notas').textContent = estudiante.notas;
        document.getElementById('notas').style.color = '#333';
    }

    // Mostrar contenido
    document.getElementById('loading').style.display = 'none';
    document.getElementById('content').style.display = 'block';
}

function editarEstudiante() {
    showToast('Función de edición próximamente', 'info');
}

function exportarPDF() {
    showToast('Exportación próximamente', 'info');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showToast(message, type = 'success') {
    alert(message);
}

// Cargar al iniciar
cargarEstudiante();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
