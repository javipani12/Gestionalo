<?php
    require_once './../app/views/layout/header_admin.php';

    $nombreAdmin = $_SESSION['usuario']['nombre'] ?? 'Administrador';

    $formatearVariacion = function($variacion) {
        $prefijo = $variacion > 0 ? '+' : '';
        return $prefijo . number_format((float)$variacion, 1, ',', '.') . '%';
    };
?>

    <div class="dashboard-page">
        <section class="dashboard-card dashboard-card--main dashboard-admin-shell">
            <div class="dashboard-hero">
                <div>
                    <h1>Bienvenido al panel de control <?= htmlspecialchars($nombreAdmin) ?></h1>
                    <p class="dashboard-lead">Resumen operativo del sistema, comparativa mensual y actividad reciente.</p>
                </div>
                <p class="dashboard-updated muted">Última actualización: <?= htmlspecialchars($ultimaActualizacion ?? date('d/m/Y H:i')) ?></p>
            </div>

            <section class="dashboard-section">
                <div class="dashboard-section__head">
                    <h2>Resumen general</h2>
                </div>

                <div class="dashboard-stats-grid">
                    <article class="dashboard-stat-card">
                        <span class="dashboard-stat-label">Usuarios activos</span>
                        <strong class="dashboard-stat-value"><?= number_format((int)($dashboardStats['usuarios_activos'] ?? 0), 0, ',', '.') ?></strong>
                    </article>

                    <article class="dashboard-stat-card">
                        <span class="dashboard-stat-label">Consultas totales</span>
                        <strong class="dashboard-stat-value"><?= number_format((int)($dashboardStats['consultas_totales'] ?? 0), 0, ',', '.') ?></strong>
                    </article>

                    <article class="dashboard-stat-card dashboard-stat-card--warning">
                        <span class="dashboard-stat-label">Consultas pendientes</span>
                        <strong class="dashboard-stat-value"><?= number_format((int)($dashboardStats['consultas_pendientes'] ?? 0), 0, ',', '.') ?></strong>
                    </article>

                    <article class="dashboard-stat-card dashboard-stat-card--accent">
                        <span class="dashboard-stat-label">Total de transacciones</span>
                        <strong class="dashboard-stat-value"><?= number_format((int)($dashboardStats['transacciones_totales'] ?? 0), 0, ',', '.') ?></strong>
                    </article>
                </div>
            </section>

            <section class="dashboard-section">
                <div class="dashboard-section__head">
                    <h2>Comparativa con el mes anterior</h2>
                </div>

                <div class="dashboard-comparisons-grid">
                    <?php foreach ($comparativas as $comparativa): ?>
                        <?php
                            $variacion = (float)($comparativa['variacion'] ?? 0);
                            $variacionClase = $variacion > 0 ? 'dashboard-variation--up' : ($variacion < 0 ? 'dashboard-variation--down' : 'dashboard-variation--flat');
                        ?>
                        <article class="dashboard-comparison-card">
                            <span class="dashboard-stat-label"><?= htmlspecialchars($comparativa['titulo']) ?></span>
                            <div class="dashboard-comparison-values">
                                <div>
                                    <small class="muted">Mes anterior</small>
                                    <strong><?= number_format((int)$comparativa['valor_anterior'], 0, ',', '.') ?></strong>
                                </div>
                                <div>
                                    <small class="muted">Mes actual</small>
                                    <strong><?= number_format((int)$comparativa['valor_actual'], 0, ',', '.') ?></strong>
                                </div>
                            </div>
                            <span class="dashboard-variation <?= $variacionClase ?>">
                                <?= htmlspecialchars($formatearVariacion($variacion)) ?>
                            </span>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="dashboard-section">
                <div class="dashboard-section__head">
                    <h2>Actividad reciente</h2>
                </div>

                <div class="dashboard-activity-grid">
                    <article class="dashboard-card dashboard-activity-card">
                        <div class="transactions-toolbar">
                            <h3>Últimas 10 consultas</h3>
                            <a class="btn transactions-action-btn" href="?controller=admin&action=mostrarConsultasAdmin">Ir a consultas generadas</a>
                        </div>
                        <?php if (!empty($ultimasConsultas)): ?>
                            <div class="dashboard-table-wrap">
                                <table class="dashboard-table dashboard-table--recent-pair">
                                    <thead>
                                        <tr>
                                            <th>Asunto</th>
                                            <th>Usuario</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ultimasConsultas as $consulta): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($consulta['asunto'] ?? '-') ?></strong><br>
                                                    <span class="muted dashboard-table__truncated"><?= htmlspecialchars($consulta['comentario'] ?? '') ?></span>
                                                </td>
                                                <td><?= htmlspecialchars(trim(($consulta['nombre'] ?? '') . ' ' . ($consulta['apellido1'] ?? ''))) ?></td>
                                                <td><span class="dashboard-pill"><?= htmlspecialchars($consulta['estado'] ?? '-') ?></span></td>
                                                <td><?= !empty($consulta['created_at']) ? date('d/m/Y H:i', strtotime($consulta['created_at'])) : '-' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="muted">No hay consultas recientes.</p>
                        <?php endif; ?>
                    </article>

                    <article class="dashboard-card dashboard-activity-card">
                        <div class="transactions-toolbar">
                            <h3>Últimos 10 usuarios registrados</h3>
                            <a class="btn transactions-action-btn" href="#">Ir a gestión usuarios</a>
                        </div>
                        <?php if (!empty($ultimosUsuarios)): ?>
                            <div class="dashboard-table-wrap">
                                <table class="dashboard-table dashboard-table--recent-users">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Registro</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ultimosUsuarios as $usuario): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars(trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido1'] ?? ''))) ?></strong></td>
                                                <td class="dashboard-table__email"><?= htmlspecialchars($usuario['email'] ?? '-') ?></td>
                                                <td><?= !empty($usuario['fecha_registro']) ? date('d/m/Y H:i', strtotime($usuario['fecha_registro'])) : '-' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="muted">No hay usuarios recientes.</p>
                        <?php endif; ?>
                    </article>

                </div>
            </section>
        </section>
    </div>

<?php 
    require_once './../app/views/layout/footer.php';
?>