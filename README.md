# TuDu

TuDu es una aplicación web multi-tenant de gestión de tareas por empresa, construida con **CodeIgniter 4.7**, **CodeIgniter Shield 1.4** (autenticación), **PHP 8.2+**, **MySQLi** y **PHPUnit 10**.

## Modelo de roles

Cada usuario pertenece a un grupo de Shield que define su panel:

- **superadmin** → panel `/admin` (empresas, usuarios, roles).
- **cliente** → panel `/cliente` (empleados de su empresa, tareas y asignaciones).
- **empleado** → panel `/empleado` (sus asignaciones y checklist).

Filtros clave: `group:superadmin`, `group:cliente + empresa-activa`, `group:empleado + empresa-activa`.

## Flujo principal

```
Cliente            →  Tarea  →  Asignación  →  Empleado
                                  ↓
                              Checklist
```

1. Un usuario se registra con verificación por correo; al verificar, se crea su empresa automáticamente y se convierte en **cliente**.
2. El cliente gestiona a sus **empleados** (CRUD limitado a su empresa).
3. El cliente crea **tareas** (nombre, descripción, tiempo estimado, icono).
4. El cliente **asigna** tareas a empleados con fecha, vencimiento, prioridad, notas y un checklist opcional de pasos.
5. El empleado consulta sus **asignaciones**, marca los pasos del checklist y las completa.

## Estructura

- `app/Controllers/Admin|Cliente|Empleado` — controladores por panel.
- `app/Models/` — modelos de acceso a datos (devuelven arrays).
- `app/Services/` — `service('usuario')` (UsuarioService) y `service('rol')` (RolService).
- `app/Filters/EmpresaActiva.php` — filtro que valida que la empresa del usuario esté activa.
- `app/Database/Migrations/` — migraciones (`empresas`, `empleados`, `tareas`, `asignaciones_tareas`, `checklist_tareas`, `registros_pendientes`).
- `app/Views/` — vistas organizadas por panel, con el layout `layouts/app.php` (sidebar dinámico por rol).

## Convenciones

- Variables, métodos y mensajes en español (`empresaId()`, `perfilActual()`, `auth()`).
- Estatus numérico (1 = activo, 0 = inactivo) en empresas/empleados/tareas; string (`pendiente`/`completada`) en asignaciones.
- Soft delete (`estatus = 0`) para empresas, empleados y tareas; hard delete para asignaciones y checklists.
- Transacciones (`db_connect()->transBegin()/transCommit()/transRollback()`) cuando se tocan varias tablas.

## Setup

Copiar `env` a `.env` y configurar `app.baseURL` y las credenciales de la base de datos.

```shell
php spark migrate
php spark db:seed AdminSeeder
```

El servidor web debe apuntar a la carpeta `public/`.

## Requisitos del servidor

PHP 8.2 o superior, con las extensiones `intl`, `mbstring`, `json`, `mysqlnd` (para MySQL) y `libcurl`.
