# Gestionalo

Una aplicación web moderna de gestión de transacciones y finanzas personales. Permite a los usuarios registrar, organizar y seguimiento de sus movimientos económicos de forma intuitiva y segura.

## 📋 Características

- **Autenticación segura**: Sistema de registro e inicio de sesión con contraseñas hasheadas
- **Gestión de transacciones**: Crear, editar y eliminar movimientos económicos
- **Categorización**: Organiza transacciones por categorías y subcategorías
- **Clasificación de movimientos**: Diferencia entre ingresos y gastos
- **Métodos de pago**: Registra el método usado en cada transacción
- **Perfil de usuario**: Gestiona tu información personal y contraseña
- **Dashboard**: Visualización personalizada de tu actividad
- **Diseño responsive**: Funciona en desktop, tablet y móvil

## 🛠️ Requisitos previos

- **PHP 7.4+** (o superior)
- **MySQL 5.7+** (o MariaDB equivalente)
- **Composer** (para gestión de autoload)
- **XAMPP, LAMP, o servidor web local** con soporte PHP

## 💾 Instalación

### 1. Clonar o descargar el repositorio

```bash
git clone https://github.com/tuusuario/Gestionalo.git
cd Gestionalo
```

### 2. Configurar la base de datos

1. Abre phpMyAdmin (o tu cliente MySQL)
2. Crea una base de datos llamada `gestionalo`
3. Importa el archivo SQL desde `config/gestionalo.sql`:
   ```sql
   mysql -u root gestionalo < config/gestionalo.sql
   ```

### 3. Configurar la conexión a BD (opcional)

Por defecto, la aplicación espera:
- **Host**: `localhost`
- **Usuario**: `root`
- **Contraseña**: (vacía)
- **Base de datos**: `gestionalo`

Si tu configuración es diferente, edita `config/Database.php`:

```php
private $host = "localhost";
private $db_name = "gestionalo";
private $user = "root";
private $pass = "";
```

### 4. Regenerar autoload de Composer

```bash
composer dump-autoload -o
```

### 5. Configurar el servidor web

- Coloca la carpeta en `htdocs` (XAMPP) o tu raíz web
- Accede a `http://localhost/Gestionalo/public/` desde tu navegador

## 🚀 Uso

### Inicio rápido

1. **Registrarse**: Crea una nueva cuenta desde la página de inicio
2. **Iniciar sesión**: Accede con tus credenciales
3. **Dashboard**: Ve tus transacciones recientes y resumen
4. **Crear transacción**: Registra un nuevo movimiento económico
5. **Uso de herramientas**: Podrás utilizar la calculadora de hipoteca, establecer objetivos y visualizar gráficamente tus transacciones, de manera que puedas tener un mayor control financiero
6. **Perfil**: Actualiza tu información personal y contraseña

## 📚 Tecnologías utilizadas

- **Backend**: PHP (MVC)
- **Base de datos**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Autoload**: Composer
- **Gestor de dependencias**: Composer

## 📝 Licencia

Este proyecto está bajo la licencia especificada en el archivo [LICENSE](LICENSE).

## 👤 Autor

Desarrollado por Javier Fernández Paniagua, como Proyecto de Fin de Grado para el Ciclo Formativo de Grado Superior en Desarrollo de Aplicaciones Web.

## 📞 Soporte

Para reportar problemas o sugerencias, por favor abre un issue en el repositorio.

---

**Última actualización**: Mayo 2026