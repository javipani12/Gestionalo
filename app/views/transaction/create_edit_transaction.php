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
                <div class="transaction-form__grid">
                    <fieldset class="transaction-form__section transaction-form__section--class">
                        <legend>Clasificación</legend>

                        <!-- Tipo de movimiento -->
                        <div class="transaction-form__field">
                            <label for="id_tipo">Tipo de movimiento</label>
                            <select name="id_tipo" id="id_tipo" required>
                                <option value="">Selecciona un tipo</option>
                                <?php foreach ($tiposMovimiento as $tipo): ?>
                                    <option value="<?= $tipo['id'] ?>" <?= (isset($transaccion) && $transaccion['id_tipo'] == $tipo['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(ucfirst($tipo['nombre'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Fin Tipo de movimiento -->

                        <!-- Categoría -->
                        <div class="transaction-form__field">
                            <label for="id_categoria">Categoría</label>
                            <select name="id_categoria" id="id_categoria" required>
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
                        <div class="transaction-form__field">
                            <label for="id_subcategoria">Subcategoría</label>
                            <select name="id_subcategoria" id="id_subcategoria" required>
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
                    <button type="button" class="btn btn-volver" onclick="window.location.href='index.php?controller=transaction&action=mostrarTransaccionesUsuario'">Cancelar</button>
                    <button type="submit" class="btn btn-enviar" <?= (!$esEdicion && !$puedeCrearTransaccion) ? 'disabled' : '' ?>><?= $esEdicion ? 'Actualizar transacción' : 'Crear transacción' ?></button>
                </div>
            </form>
        </section>
    </div>
<?php
    require_once './../app/views/layout/footer.php';
?>
<script src="./js/transaction-form.js" defer></script>