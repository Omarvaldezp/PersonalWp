<?php
/**
 * Panel de Administración - Dashboard
 */

require_once __DIR__ . '/../api/auth/Auth.php';

$auth = new Auth();
$auth->requireAuth();

$user = $auth->user();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Dr. Omar Valdez</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
                <p>Bienvenido, <?= htmlspecialchars($user['nombre_completo']) ?></p>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="nav-item active">
                    <span>📊</span> Dashboard
                </a>
                <a href="blog.php" class="nav-item">
                    <span>📝</span> Blog Posts
                </a>
                <a href="courses.php" class="nav-item">
                    <span>🎓</span> Cursos
                </a>
                <a href="research.php" class="nav-item">
                    <span>🔬</span> Investigaciones
                </a>
                <a href="contacts.php" class="nav-item">
                    <span>📧</span> Contactos
                </a>
                <a href="newsletter.php" class="nav-item">
                    <span>📰</span> Newsletter
                </a>
                <a href="#" onclick="logout()" class="nav-item logout">
                    <span>🚪</span> Cerrar Sesión
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <h1>Dashboard</h1>
            </header>

            <div class="dashboard-grid">
                <div class="stat-card">
                    <h3>Blog Posts</h3>
                    <p class="stat-number" id="blog-count">-</p>
                    <a href="blog.php" class="stat-link">Ver todos →</a>
                </div>

                <div class="stat-card">
                    <h3>Cursos</h3>
                    <p class="stat-number" id="courses-count">-</p>
                    <a href="courses.php" class="stat-link">Ver todos →</a>
                </div>

                <div class="stat-card">
                    <h3>Investigaciones</h3>
                    <p class="stat-number" id="research-count">-</p>
                    <a href="research.php" class="stat-link">Ver todos →</a>
                </div>

                <div class="stat-card">
                    <h3>Contactos Nuevos</h3>
                    <p class="stat-number" id="contacts-count">-</p>
                    <a href="contacts.php" class="stat-link">Ver todos →</a>
                </div>
            </div>

            <div class="quick-actions">
                <h2>Acciones Rápidas</h2>
                <div class="actions-grid">
                    <button onclick="location.href='blog.php?action=new'" class="action-btn">
                        ➕ Nuevo Post
                    </button>
                    <button onclick="location.href='courses.php?action=new'" class="action-btn">
                        ➕ Nuevo Curso
                    </button>
                    <button onclick="location.href='research.php?action=new'" class="action-btn">
                        ➕ Nueva Investigación
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Cargar estadísticas
        async function loadStats() {
            try {
                const [blog, courses, research, contacts] = await Promise.all([
                    fetch('/api/controllers/blog.php?estado=publicado').then(r => r.json()),
                    fetch('/api/controllers/courses.php?activo=true').then(r => r.json()),
                    fetch('/api/controllers/research.php').then(r => r.json()),
                    fetch('/api/controllers/contact.php?estado=nuevo').then(r => r.json())
                ]);

                document.getElementById('blog-count').textContent = blog.data?.length || 0;
                document.getElementById('courses-count').textContent = courses.data?.length || 0;
                document.getElementById('research-count').textContent = research.data?.length || 0;
                document.getElementById('contacts-count').textContent = contacts.data?.items?.length || 0;
            } catch (error) {
                console.error('Error cargando estadísticas:', error);
            }
        }

        function logout() {
            if (confirm('¿Estás seguro que deseas cerrar sesión?')) {
                fetch('/api/auth/login.php?action=logout', {
                    method: 'POST'
                })
                .then(() => {
                    window.location.href = 'login.php';
                });
            }
        }

        loadStats();
    </script>
</body>
</html>
