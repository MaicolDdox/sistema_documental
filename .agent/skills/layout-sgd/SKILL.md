---
name: layout-sgd
description: >
  Estándares de diseño y desarrollo frontend para el Sistema de Gestión
  Documental (SGD) del SENA Colombia. Usar SIEMPRE este skill cuando se
  vayan a crear nuevas vistas, módulos, componentes o páginas dentro de
  la plataforma post-login. Contiene el sistema de diseño completo,
  convenciones de layout, paleta de colores, tipografía, componentes
  reutilizables y patrones de UX del sistema.
---

# SGD — Sistema de Diseño del Layout

## Stack técnico
- Laravel 12 + Blade (sin Livewire en vistas de módulos)
- TailwindCSS via CDN
- Alpine.js via CDN (solo para interactividad ligera: dropdowns, toggles)
- Componentes Blade nativos con $slot
- Sin Flux, sin componentes Livewire en el layout

## Layout principal
Componente: `resources/views/components/app-layout.blade.php`
Uso en vistas:
```html
<x-app-layout>
    <x-slot name="header">Nombre del Módulo</x-slot>
    <!-- contenido aquí -->
</x-app-layout>
```

## Paleta de colores

| Token          | Hex       | Uso                              |
|----------------|-----------|----------------------------------|
| Verde SGD      | #39A900   | Acento, botones primarios, activo |
| Azul oscuro    | #0a1628   | Logo, avatar, elementos primarios |
| Blanco         | #ffffff   | Fondos de tarjetas y sidebar     |
| Gris suave     | #f8fafc   | Fondo del body (slate-50)        |
| Gris borde     | #e2e8f0   | Bordes de tarjetas (slate-200)   |
| Gris texto     | #64748b   | Texto secundario (slate-500)     |
| Rojo alerta    | #ef4444   | Errores y acciones destructivas  |

## Tipografía
- Fuente base: Inter (400, 500, 600, 700)
- Fuente títulos: Outfit (700, 900)
- Tamaños:
  * Título de página: `text-xl font-semibold text-slate-900`
  * Subtítulo: `text-sm text-slate-500`
  * Label de campo: `text-sm font-medium text-slate-700`
  * Texto de tarjeta: `text-sm text-slate-600`
  * Etiqueta sección sidebar: `text-xs font-semibold text-slate-400 uppercase`

## Componentes de sidebar

### Item de navegación (inactivo)
```html
<a href="#" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg
                   text-sm font-medium text-slate-600 transition-all">
    <svg class="w-5 h-5 flex-shrink-0">...</svg>
    Nombre del módulo
</a>
```

### Item de navegación (activo)
Agregar clase: `nav-item-active`
(fondo #f0fdf4, texto #39A900, borde izquierdo #39A900)

### Separador de sección en sidebar
```html
<p class="text-xs font-semibold text-slate-400 uppercase
          tracking-widest px-3 mb-2 mt-4">
    Nombre Sección
</p>
```

## Tarjetas de contenido

### Tarjeta estándar
```html
<div class="bg-white rounded-xl border border-slate-200 p-5">
    <!-- contenido -->
</div>
```

### Tarjeta con encabezado
```html
<div class="bg-white rounded-xl border border-slate-200">
    <div class="px-5 py-4 border-b border-slate-100">
        <h3 class="text-sm font-semibold text-slate-900">Título</h3>
        <p class="text-xs text-slate-400 mt-0.5">Descripción opcional</p>
    </div>
    <div class="p-5">
        <!-- contenido -->
    </div>
</div>
```

## Botones

### Primario (acción principal)
```html
<button class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold
               py-2.5 px-4 rounded-lg text-sm transition-all">
    Guardar
</button>
```

### Secundario
```html
<button class="border border-slate-200 bg-white hover:bg-slate-50
               text-slate-700 font-semibold py-2.5 px-4 rounded-lg
               text-sm transition-all">
    Cancelar
</button>
```

### Destructivo
```html
<button class="bg-red-500 hover:bg-red-600 text-white font-semibold
               py-2.5 px-4 rounded-lg text-sm transition-all">
    Eliminar
</button>
```

## Formularios

### Campo de texto estándar
```html
<div>
    <label class="block text-sm font-medium text-slate-700 mb-1.5">
        Etiqueta
    </label>
    <input type="text"
           class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5
                  text-sm text-slate-800 focus:border-[#39A900]
                  focus:ring-2 focus:ring-[#39A900]/10 transition-all">
</div>
```

### Select estándar
```html
<select class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5
               text-sm text-slate-800 bg-white focus:border-[#39A900]
               focus:ring-2 focus:ring-[#39A900]/10 transition-all">
    <option value="">Selecciona...</option>
</select>
```

## Tablas de datos
```html
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="text-left px-4 py-3 text-xs font-semibold
                           text-slate-500 uppercase tracking-wide">
                    Columna
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="border-b border-slate-100 hover:bg-slate-50
                       transition-colors">
                <td class="px-4 py-3 text-slate-700">Valor</td>
            </tr>
        </tbody>
    </table>
</div>
```

## Badges / Estados
```html
<!-- Activo -->
<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full
             text-xs font-medium bg-green-100 text-green-700">
    <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
    Activo
</span>

<!-- Inactivo -->
<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full
             text-xs font-medium bg-slate-100 text-slate-600">
    <div class="w-1.5 h-1.5 rounded-full bg-slate-400"></div>
    Inactivo
</span>

<!-- Pendiente -->
<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full
             text-xs font-medium bg-amber-100 text-amber-700">
    <div class="w-1.5 h-1.5 rounded-full bg-amber-500"></div>
    Pendiente
</span>
```

## Iconos
- Usar SOLO Heroicons stroke (outline), stroke-width="1.5"
- Tamaño estándar en sidebar: w-5 h-5
- Tamaño en botones: w-4 h-4
- Color: heredado del texto padre (text-current)
- PROHIBIDO: iconos multicolor, iconos filled en navegación principal

## Idioma
- TODO el texto visible al usuario debe estar en ESPAÑOL
- Variables de backend, nombres de rutas y clases: en inglés
- Mensajes de error, labels, placeholders, títulos: en español

## Convención de rutas y vistas
- Cada módulo vive en: `resources/views/{modulo}/`
- Vista principal del módulo: `resources/views/{modulo}/index.blade.php`
- Vista detalle: `resources/views/{modulo}/show.blade.php`
- Vista crear/editar: `resources/views/{modulo}/form.blade.php`
- Todas usan `<x-app-layout>` como wrapper

## Estructura de archivos por módulo
```
resources/views/
├── components/
│   └── app-layout.blade.php    ← Layout maestro
├── dashboard/
│   └── home.blade.php          ← Dashboard principal
├── {modulo}/
│   ├── index.blade.php
│   ├── show.blade.php
│   └── form.blade.php
└── livewire/settings/
    ├── profile.blade.php
    └── password.blade.php
```
