<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="docs/dark-logo.svg" height="80">
    <source media="(prefers-color-scheme: light)" srcset="docs/logo.svg" height="80">
    <img src="docs/logo.svg" alt="Aether CRM Logo" height="80">
  </picture>
</p>


<h1 align="center">Aether CRM</h1>

<p align="center">
  Sistema de gestión de relaciones con clientes (CRM) construido con <strong>Laravel</strong> y <strong>Vue.js</strong>,
  con integración nativa de <strong>WhatsApp</strong> vía OpenWA.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10.x-red?style=for-the-badge&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/Vue.js-3.x-42b883?style=for-the-badge&logo=vue.js" alt="Vue.js">
  <img src="https://img.shields.io/badge/WhatsApp-OpenWA-25D366?style=for-the-badge&logo=whatsapp" alt="WhatsApp">
</p>

---

##  Características principales

-  **Dashboard** con métricas de ventas en tiempo real
-  **Tablero Kanban** para gestión visual del pipeline de ventas
-  **Integración WhatsApp** — envío de mensajes y campañas masivas desde el CRM
-  **Cotizaciones en PDF** con cálculo automático de impuestos y descuentos
-  **Contactos y Organizaciones** — gestión B2B y B2C
-  **Catálogo de Productos / Servicios**
-  **Etapas y flujos de venta** completamente personalizables
-  **Multi-usuario** con roles y permisos

---

##  Requisitos del servidor

| Tecnología | Versión mínima |
|---|---|
| PHP | 8.3 o superior |
| Composer | 2.5 o superior |
| MySQL | 8.0.32 o superior |
| Node.js | 18.x o superior |
| Servidor web | Apache 2 o NGINX |
| RAM | 3 GB o más |

---

##  Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/aether-crm.git
cd aether-crm
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Configurar el entorno

```bash
cp .env.example .env
php artisan key:generate
```

Edita el archivo `.env` y configura:

```env
APP_URL=http://localhost
DB_HOST=127.0.0.1
DB_DATABASE=aether_crm
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña

MAIL_MAILER=smtp
MAIL_HOST=smtp.tuproveedor.com
MAIL_PORT=587
MAIL_USERNAME=tu@correo.com
MAIL_PASSWORD=tu_contraseña_email
```

### 4. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

### 5. Instalar dependencias frontend

```bash
npm install && npm run build
```

### 6. Levantar el servidor

```bash
php artisan serve
```

Accede desde tu navegador a: **http://127.0.0.1:8000/admin**

---

##  Credenciales de acceso por defecto

>  **Importante:** Cambia estas credenciales inmediatamente después de tu primer ingreso.

| Campo | Valor |
|---|---|
| Email | `admin@example.com` |
| Contraseña | `admin123` |

---

## Manual de usuario

###  1. Pantalla Principal (Tablero)

Al ingresar verás un resumen de tus ventas: oportunidades abiertas, valor total ganado y actividades pendientes del día (llamadas, reuniones, correos).

---

###  2. Clientes Potenciales — Tablero Kanban

La sección más importante del CRM. Controla tu pipeline de ventas visualmente.

- **Crear un prospecto:** Botón superior derecho **"Crear"** → ingresa nombre, valor esperado, contacto y etapa.
- **Mover prospectos:** Arrastra las tarjetas entre columnas (Nuevo → Contactado → Negociación → Ganado).
- **Eliminar prospectos:** Icono de basurero dentro de cada tarjeta.

---

###  3. Cotizaciones

1. Ve a la sección **Cotizaciones**
2. Haz clic en **Crear Cotización**
3. Agrega los productos/servicios (regístralos previamente en **Productos**)
4. El sistema calcula subtotales, impuestos y descuentos automáticamente
5. Exporta a **PDF** y envíaselo a tu cliente

---

###  4. Contactos y Organizaciones

- **Personas:** Nombres, teléfonos y correos de tus contactos individuales.
- **Organizaciones:** Para ventas B2B — crea una empresa y vincula múltiples personas dentro de ella.

---

###  5. Integración WhatsApp (OpenWA)

El CRM tiene un sistema WhatsApp integrado que corre en segundo plano. Permite enviar mensajes automáticos y campañas masivas usando tu propio número.

#### Campaña masiva de promoción

```bash
php artisan app:send-hotel-promo
```

**Características del envío:**
-  Mensaje personalizado con el nombre del contacto
-  Formato automático de números mexicanos (`521` prefijo)
-  Pausa de 3 segundos entre envíos (evita bloqueos de WhatsApp)
-  Resistente a errores — continúa aunque un número sea inválido

---

###  6. Configuración General

Ve al menú **Configuraciones** (ícono de engranaje en el panel izquierdo) para:
- Personalizar etapas del pipeline
- Agregar usuarios al equipo de ventas
- Configurar etiquetas y flujos de trabajo

---

##  Uso en producción

Antes de pasar a producción, elimina las dependencias de desarrollo:

```bash
composer install 
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📁 Estructura del proyecto

```
aether-crm/
├── app/                  # Lógica de la aplicación (Laravel)
├── config/               # Configuraciones
├── database/             # Migraciones y seeders
├── OpenWA/               # Gateway de WhatsApp
│   └── index.js          # Servidor Node.js de WhatsApp
├── packages/Webkul/      # Módulos del CRM
├── resources/            # Vistas, assets, JS/Vue
├── routes/               # Rutas web y API
├── docs/                 # Imágenes y documentación
└── .env.example          # Plantilla de configuración
```


