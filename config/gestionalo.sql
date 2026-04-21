-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-02-2026 a las 11:51:00
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Base de datos: `gestionalo`
CREATE DATABASE IF NOT EXISTS `gestionalo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gestionalo`;

-- Tablas lookup
CREATE TABLE IF NOT EXISTS `roles` (
  `id_rol` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `uq_roles_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `metodos_pago` (
  `id_metodo` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_metodo`),
  UNIQUE KEY `uq_metodos_pago_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tipos_informe` (
  `id_tipo_informe` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_tipo_informe`),
  UNIQUE KEY `uq_tipos_informe_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `estados_objetivo` (
  `id_estado` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_estado`),
  UNIQUE KEY `uq_estados_objetivo_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `estados_consulta` (
  `id_estado` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_estado`),
  UNIQUE KEY `uq_estados_consulta_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `asuntos` (
  `id_asunto` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_asunto`),
  UNIQUE KEY `uq_asuntos_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tipos_movimiento` (
  `id_tipo` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_tipo`),
  UNIQUE KEY `uq_tipos_movimiento_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Valores iniciales para lookup
INSERT INTO `roles` (`nombre`) VALUES ('usuario'), ('admin')
  ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);
INSERT INTO `metodos_pago` (`nombre`) VALUES
  ('efectivo'), ('tarjeta'), ('transferencia'), ('bizum'), ('paypal'), ('otro')
  ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);
INSERT INTO `tipos_informe` (`nombre`) VALUES
  ('ingresos'), ('gastos'), ('balance'), ('objetivos'), ('general'), ('hipoteca')
  ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);
INSERT INTO `estados_objetivo` (`nombre`) VALUES
  ('en curso'), ('completado'), ('cancelado')
  ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);
INSERT INTO `estados_consulta` (`nombre`) VALUES
  ('Enviada'), ('En Curso'), ('Finalizada')
  ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);
INSERT INTO `asuntos` (`nombre`) VALUES
  ('Cambio de correo electrónico'),
  ('Cambio de contraseña'),
  ('Problema técnico'),
  ('Duda sobre transacciones'),
  ('Sugerencia de mejora'),
  ('Otra consulta')
  ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);
INSERT INTO `tipos_movimiento` (`nombre`) VALUES
  ('gasto'),
  ('ingreso'),
  ('Transferencia Interna Aporte'),
  ('Transferencia Interna Retiro')
  ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

-- Categorías y subcategorías (globales)
CREATE TABLE `categorias` (
  `id_categoria` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre_categoria` VARCHAR(100) NOT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `uq_categorias_nombre` (`nombre_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `subcategorias` (
  `id_subcategoria` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_categoria` INT UNSIGNED DEFAULT NULL,
  `nombre_subcategoria` VARCHAR(100) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_subcategoria`),
  UNIQUE KEY `uq_subcategorias_categoria_nombre` (`id_categoria`, `nombre_subcategoria`),
  INDEX (`id_categoria`),
  CONSTRAINT `subcategorias_fk_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias`(`id_categoria`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categorías y subcategorías iniciales para transacciones
INSERT INTO `categorias` (`nombre_categoria`, `descripcion`) VALUES
  ('Alimentación', 'Gastos de comida y consumo alimentario diario'),
  ('Salud', 'Gastos médicos, farmacia y bienestar personal'),
  ('Capricho', 'Compras no esenciales y consumo personal'),
  ('Transporte', 'Movilidad urbana e interurbana'),
  ('Vivienda y suministros', 'Gastos del hogar y servicios básicos'),
  ('Inversiones', 'Aportaciones, costes y rendimientos de inversión'),
  ('Ingresos', 'Entradas de dinero por trabajo, rentas y cobros puntuales'),
  ('Objetivo', 'Movimientos de transferencia interna hacia o desde objetivos de ahorro')
ON DUPLICATE KEY UPDATE
  `descripcion` = VALUES(`descripcion`),
  `updated_at` = CURRENT_TIMESTAMP;

INSERT INTO `subcategorias` (`id_categoria`, `nombre_subcategoria`) VALUES
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Alimentación'), 'Comida'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Alimentación'), 'Compra supermercado'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Alimentación'), 'Tomar algo'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Alimentación'), 'Comer fuera'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Alimentación'), 'Comida mascota'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Alimentación'), 'Panadería y bollería'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Alimentación'), 'Café y desayuno'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Salud'), 'Farmacia'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Salud'), 'Podólogo'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Salud'), 'Veterinario mascota'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Salud'), 'Dentista'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Salud'), 'Psicólogo'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Salud'), 'Fisioterapia'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Salud'), 'Seguro médico'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Ropa'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Cine'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Videojuegos'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Planeta DeAgostini'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Maquillaje'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Multimedia'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Libros'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Wallapop'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'eBay'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Todocolección'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Vinted'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Amazon'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Peluquería'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Otros gastos'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Regalo'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Consolas'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Cosméticos'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Ikea'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Lotería'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Complementos'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Aliexpress'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Shein'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Suscripciones de ocio'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Capricho'), 'Eventos y conciertos'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Transporte'), 'Uber'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Transporte'), 'Transporte público'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Transporte'), 'Repostar coche'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Transporte'), 'Bolt'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Transporte'), 'Taxi'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Transporte'), 'Peaje'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Transporte'), 'Tren'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Transporte'), 'Autobús'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Transporte'), 'Avión'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Transporte'), 'Blablacar'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Transporte'), 'ITV'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Transporte'), 'Aparcamiento'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Transporte'), 'Mantenimiento vehículo'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Transporte'), 'Seguro vehículo'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Transporte'), 'Otros gastos'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Vivienda y suministros'), 'Luz'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Vivienda y suministros'), 'Alquiler'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Vivienda y suministros'), 'Hipoteca'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Vivienda y suministros'), 'Agua'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Vivienda y suministros'), 'Internet'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Vivienda y suministros'), 'Gas'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Vivienda y suministros'), 'Comunidad'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Vivienda y suministros'), 'Seguro hogar'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Vivienda y suministros'), 'Reparaciones hogar'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Vivienda y suministros'), 'Limpieza hogar'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Inversiones'), 'Estudios'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Inversiones'), 'Fondos indexados'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Inversiones'), 'Acciones'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Inversiones'), 'ETF'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Inversiones'), 'Criptomonedas'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Inversiones'), 'Dividendos'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Inversiones'), 'Intereses'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Inversiones'), 'Venta de activos'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Inversiones'), 'Comisiones y custodia'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Ingresos'), 'Nómina'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Ingresos'), 'Pagas extra'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Ingresos'), 'Bonus y comisiones'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Ingresos'), 'Autónomo y facturación'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Ingresos'), 'Intereses bancarios'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Ingresos'), 'Dividendos'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Ingresos'), 'Reembolsos y devoluciones'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Ingresos'), 'Venta de artículos'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Ingresos'), 'Alquileres cobrados'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Ingresos'), 'Premios y lotería'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Ingresos'), 'Regalos recibidos'),
  ((SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Ingresos'), 'Otros ingresos')
ON DUPLICATE KEY UPDATE
  `updated_at` = CURRENT_TIMESTAMP;

-- Usuarios (sin almacenar la contraseña en la columna directa; se usa tabla `contrasenas`)
CREATE TABLE `usuarios` (
  `id_usuario` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  `apellido1` VARCHAR(50) NOT NULL,
  `apellido2` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(150) NOT NULL,
  `localidad` VARCHAR(100) DEFAULT NULL,
  `fecha_nacimiento` DATE DEFAULT NULL,
  `politica_privacidad` TINYINT(1) NOT NULL DEFAULT 0,
  `consentimiento_datos` TINYINT(1) NOT NULL DEFAULT 0,
  `fecha_registro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rol_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `eliminado` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uq_usuarios_email` (`email`),
  INDEX (`rol_id`),
  CONSTRAINT `usuarios_fk_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles`(`id_rol`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de contraseñas (historial y contraseña activa)
CREATE TABLE `contrasenas` (
  `id_contrasena` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` INT UNSIGNED NOT NULL,
  `contrasenna_hash` VARCHAR(255) NOT NULL,
  `creada_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actual` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_contrasena`),
  INDEX (`id_usuario`),
  CONSTRAINT `contrasenas_fk_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Consultas de contacto al admin
CREATE TABLE `consultas` (
  `id_consulta` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` INT UNSIGNED NOT NULL,
  `id_asunto` INT UNSIGNED NOT NULL,
  `comentario` TEXT NOT NULL,
  `respuesta` TEXT DEFAULT NULL,
  `id_estado` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_consulta`),
  INDEX (`id_usuario`),
  INDEX (`id_asunto`),
  INDEX (`id_estado`),
  CONSTRAINT `consultas_fk_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `consultas_fk_asunto` FOREIGN KEY (`id_asunto`) REFERENCES `asuntos`(`id_asunto`) ON DELETE RESTRICT,
  CONSTRAINT `consultas_fk_estado` FOREIGN KEY (`id_estado`) REFERENCES `estados_consulta`(`id_estado`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transacciones (ingresos, gastos y transferencias internas)
CREATE TABLE `transacciones` (
  `id_transaccion` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` INT UNSIGNED NOT NULL,
  `id_categoria` INT UNSIGNED DEFAULT NULL,
  `id_subcategoria` INT UNSIGNED DEFAULT NULL,
  `id_objetivo` INT UNSIGNED DEFAULT NULL,
  `id_tipo` INT UNSIGNED NOT NULL, -- referencia a tipos_movimiento (gasto/ingreso/transferencias internas)
  `concepto` VARCHAR(255) DEFAULT NULL,
  `fecha_movimiento` DATE NOT NULL,
  `id_metodo` INT UNSIGNED DEFAULT NULL,
  `importe` DECIMAL(14,2) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_transaccion`),
  INDEX (`id_usuario`),
  INDEX (`id_categoria`),
  INDEX (`id_subcategoria`),
  INDEX (`id_objetivo`),
  INDEX (`id_tipo`),
  INDEX (`fecha_movimiento`),
  CONSTRAINT `transacciones_fk_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `transacciones_fk_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias`(`id_categoria`) ON DELETE SET NULL,
  CONSTRAINT `transacciones_fk_subcategoria` FOREIGN KEY (`id_subcategoria`) REFERENCES `subcategorias`(`id_subcategoria`) ON DELETE SET NULL,
  CONSTRAINT `transacciones_fk_tipo` FOREIGN KEY (`id_tipo`) REFERENCES `tipos_movimiento`(`id_tipo`) ON DELETE RESTRICT,
  CONSTRAINT `transacciones_fk_metodo` FOREIGN KEY (`id_metodo`) REFERENCES `metodos_pago`(`id_metodo`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Informes
CREATE TABLE `informes` (
  `id_informe` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` INT UNSIGNED NOT NULL,
  `nombre_informe` VARCHAR(150) DEFAULT NULL,
  `id_tipo_informe` INT UNSIGNED DEFAULT NULL,
  `ruta_archivo` VARCHAR(255) NOT NULL,
  `fecha_generacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_informe`),
  INDEX (`id_usuario`),
  CONSTRAINT `informes_fk_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `informes_fk_tipo` FOREIGN KEY (`id_tipo_informe`) REFERENCES `tipos_informe`(`id_tipo_informe`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Objetivos de ahorro
CREATE TABLE `objetivos_ahorro` (
  `id_objetivo` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` INT UNSIGNED NOT NULL,
  `nombre_objetivo` VARCHAR(150) NOT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `cantidad_meta` DECIMAL(14,2) NOT NULL,
  `fecha_inicio` DATE DEFAULT NULL,
  `fecha_limite` DATE DEFAULT NULL,
  `id_estado` INT UNSIGNED NOT NULL DEFAULT 1,
  `cantidad_final` DECIMAL(14,2) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_objetivo`),
  INDEX (`id_usuario`),
  INDEX (`id_estado`),
  CONSTRAINT `objetivos_fk_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `objetivos_fk_estado` FOREIGN KEY (`id_estado`) REFERENCES `estados_objetivo`(`id_estado`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices compuestos sugeridos para informes/consultas frecuentes
CREATE INDEX `idx_tx_usuario_fecha` ON `transacciones` (`id_usuario`, `fecha_movimiento`);
CREATE INDEX `idx_tx_usuario_categoria_fecha` ON `transacciones` (`id_usuario`, `id_categoria`, `fecha_movimiento`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
