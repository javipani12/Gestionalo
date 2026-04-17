<?php
    require_once './../app/views/layout/header.php';

    $esEdicion = isset($transaccion['id_transaccion']) && !empty($transaccion['id_transaccion']);
    $fechaMovimiento = '';
    if (!empty($transaccion['fecha_movimiento'])) {
        $fechaMovimiento = date('Y-m-d', strtotime($transaccion['fecha_movimiento']));
    }
    $limiteDiarioTransacciones = $limiteDiarioTransacciones ?? 20;
    $transaccionesHoy = $transaccionesHoy ?? 0;
    $puedeCrearTransaccion = $puedeCrearTransaccion ?? true;
    $objetivosEnCurso = $objetivosEnCurso ?? [];
    $idTipoPreseleccionado = (int)($idTipoPreseleccionado ?? 0);
    $idObjetivoPreseleccionado = (int)($idObjetivoPreseleccionado ?? 0);
    $tipoSeleccionado = (int)($transaccion['id_tipo'] ?? $idTipoPreseleccionado);
    $objetivoSeleccionado = (int)($transaccion['id_objetivo'] ?? $idObjetivoPreseleccionado);
    $redirigirAObjetivoId = (int)($redirigirAObjetivoId ?? 0);
    $redirigirPaginaHistorial = max(1, (int)($redirigirPaginaHistorial ?? 1));
    $urlCancelar = 'index.php?controller=transaction&action=mostrarTransaccionesUsuario';
    if ($redirigirAObjetivoId > 0) {
        $urlCancelar = 'index.php?controller=goal&action=mostrarDetalleObjetivo&id_objetivo=' . $redirigirAObjetivoId . '&pagina_historial=' . $redirigirPaginaHistorial;
    }

    $idsTiposInternos = [];
    foreach ($tiposMovimiento as $tipoMovimientoItem) {
        $nombreTipoMovimiento = strtolower(trim((string)($tipoMovimientoItem['nombre'] ?? '')));
        if ($nombreTipoMovimiento === 'transferencia interna aporte' || $nombreTipoMovimiento === 'transferencia interna retiro') {
            $idsTiposInternos[] = (int)($tipoMovimientoItem['id'] ?? 0);
        }
    }
    $esTipoInternoSeleccionado = in_array($tipoSeleccionado, $idsTiposInternos, true);
?>
    <div class="dashboard-page">
        <?php if (!$esEdicion && isset($transaccionesHoy, $limiteDiarioTransacciones) && !$puedeCrearTransaccion): ?>
            <div class="alert error">Has alcanzado el límite diario de <?= (int)$limiteDiarioTransacciones ?> transacciones. Podrás crear más mañana.</div>
        <?php elseif (!$esEdicion && isset($transaccionesHoy, $limiteDiarioTransacciones) && $transaccionesHoy > 0): ?>
            <div class="alert success">Hoy has creado <?= (int)$transaccionesHoy ?> de <?= (int)$limiteDiarioTransacciones ?> transacciones permitidas.</div>
        <?php endif; ?>
        <section class="dashboard-card dashboard-card--main">
            <h1><?= $esEdicion ? 'Editar Transacción' : 'Crear Nueva Transacción' ?></h1>
            <form action="index.php?controller=transaction&action=guardarTransaccion" method="POST" class="transaction-form">
                <input type="hidden" name="id_transaccion" value="<?= $transaccion['id_transaccion'] ?? '' ?>">
                <input type="hidden" name="redirigir_objetivo_id" value="<?= $redirigirAObjetivoId ?>">
                <input type="hidden" name="redirigir_pagina_historial" value="<?= $redirigirPaginaHistorial ?>">
                <div class="transaction-form__grid">
                    <fieldset class="transaction-form__section transaction-form__section--class">
                        <legend>Clasificación</legend>

                        <!-- Tipo de movimiento -->
                        <div class="transaction-form__field">
                            <label for="id_tipo">Tipo de movimiento</label>
                            <select name="id_tipo" id="id_tipo" required>
                                <option value="">Selecciona un tipo</option>
                                <?php foreach ($tiposMovimiento as $tipo): ?>
                                    <?php $nombreTipo = strtolower(trim((string)($tipo['nombre'] ?? ''))); ?>
                                    <option
                                        value="<?= $tipo['id'] ?>"
                                        data-is-internal="<?= ($nombreTipo === 'transferencia interna aporte' || $nombreTipo === 'transferencia interna retiro') ? '1' : '0' ?>"
                                        <?= $tipoSeleccionado === (int)$tipo['id'] ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars(ucfirst($tipo['nombre'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Fin Tipo de movimiento -->

                        <!-- Objetivo -->
                        <div class="transaction-form__field" id="id_objetivo_field" style="display:<?= $esTipoInternoSeleccionado ? 'block' : 'none' ?>;">
                            <label for="id_objetivo">Objetivo en curso</label>
                            <select name="id_objetivo" id="id_objetivo" <?= $esTipoInternoSeleccionado ? 'required' : 'disabled' ?>>
                                <option value="">Selecciona un objetivo</option>
                                <?php foreach ($objetivosEnCurso as $objetivo): ?>
                                    <option value="<?= (int)$objetivo['id_objetivo'] ?>" <?= $objetivoSeleccionado === (int)$objetivo['id_objetivo'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string)$objetivo['nombre_objetivo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Fin Objetivo -->

                        <!-- Categoría -->
                        <div class="transaction-form__field" id="id_categoria_field" style="display:<?= $esTipoInternoSeleccionado ? 'none' : 'block' ?>;">
                            <label for="id_categoria">Categoría</label>
                            <select name="id_categoria" id="id_categoria" <?= $esTipoInternoSeleccionado ? 'disabled' : '' ?> required>
                                <option value="">Selecciona una categoría</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= $categoria['id'] ?>" <?= (isset($transaccion) && $transaccion['id_categoria'] == $categoria['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(ucfirst($categoria['nombre'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Fin Categoría -->

                        <!-- Subcategoría -->
                        <div class="transaction-form__field" id="id_subcategoria_field" style="display:<?= $esTipoInternoSeleccionado ? 'none' : 'block' ?>;">
                            <label for="id_subcategoria">Subcategoría</label>
                            <select name="id_subcategoria" id="id_subcategoria" <?= $esTipoInternoSeleccionado ? 'disabled' : '' ?> required>
                                <option value="">Selecciona una subcategoría</option>
                                <?php foreach ($subcategorias as $subcategoria): ?>
                                    <option
                                        value="<?= $subcategoria['id'] ?>"
                                        data-categoria-id="<?= (int)$subcategoria['id_categoria'] ?>"
                                        <?= (isset($transaccion) && $transaccion['id_subcategoria'] == $subcategoria['id']) ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars(ucfirst($subcategoria['nombre'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Fin Subcategoría -->
                    </fieldset>

                    <fieldset class="transaction-form__section">
                        <legend>Detalle</legend>
                        
                        <!-- Concepto -->
                        <div class="transaction-form__field">
                            <label for="concepto">Concepto</label>
                            <input type="text" name="concepto" id="concepto" placeholder="Ej. Compra supermercado" value="<?= htmlspecialchars($transaccion['concepto'] ?? '') ?>" maxlength="255" required>
                        </div>
                        <!-- Fin Concepto -->
                    </fieldset>

                    <fieldset class="transaction-form__section transaction-form__section--two-cols">
                        <legend>Importe y fecha</legend>

                        <!-- Importe -->
                        <div class="transaction-form__field">
                            <label for="importe">Importe (€)</label>
                            <input type="number" name="importe" id="importe" placeholder="0.00" min="0" step="0.01" value="<?= htmlspecialchars($transaccion['importe'] ?? '') ?>" required>
                        </div>
                        <!-- Fin Importe -->

                        <!-- Fecha -->
                        <div class="transaction-form__field">
                            <label for="fecha_movimiento">Fecha</label>
                            <input type="date" name="fecha_movimiento" id="fecha_movimiento" value="<?= $fechaMovimiento ?>" required>
                        </div>
                        <!-- Fin Fecha -->
                    </fieldset>

                    <fieldset class="transaction-form__section">
                        <legend>Pago</legend>
                        
                        <!-- Método de pago -->
                        <div class="transaction-form__field">
                            <label for="id_metodo">Método de pago</label>
                            <select name="id_metodo" id="id_metodo" required>
                                <option value="">Selecciona un método</option>
                                <?php foreach ($metodosPago as $metodo): ?>
                                    <option value="<?= $metodo['id'] ?>" <?= (isset($transaccion) && $transaccion['id_metodo'] == $metodo['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(ucfirst($metodo['nombre'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Fin Método de pago -->
                    </fieldset>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn btn-volver" onclick="window.location.href='<?= htmlspecialchars($urlCancelar) ?>'">Cancelar</button>
                    <button type="submit" class="btn btn-enviar" <?= (!$esEdicion && !$puedeCrearTransaccion) ? 'disabled' : '' ?>><?= $esEdicion ? 'Actualizar transacción' : 'Crear transacción' ?></button>
                </div>
            </form>
        </section>
    </div>
<?php
    require_once './../app/views/layout/footer.php';
?>
<script src="./js/transaction-form.js" defer></script>