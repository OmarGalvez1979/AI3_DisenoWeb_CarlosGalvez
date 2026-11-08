# AI3_DisenoWeb_CarlosGalvez

Sistema web dinámico de **registro y consulta de estudiantes** desarrollado en **PHP 8 (modular / MVC ligero)** con **MySQL/MariaDB** y **PDO**, aplicando sentencias preparadas contra *SQL Injection* y saneamiento de salidas contra *XSS*.

> Universidad ECOTEC · Programación de Sistemas Web · Actividad Integradora 3
> Autor: **Carlos Omar Galvez**

---

## Funcionalidades

| Módulo | Archivo | Descripción |
|---|---|---|
| Conexión | `config/conexion.php` | Conexión PDO (Singleton) con `try-catch`; errores registrados en `logs/` y mensaje genérico al usuario. |
| Create | `registro.php` | Formulario HTML5/CSS3 con validación en cliente (JS) y servidor (PHP); inserción con **sentencias preparadas**. |
| Read | `listado.php` | Tabla HTML responsiva con búsqueda; toda salida pasa por `htmlspecialchars()`. |
| Modelo | `models/Estudiante.php` | Validaciones, cédula ecuatoriana (módulo 10), `insertar()`, `listar()`. |
| Seguridad | `includes/seguridad.php` | `e()` (XSS), token CSRF, cabeceras CSP / X-Frame-Options, cookies de sesión seguras. |

## Estructura

```
AI3_DisenoWeb_CarlosGalvez/
├── assets/
│   ├── css/estilos.css          # Estilos responsivos
│   └── js/validacion.js         # Validación del lado del cliente
├── config/
│   ├── conexion.php             # Conexión PDO + manejo de errores
│   └── config.local.example.php # Plantilla de credenciales locales
├── database/
│   └── db_sistema_web.sql       # Script DDL de la base de datos
├── docs/                        # Informe técnico (PDF)
├── includes/                    # header, footer y funciones de seguridad
├── logs/                        # Bitácora de errores (ignorada por Git)
├── models/Estudiante.php        # Modelo de datos
├── index.php                    # Punto de entrada
├── registro.php                 # Create
└── listado.php                  # Read
```

## Requisitos

- [XAMPP](https://www.apachefriends.org/) 8.x (Apache + PHP ≥ 8.0 + MariaDB/MySQL)
- Extensión `pdo_mysql` habilitada (viene activa en XAMPP)
- Git

## Despliegue local (paso a paso)

1. **Clonar** el repositorio dentro de `htdocs` de XAMPP:

   ```bash
   cd C:/xampp/htdocs
   git clone https://github.com/OmarGalvez1979/AI3_DisenoWeb_CarlosGalvez.git
   ```

2. **Iniciar** los servicios *Apache* y *MySQL* desde el XAMPP Control Panel.

3. **Crear la base de datos** importando `database/db_sistema_web.sql`:
   - *phpMyAdmin*: abrir `http://localhost/phpmyadmin` → pestaña **Importar** → seleccionar el archivo → **Continuar**.
   - *Consola*:
     ```bash
     C:/xampp/mysql/bin/mysql.exe -u root --default-character-set=utf8mb4 < database/db_sistema_web.sql
     ```

4. **(Opcional) Credenciales**: si su MySQL no usa `root` sin contraseña, copie
   `config/config.local.example.php` como `config/config.local.php` y edite los valores.
   Este archivo está en `.gitignore`, por lo que las credenciales nunca se suben al repositorio.

5. **Abrir** en el navegador:
   - Registro: <http://localhost/AI3_DisenoWeb_CarlosGalvez/registro.php>
   - Consulta: <http://localhost/AI3_DisenoWeb_CarlosGalvez/listado.php>

## Medidas de seguridad

- **SQL Injection** → `PDO::prepare()` + `bindValue()` / `execute()` con parámetros nombrados y `PDO::ATTR_EMULATE_PREPARES = false` (preparación real en el servidor).
- **XSS** → toda salida dinámica se imprime con `e()` = `htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8')`, más cabecera *Content-Security-Policy*.
- **CSRF** → token aleatorio (`random_bytes`) por sesión validado con `hash_equals()`.
- **Exposición de información** → `try-catch` en la conexión y en cada operación; el detalle técnico va a `logs/errores.log`, el usuario solo ve un mensaje genérico.
- **Integridad** → validación en cliente y servidor, claves `UNIQUE` para cédula y correo, `CHECK` en semestre, patrón *Post/Redirect/Get*.

## Convención de commits

Se usa [Conventional Commits](https://www.conventionalcommits.org/es/): `feat:`, `docs:`, `fix:`…

## Autor

**Carlos Omar Galvez** — Universidad ECOTEC
