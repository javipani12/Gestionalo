<?php
    require_once './../app/views/layout/header.php';

    $objetivo = $objetivo ?? [];
    $esEdicion = !empty($objetivo['id_objetivo']);
    $fechaInicio = '';
    $fechaLimite = '';

    if (!empty($objetivo['fecha_inicio'])) {
        $fechaInicio = date('Y-m-d', strtotime($objetivo['fecha_inicio']));
    }

    if (!empty($objetivo['fecha_limite'])) {
        $fechaLimite = date('Y-m-d', strtotime($objetivo['fecha_limite']));
    }
?>

    <div class="dashboard-page">
        <section class="dashboard-card dashboard-card--main">
            <div class="transactions-toolbar">
                <div>
                    <h1><?= $esEdicion ? 'Editar objetivo de ahorro' : 'Crear objetivo de ahorro' ?></h1>
                    <p class="dashboard-lead"><?= $esEdicion
                        ? 'Actualiza los datos del objetivo para mantener tu plan de ahorro al día.'
                        : 'Define tu meta, fechas y preferencias para empezar a apartar dinero de forma controlada.'
                    ?></p>
                </div>

                <a class="btn transactions-action-btn" href="?controller=goal&action=mostrarObjetivosAhorro">Volver a objetivos</a>
            </div>

            <form action="index.php?controller=goal&action=guardarObjetivo" method="POST" class="transaction-form">
                <input type="hidden" name="id_objetivo" value="<?= (int)($objetivo['id_objetivo'] ?? 0) ?>">

                <div class="transaction-form__grid">
                    <fieldset class="transaction-form__section">
                        <legend>Datos del objetivo</legend>

                        <div class="transaction-form__field">
                            <label for="nombre_objetivo">Nombre del objetivo</label>
                            <input
                                type="text"
                                name="nombre_objetivo"
                                id="nombre_objetivo"
                                maxlength="150"
                                placeholder="Ej. Viaje a Japón"
                                value="<?= htmlspecialchars((string)($objetivo['nombre_objetivo'] ?? '')) ?>"
                                required
                            >
                        </div>

                        <div class="transaction-form__field">
                            <label for="descripcion">Descripción</label>
                            <textarea
                                name="descripcion"
                                id="descripcion"
                                rows="5"
                                maxlength="2000"
                                placeholder="Añade una nota opcional para recordar el propósito del objetivo"
                            ><?= htmlspecialchars((string)($objetivo['descripcion'] ?? '')) ?></textarea>
                        </div>
                    </fieldset>

                    <fieldset class="transaction-form__section transaction-form__section--two-cols">
                        <legend>Importe y fechas</legend>

                        <div class="transaction-form__field">
                            <label for="cantidad_meta">Cantidad meta (€)</label>
                            <input
                                type="number"
                                name="cantidad_meta"
                                id="cantidad_meta"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                value="<?= htmlspecialchars((string)($objetivo['cantidad_meta'] ?? '')) ?>"
                                required
                            >
                        </div>

                        <div class="transaction-form__field">
                            <label for="fecha_inicio">Fecha de inicio</label>
                            <input
                                type="date"
                                name="fecha_inicio"
                                id="fecha_inicio"
                                value="<?= htmlspecialchars($fechaInicio) ?>"
                            >
                        </div>

                        <div class="transaction-form__field">
                            <label for="fecha_limite">Fecha límite</label>
                            <input
                                type="date"
                                name="fecha_limite"
                                id="fecha_limite"
                                value="<?= htmlspecialchars($fechaLimite) ?>"
                            >
                        </div>
                    </fieldset>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn btn-volver" onclick="window.location.href='?controller=goal&action=mostrarObjetivosAhorro'">Cancelar</button>
                    <button type="submit" class="btn btn-enviar"><?= $esEdicion ? 'Actualizar objetivo' : 'Guardar objetivo' ?></button>
                </div>
            </form>
        </section>
    </div>

<?php
    require_once './../app/views/layout/footer.php';
?>