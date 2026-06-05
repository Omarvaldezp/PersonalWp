<?php
/**
 * Footer común para el panel de administración
 */
?>

    <!-- Scripts globales -->
    <script src="/admin/assets/admin.js"></script>

    <?php if (isset($extra_js)): ?>
        <?php foreach ($extra_js as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
