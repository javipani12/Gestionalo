<?php
    require_once './../app/views/layout/header.php';
?>

    <div class="dashboard-page tools-page">
        <section class="dashboard-card dashboard-card--main tool-detail-card">
            <h1><?= htmlspecialchars($nombreHerramienta) ?></h1>
            <p class="dashboard-lead"><?= htmlspecialchars($descripcionHerramienta) ?></p>
            <p class="muted">Modulo en preparacion. Esta vista ya queda enlazada desde Herramientas para integrarlo sin errores en la navegacion.</p>

            <div class="tool-detail-actions">
                <a class="btn transactions-action-btn" href="?controller=tool&action=mostrarHerramientas">Volver a herramientas</a>
                <a class="link" href="?controller=dashboard&action=mostrarDashboard">Ir al inicio</a>
            </div>
        </section>
    </div>

<?php
    require_once './../app/views/layout/footer.php';
?>
