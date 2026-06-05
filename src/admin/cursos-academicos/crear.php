<?php
/**
 * Admin: Crear/Editar Curso Académico
 */

require_once __DIR__ . '/../../api/auth/Auth.php';

$auth = new Auth();
$auth->requireAuthPage();
$user = $auth->user();

$curso_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$page_title = $curso_id ? 'Editar Curso' : 'Nuevo Curso';
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
                <h1><?= $curso_id ? 'Editar' : 'Nuevo' ?> Curso</h1>
            </div>
        </header>

        <div class="card" style="max-width:800px;margin:0 auto;padding:30px;">
            <form id="cursoForm" onsubmit="guardarCurso(event)">
                <div style="margin-bottom:20px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Nombre del Curso *</label>
                    <input type="text" id="nombre" required placeholder="Ej: DISDE Propedéutico 2026-A"
                           style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;font-weight:bold;">Código *</label>
                        <input type="text" id="codigo" required placeholder="DISDE-PROP-2026A"
                               style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;font-weight:bold;">Periodo</label>
                        <input type="text" id="periodo" placeholder="2026-A"
                               style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;">
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Descripción</label>
                    <textarea id="descripcion" rows="4" placeholder="Descripción del curso..."
                              style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;"></textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;font-weight:bold;">Fecha Inicio *</label>
                        <input type="date" id="fecha_inicio" required
                               style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;font-weight:bold;">Fecha Fin *</label>
                        <input type="date" id="fecha_fin" required
                               style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;font-weight:bold;">Cupo Máximo</label>
                        <input type="number" id="cupo_maximo" placeholder="30"
                               style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;font-weight:bold;">Estado *</label>
                        <select id="estado" required
                                style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;">
                            <option value="planificado">Planificado</option>
                            <option value="activo" selected>Activo</option>
                            <option value="finalizado">Finalizado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top:30px;text-align:right;">
                    <button type="button" class="btn-secondary" onclick="history.back()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-primary" style="margin-left:10px;">
                        <i class="fas fa-save"></i> Guardar Curso
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<style>
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
</style>

<script>
const cursoId = <?= $curso_id ? $curso_id : 'null' ?>;

async function cargarCurso() {
    if (!cursoId) return;

    try {
        const response = await fetch(`/api/controllers/cursos-instancias.php?id=${cursoId}`);
        const data = await response.json();

        if (data.success) {
            const curso = data.data;
            document.getElementById('nombre').value = curso.nombre;
            document.getElementById('codigo').value = curso.codigo;
            document.getElementById('periodo').value = curso.periodo || '';
            document.getElementById('descripcion').value = curso.descripcion || '';
            document.getElementById('fecha_inicio').value = curso.fecha_inicio;
            document.getElementById('fecha_fin').value = curso.fecha_fin;
            document.getElementById('cupo_maximo').value = curso.cupo_maximo || '';
            document.getElementById('estado').value = curso.estado;
        }
    } catch (error) {
        console.error('Error cargando curso:', error);
        alert('Error al cargar el curso');
    }
}

async function guardarCurso(e) {
    e.preventDefault();

    const data = {
        nombre: document.getElementById('nombre').value,
        codigo: document.getElementById('codigo').value,
        periodo: document.getElementById('periodo').value || null,
        descripcion: document.getElementById('descripcion').value || null,
        fecha_inicio: document.getElementById('fecha_inicio').value,
        fecha_fin: document.getElementById('fecha_fin').value,
        cupo_maximo: document.getElementById('cupo_maximo').value || null,
        estado: document.getElementById('estado').value
    };

    try {
        const url = cursoId
            ? `/api/controllers/cursos-instancias.php?id=${cursoId}`
            : '/api/controllers/cursos-instancias.php';

        const response = await fetch(url, {
            method: cursoId ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message || 'Curso guardado exitosamente');
            window.location.href = 'index.php';
        } else {
            alert(result.message || 'Error al guardar el curso');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al guardar el curso');
    }
}

// Cargar si es edición
if (cursoId) {
    cargarCurso();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
