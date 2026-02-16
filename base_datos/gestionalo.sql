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

-- Base de datos: `gestionalo` (esquema revisado)
CREATE DATABASE IF NOT EXISTS `gestionalo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gestionalo`;

-- Tablas lookup (valores en español)
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
  ('ingresos'), ('gastos'), ('balance'), ('objetivos'), ('general')
  ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);
INSERT INTO `estados_objetivo` (`nombre`) VALUES
  ('en curso'), ('completado'), ('cancelado')
  ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);
INSERT INTO `tipos_movimiento` (`nombre`) VALUES
  ('gasto'), ('ingreso')
  ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

-- Categorías y subcategorías (globales)
CREATE TABLE `categorias` (
  `id_categoria` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre_categoria` VARCHAR(100) NOT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `subcategorias` (
  `id_subcategoria` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_categoria` INT UNSIGNED DEFAULT NULL,
  `nombre_subcategoria` VARCHAR(100) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_subcategoria`),
  INDEX (`id_categoria`),
  CONSTRAINT `subcategorias_fk_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias`(`id_categoria`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuarios (sin almacenar la contraseña en la columna directa; se usa tabla `contrasenas`)
CREATE TABLE `usuarios` (
  `id_usuario` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  `apellido1` VARCHAR(50) NOT NULL,
  `apellido2` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(150) NOT NULL,
  `localidad` VARCHAR(100) DEFAULT NULL,
  `fecha_nacimiento` DATE DEFAULT NULL,
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
  `hash` VARCHAR(255) NOT NULL,
  `creada_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actual` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_contrasena`),
  INDEX (`id_usuario`),
  CONSTRAINT `contrasenas_fk_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sesiones para login persistente
CREATE TABLE `sesiones` (
  `id_sesion` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` INT UNSIGNED NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `ip` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `expires_at` DATETIME NOT NULL,
  `creada_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `valida` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_sesion`),
  UNIQUE KEY `uq_sesiones_token` (`token`),
  INDEX (`id_usuario`),
  CONSTRAINT `sesiones_fk_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transacciones (unifica ingresos y gastos)
CREATE TABLE `transacciones` (
  `id_transaccion` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` INT UNSIGNED NOT NULL,
  `id_categoria` INT UNSIGNED DEFAULT NULL,
  `id_subcategoria` INT UNSIGNED DEFAULT NULL,
  `id_tipo` INT UNSIGNED NOT NULL, -- referencia a tipos_movimiento (gasto/ingreso/transferencia)
  `concepto` VARCHAR(255) DEFAULT NULL,
  `fecha_movimiento` DATETIME NOT NULL,
  `id_metodo` INT UNSIGNED DEFAULT NULL,
  `importe` DECIMAL(14,2) NOT NULL,
  `comentario` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_transaccion`),
  INDEX (`id_usuario`),
  INDEX (`id_categoria`),
  INDEX (`id_subcategoria`),
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
  `periodo_inicio` DATE DEFAULT NULL,
  `periodo_fin` DATE DEFAULT NULL,
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
  `notificar_ia` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_objetivo`),
  INDEX (`id_usuario`),
  INDEX (`id_estado`),
  CONSTRAINT `objetivos_fk_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `objetivos_fk_estado` FOREIGN KEY (`id_estado`) REFERENCES `estados_objetivo`(`id_estado`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Preguntas y respuestas (IA)
CREATE TABLE `preguntas` (
  `id_pregunta` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_categoria` INT UNSIGNED DEFAULT NULL,
  `texto_pregunta` VARCHAR(255) NOT NULL,
  `activa` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pregunta`),
  INDEX (`id_categoria`),
  CONSTRAINT `preguntas_fk_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias`(`id_categoria`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `respuestas` (
  `id_respuesta` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` INT UNSIGNED NOT NULL,
  `id_pregunta` INT UNSIGNED NOT NULL,
  `respuesta` TEXT DEFAULT NULL,
  `fecha_respuesta` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_respuesta`),
  INDEX (`id_usuario`),
  INDEX (`id_pregunta`),
  CONSTRAINT `respuestas_fk_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `respuestas_fk_pregunta` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas`(`id_pregunta`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `recomendaciones_ia` (
  `id_recomendacion` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_usuario` INT UNSIGNED NOT NULL,
  `id_objetivo` INT UNSIGNED DEFAULT NULL,
  `id_pregunta` INT UNSIGNED DEFAULT NULL,
  `mensaje` TEXT DEFAULT NULL,
  `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leido` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_recomendacion`),
  INDEX (`id_usuario`),
  INDEX (`id_objetivo`),
  INDEX (`id_pregunta`),
  CONSTRAINT `reco_fk_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `reco_fk_objetivo` FOREIGN KEY (`id_objetivo`) REFERENCES `objetivos_ahorro`(`id_objetivo`) ON DELETE SET NULL,
  CONSTRAINT `reco_fk_pregunta` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas`(`id_pregunta`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices compuestos sugeridos para informes/consultas frecuentes
CREATE INDEX `idx_tx_usuario_fecha` ON `transacciones` (`id_usuario`, `fecha_movimiento`);
CREATE INDEX `idx_tx_usuario_categoria_fecha` ON `transacciones` (`id_usuario`, `id_categoria`, `fecha_movimiento`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
