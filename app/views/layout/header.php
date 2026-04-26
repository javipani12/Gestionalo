<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $titulo ?></title>
  <link rel="stylesheet" href="./css/style.css">
  <link rel="icon" type="image/png" sizes="32x32" href="./img/favicon-32x32.png?v=2">
  <link rel="icon" type="image/png" sizes="16x16" href="./img/favicon-16x16.png?v=2">
  <link rel="shortcut icon" href="./img/favicon.ico?v=2">
  <link rel="apple-touch-icon" href="./img/apple-touch-icon.png?v=2">
  <meta name="theme-color" content="#0b5cff">
  <script src="./js/page-transition.js" defer></script>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <ul class="navbar__menu">
                <li><a href="?controller=dashboard&action=mostrarDashboard" class="navbar__link">Inicio</a></li>
                <li class="navbar__item navbar__item--dropdown">
                    <a href="?controller=transaction&action=mostrarTransaccionesUsuario" class="navbar__link navbar__link--dropdown" aria-haspopup="true">
                        Transacciones
                    </a>
                    <ul class="navbar__dropdown" aria-label="Submenú de transacciones">
                        <li>
                            <a href="?controller=transaction&action=mostrarTransaccionesUsuario" class="navbar__dropdown-link">
                                Ver transacciones
                            </a>
                        </li>
                        <li>
                            <a href="?controller=transaction&action=mostrarFormularioCrearTransaccion" class="navbar__dropdown-link">
                                Nueva transacción
                            </a>
                        </li>
                    </ul>
                </li>
                <li><a href="?controller=tool&action=mostrarHerramientas" class="navbar__link">Herramientas</a></li>
                <li><a href="?controller=about&action=mostrarSobreNosotros" class="navbar__link">Sobre nosotros</a></li>
                <li class="navbar__item navbar__item--dropdown">
                    <a href="?controller=contact&action=mostrarMisConsultas" class="navbar__link navbar__link--dropdown" aria-haspopup="true">
                        Contacto
                    </a>
                    <ul class="navbar__dropdown" aria-label="Submenú de transacciones">
                        <li>
                            <a href="?controller=contact&action=mostrarMisConsultas" class="navbar__dropdown-link">
                                Mis Consultas
                            </a>
                        </li>
                        <li>
                            <a href="?controller=contact&action=mostrarCrearConsulta" class="navbar__dropdown-link">
                                Nueva Consulta
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="navbar__item navbar__item--dropdown">
                    <a href="?controller=profile&action=mostrarPerfil" class="navbar__link navbar__link--dropdown" aria-haspopup="true">
                        Perfil
                    </a>
                    <ul class="navbar__dropdown" aria-label="Submenú de transacciones">
                        <li>
                            <a href="?controller=profile&action=mostrarPerfil" class="navbar__dropdown-link">
                                Mi Perfil
                            </a>
                        </li>
                        <li>
                            <a href="?controller=legal&action=descargarManualUsuario" class="navbar__dropdown-link">
                                Descargar Manual de Usuario
                            </a>
                        </li>
                        <li>
                            <a href="?controller=auth&action=cerrarSesion" class="navbar__dropdown-link">
                                Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>

    <?php if(isset($_SESSION['correcto'])): ?>
        <div class="alert success"><?= htmlspecialchars($_SESSION['correcto']) ?></div>
    <?php unset($_SESSION['correcto']); endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); endif; ?>

    <?php if (!empty($consultasHoy) && isset($limiteDiarioConsultas) && $consultasHoy >= $limiteDiarioConsultas): ?>
        <div class="alert error">Has alcanzado el límite diario de <?= (int)$limiteDiarioConsultas ?> consultas. Podrás enviar más mañana.</div>
    <?php endif; ?>