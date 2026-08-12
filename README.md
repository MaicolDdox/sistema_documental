# GIDESTH — Sistema Documental de Semilleros de Investigación

Sistema web para la gestión documental y el seguimiento de semilleros de investigación: registro de proyectos, control de participantes por rol, generación de reportes y flujo de aprobación de productos de investigación.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-4.0-4E56A6?logo=livewire&logoColor=white)
![Tailwind](https://img.shields.io/badge/Tailwind_CSS-4.0-06B6D4?logo=tailwindcss&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-blue)

## Índice

- [Descripción](#descripción)
- [Stack tecnológico](#stack-tecnológico)
- [Arquitectura](#arquitectura)
- [Instalación](#instalación)
- [Comandos disponibles](#comandos-disponibles)
- [Despliegue](#despliegue)
- [Colaboradores](#colaboradores)
- [Licencia](#licencia)

## Descripción

GIDESTH centraliza la administración de semilleros de investigación de una institución educativa: gestión de usuarios por rol, registro de proyectos y productos de investigación, control de acceso multi-sede (por centro de formación) y generación de reportes en PDF/Excel.

El sistema está organizado por rol de usuario, replicando la misma estructura en controladores, componentes Livewire y vistas para cada uno de los siguientes roles:

| Rol | Responsabilidad principal |
|---|---|
| `SuperAdmin` | Administración global del sistema |
| `Admin` | Gestión de usuarios, roles y configuración por centro de formación |
| `DirectorSemilleros` | Supervisión de semilleros de investigación |
| `LiderSemillero` | Gestión operativa de un semillero |
| `AsesorSemillero` | Acompañamiento y validación de proyectos |
| `DirectorInvestigacion` | Aprobación de productos de investigación |
| `InvestigadorAsociado` | Participación en proyectos y productos |

## Stack tecnológico

- **Backend:** Laravel 12 (PHP 8.2)
- **Frontend reactivo:** Livewire 4.0 + Flux UI 2.9
- **Estilos:** Tailwind CSS 4.0
- **Autenticación:** Laravel Fortify + Spatie Permission 6.24
- **Base de datos:** MySQL
- **Testing:** PHPUnit 11.5 (SQLite en memoria)
- **Reportes:** DomPDF (PDF) + PhpSpreadsheet (Excel)
- **Bundler:** Vite 7

## Arquitectura

El proyecto sigue una organización modular por rol:

```
app/Http/Controllers/{Rol}/
app/Livewire/{Rol}/
resources/views/{rol}/
```

El acceso a los datos es multi-tenant por centro de formación (`training_center_id`), controlado de forma centralizada en `app/Support/TrainingCenterAccess.php`. Toda consulta que liste usuarios o datos sensibles pasa por esta clase para evitar fugas de información entre centros.

## Instalación

```bash
git clone https://github.com/MaicolDdox/sistema_documental.git
cd sistema_documental
composer setup      # instala dependencias, copia .env, genera key, migra y compila assets
```

O paso a paso:

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configura DB_* en .env antes de migrar
php artisan migrate
npm install
npm run build
```

## Comandos disponibles

```bash
composer dev             # Servidor + queue + Vite en paralelo
composer test            # PHPUnit + linting con Pint
composer lint            # Solo Laravel Pint
php artisan migrate      # Ejecutar migraciones
npm run dev              # Vite con hot reload
npm run build            # Build de producción
```

## Despliegue

El despliegue a producción (Hostinger) es automático mediante GitHub Actions al hacer push a `main`: instala dependencias, ejecuta migraciones y recompila cachés de configuración, rutas y vistas. Los workflows de tests y linting corren en cada push/PR contra `main`.

## Colaboradores

| Colaborador | Aporte principal | Impacto en el sistema |
|---|---|---|
| **Maicol Duvan Gasca Rodas** ([@MaicolDdox](https://github.com/MaicolDdox)) | Arquitectura base, modelos de datos, controladores y componentes Livewire del core | Diseñó la estructura fundacional del sistema: capa de modelos, layouts, dashboard, módulo Admin y la página de bienvenida pública |
| **Maria Vargas** ([@MaripVargas](https://github.com/MaripVargas)) | Módulo Asesor de Semillero | Desarrolló el flujo completo de acompañamiento y validación de proyectos por parte del rol Asesor |
| **Saira Castañeda** ([@saira1504](https://github.com/saira1504)) | Módulos Admin, Director de Semilleros, Líder de Semillero y SuperAdmin | Construyó gran parte de la capa HTTP y de vistas de gestión operativa, además de soporte y políticas de autorización |
| **Marlon Pérez** | Módulos Director de Investigación e Investigador Asociado | Implementó el flujo de aprobación de productos de investigación y los servicios asociados a investigadores |
| **Juan Aldana** ([@juan14857](https://github.com/juan14857)) | Refactorización general, módulo SuperAdmin, sistema de correo y ajustes de configuración | Consolidó modelos y servicios transversales, sistema de notificaciones por correo, y corrección de bugs previos al cierre de versión |

*Estadísticas de aporte por líneas de código y commits disponibles en el historial de Git del repositorio.*

## Licencia

Este proyecto se distribuye bajo licencia MIT.
