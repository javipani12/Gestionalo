<?php
    require_once './../app/views/layout/header.php';
?>

    <div class="dashboard-page about-page">
        <section class="dashboard-card dashboard-card--main about-shell">
            <h1>Sobre nosotros</h1>
            <p class="dashboard-lead">Conoce un poco más sobre el proyecto y dónde encontrarnos.</p>

            <div class="about-grid">
                <section class="dashboard-card dashboard-card--main about-content" aria-labelledby="about-texto-title">
                    <h2 id="about-texto-title">Nuestra historia</h2>
                    <p class="about-story__text">Gestionalo surge de una necesidad muy común: entender mejor en qué gastamos nuestro dinero y cómo podemos gestionarlo de forma más eficiente.</p>
                    <p class="about-story__text">La aplicación está pensada para ayudar a los usuarios a registrar sus movimientos, visualizar su situación financiera y mejorar sus hábitos de ahorro sin complicaciones.</p>
                    <p class="about-story__text">Además, Gestionalo busca ir más allá del simple registro de datos, incorporando herramientas que permitan interpretar la información y convertirla en decisiones útiles para mejorar la economía personal.</p>
                    <p class="about-story__text about-story__text--highlight">Nuestro propósito es ofrecer una herramienta clara, útil e intuitiva que haga más fácil tomar decisiones económicas en el día a día.</p>
                </section>

                <section class="dashboard-card about-map" aria-labelledby="about-map-title">
                    <h2 id="about-map-title">Nuestra ubicación</h2>
                    <div class="about-map__frame-wrap">
                        <iframe
                            title="Ubicacion de IES Ágora"
                            src="https://www.google.com/maps?q=39.4661801,-6.3855013&amp;z=17&amp;output=embed"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <p class="muted">IES Ágora, Cáceres</p>
                    <p class="muted"><a class="link" href="https://www.google.com/maps/place/IES+%C3%81gora/@39.4660857,-6.3852794,252m/data=!3m1!1e3!4m6!3m5!1s0xd15dfc2c4bf80f1:0x111db258bf612c6f!8m2!3d39.4661801!4d-6.3855013!16s%2Fg%2F1tdy145d?authuser=0&amp;entry=ttu&amp;g_ep=EgoyMDI2MDMzMC4wIKXMDSoASAFQAw%3D%3D" target="_blank" rel="noreferrer noopener">Abrir ubicación exacta en Google Maps</a></p>
                </section>
            </div>
        </section>
    </div>

<?php
    require_once './../app/views/layout/footer.php';
?>
