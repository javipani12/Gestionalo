<?php
    require_once './../app/views/layout/header.php';
?>

    <div class="dashboard-page">
        <h1>Bienvenido al panel de control <?= $_SESSION['usuario']['nombre'] ?></h1>

        <div class="dashboard-grid"> <!-- Contenedor grid con dos columnas -->
            <section class="dashboard-card dashboard-card--main">
                <!-- Columna izquierda: últimas 10 transacciones -->
                <h2>Últimas transacciones</h2>
                <p class="muted">Concepto | Fecha | Importe</p>
            </section>

            <div class="dashboard-col">
                <section class="dashboard-card">
                    <!-- Fila superior: Objetivos actuales -->
                    <h2>Objetivos actuales</h2>
                    <p class="muted">Cantidad ahorrada | Cantidad restante | % alcanzado</p>
                </section>

                <section class="dashboard-card">
                    <!-- Fila inferior: Gráficos de progreso -->
                    <h2>Últimos informes</h2>
                    <p class="muted">Título | Fecha | Enlace de descarga</p>
                </section>
            </div>
        </div>
    </div>

<?php 
    require_once './../app/views/layout/footer.php';
?>