<?php
    require_once './../app/views/layout/header.php';
?>

    <div class="dashboard-page">
        <section class="dashboard-card dashboard-card--main">
            <h1>Crear nueva consulta</h1>
            <form action="index.php?controller=contact&action=enviarConsulta" method="POST" class="transaction-form">
                <div class="transaction-form__grid">
                    <fieldset class="transaction-form__section">
                        <legend>Asunto</legend>

                        <div class="transaction-form__field">
                            <label for="asunto">Selecciona el motivo de tu consulta</label>
                            <select name="asunto" id="asunto" required>
                                <option value="">-- Selecciona un asunto --</option>
                                <?php foreach ($asuntos as $asunto): ?>
                                    <option value="<?= (int)$asunto['id'] ?>">
                                        <?= htmlspecialchars($asunto['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </fieldset>

                    <fieldset class="transaction-form__section">
                        <legend>Detalle de la consulta</legend>

                        <div class="transaction-form__field">
                            <label for="comentario">Describe tu consulta</label>
                            <textarea id="comentario" name="comentario" rows="6" maxlength="2000" placeholder="Escribe aquí los detalles de tu consulta..." required></textarea>
                        </div>
                    </fieldset>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn btn-volver" onclick="window.location.href='index.php?controller=contact&action=mostrarMisConsultas'">Cancelar</button>
                    <button type="submit" class="btn btn-enviar" <?= (!empty($puedeEnviarConsulta) || !isset($puedeEnviarConsulta)) ? '' : 'disabled' ?>>Enviar consulta</button>
                </div>
            </form>
        </section>
    </div>

<?php
    require_once './../app/views/layout/footer.php';
?>