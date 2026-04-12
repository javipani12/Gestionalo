<?php
    require_once './../app/views/layout/header.php';
?>

    <div class="dashboard-page tools-page">
        <section class="dashboard-card dashboard-card--main tools-shell" aria-labelledby="tools-title">
            <div class="tools-hero">
                <h1 id="tools-title">Herramientas financieras</h1>
                <p class="dashboard-lead">Accede rapido a tus utilidades de analisis y planificacion para mejorar tus decisiones economicas.</p>
            </div>

            <div class="tools-grid">
                <article class="tool-card" aria-labelledby="tool-hipoteca-title">
                    <img
                        src="./img/herramientas/hipoteca.jpg"
                        alt="Calculadora de hipoteca"
                        class="tool-card__image"
                        loading="lazy">
                    <div class="tool-card__content">
                        <h2 id="tool-hipoteca-title">Calculadora de hipoteca</h2>
                        <p>Calcula cuota mensual, intereses y coste final para evaluar distintas opciones de financiacion.</p>
                        <a class="btn transactions-action-btn" href="?controller=tool&action=mostrarCalculadoraHipoteca">Acceder</a>
                    </div>
                </article>

                <article class="tool-card" aria-labelledby="tool-ahorro-title">
                    <img
                        src="./img/herramientas/ahorro.jpg"
                        alt="Objetivos de ahorro"
                        class="tool-card__image"
                        loading="lazy">
                    <div class="tool-card__content">
                        <h2 id="tool-ahorro-title">Objetivos de ahorro</h2>
                        <p>Define metas economicas, plazos y seguimiento para mantener un plan de ahorro constante.</p>
                        <a class="btn transactions-action-btn" href="?controller=tool&action=mostrarObjetivosAhorro">Acceder</a>
                    </div>
                </article>

                <article class="tool-card" aria-labelledby="tool-graficos-title">
                    <img
                        src="./img/herramientas/grafico.jpg"
                        alt="Graficos financieros"
                        class="tool-card__image"
                        loading="lazy">
                    <div class="tool-card__content">
                        <h2 id="tool-graficos-title">Graficos</h2>
                        <p>Explora visualizaciones de ingresos, gastos y balance para detectar patrones y oportunidades.</p>
                        <a class="btn transactions-action-btn" href="?controller=tool&action=mostrarGraficos">Acceder</a>
                    </div>
                </article>

                <article class="tool-card" aria-labelledby="tool-informes-title">
                    <img
                        src="./img/herramientas/informe.jpg"
                        alt="Informes generados"
                        class="tool-card__image"
                        loading="lazy">
                    <div class="tool-card__content">
                        <h2 id="tool-informes-title">Informes generados</h2>
                        <p>Consulta los reportes disponibles y revisa la evolucion de tus finanzas en periodos concretos.</p>
                        <a class="btn transactions-action-btn" href="?controller=tool&action=mostrarInformesGenerados">Acceder</a>
                    </div>
                </article>
            </div>
        </section>
    </div>

<?php
    require_once './../app/views/layout/footer.php';
?>