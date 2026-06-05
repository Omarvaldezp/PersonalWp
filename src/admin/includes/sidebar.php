<?php
/**
 * Sidebar común para el panel de administración
 */

// Obtener la página actual
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <h2>Admin Panel</h2>
        <p>Bienvenido, <?= htmlspecialchars($user['nombre_completo'] ?? 'Admin') ?></p>
    </div>

    <nav class="sidebar-nav">
        <a href="index.php" class="nav-item <?= $current_page === 'index.php' ? 'active' : '' ?>">
            <span>📊</span> Dashboard
        </a>

        <a href="blog.php" class="nav-item <?= $current_page === 'blog.php' ? 'active' : '' ?>">
            <span>📝</span> Blog Posts
        </a>

        <a href="courses.php" class="nav-item <?= $current_page === 'courses.php' ? 'active' : '' ?>">
            <span>🎓</span> Cursos
        </a>

        <a href="research.php" class="nav-item <?= $current_page === 'research.php' ? 'active' : '' ?>">
            <span>🔬</span> Investigaciones
        </a>

        <a href="contacts.php" class="nav-item <?= $current_page === 'contacts.php' ? 'active' : '' ?>">
            <span>📧</span> Contactos
        </a>

        <a href="newsletter.php" class="nav-item <?= $current_page === 'newsletter.php' ? 'active' : '' ?>">
            <span>📰</span> Newsletter
        </a>

        <hr style="margin: 1rem 0; border: none; border-top: 1px solid rgba(255,255,255,0.1);">

        <div style="padding: 0.5rem 1rem; color: rgba(255,255,255,0.6); font-size: 0.85em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
            Sistema Académico
        </div>

        <a href="estudiantes/index.php" class="nav-item <?= in_array($current_page, ['index.php', 'detalle.php']) && strpos($_SERVER['PHP_SELF'], 'estudiantes') !== false ? 'active' : '' ?>">
            <span>👥</span> Estudiantes
        </a>

        <a href="cursos-academicos/index.php" class="nav-item <?= in_array($current_page, ['index.php', 'crear.php', 'inscripciones.php']) && strpos($_SERVER['PHP_SELF'], 'cursos-academicos') !== false ? 'active' : '' ?>">
            <span>🎓</span> Cursos Académicos
        </a>

        <hr style="margin: 1rem 0; border: none; border-top: 1px solid rgba(255,255,255,0.1);">

        <a href="diagnostico-disde.php" class="nav-item <?= $current_page === 'diagnostico-disde.php' ? 'active' : '' ?>">
            <span>📊</span> Diagnóstico DISDE
        </a>

        <hr style="margin: 1rem 0; border: none; border-top: 1px solid rgba(255,255,255,0.1);">

        <a href="#" onclick="logout(); return false;" class="nav-item logout">
            <span>🚪</span> Cerrar Sesión
        </a>
    </nav>
</aside>

<script>
async function logout() {
    if (await confirmDialog({
        title: '¿Cerrar sesión?',
        message: '¿Estás seguro que deseas salir?',
        confirmText: 'Sí, salir',
        cancelText: 'Cancelar'
    })) {
        try {
            const response = await fetch('/api/auth/login.php?action=logout', {
                method: 'POST'
            });

            if (response.ok) {
                window.location.href = 'login.php';
            }
        } catch (error) {
            showToast('Error al cerrar sesión', 'error');
        }
    }
}
</script>
