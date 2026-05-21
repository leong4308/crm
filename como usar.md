# Manual de Usuario - Aether CRM

Sistema está diseñado para ayudarte a gestionar tus ventas, clientes y cotizaciones de forma rápida e intuitiva.

## 1. Acceso al Sistema

Para iniciar el CRM, asegúrate de haber ejecutado en tu terminal:

```bash
php artisan serve
```

Luego, ingresa desde tu navegador web a la dirección proporcionada (usualmente `http://127.0.0.1:8000/admin`).

**Tus credenciales de acceso:**

- **Email:** tu-email@ejemplo.com (o tu correo registrado)
- **Contraseña:** tu_contraseña_segura

---

## 2. Pantalla Principal (Tablero)

Al ingresar, verás un resumen completo de tus ventas. Aquí podrás monitorear rápidamente cuántas oportunidades tienes abiertas, el valor total de las ventas ganadas y las actividades pendientes que tienes programadas para el día 
(llamadas, reuniones, correos).

---

## 3. Clientes Potenciales (Leads / Tablero)

La sección más importante es **Clientes Potenciales**.
Aquí es donde controlas el flujo de tus ventas mediante un tablero "Kanban" de arrastrar y soltar.

- **Crear un Nuevo Prospecto:** Haz clic en el botón superior derecho "Crear". Ingresa el nombre del prospecto, valor esperado de la venta, su correo o teléfono y asigna una etapa.
- **Mover Prospectos:** A medida que avanzas en una negociación, simplemente haz clic sobre la tarjeta de un prospecto y arrástrala a la siguiente columna (ej. de "Nuevo" a "Negociación" o "Ganado").
- **Eliminar Prospectos:** Si te equivocas o un prospecto es descartado (spam), puedes usar el **ícono de basurero** ubicado dentro de su tarjeta.

---

## 4. Cotizaciones

Cuando un cliente potencial requiere conocer precios formales:

1. Ve a la sección **Cotizaciones**.
2. Haz clic en **Crear Cotización**.
3. Añade los productos o servicios que le estás ofreciendo (puedes registrarlos previamente en la sección de **Productos**).
4. El sistema calculará automáticamente subtotales, impuestos y descuentos. Luego, puedes exportar esto a PDF y enviárselo a tu cliente.

---

## 5. Contactos y Organizaciones

Aether separa a las personas físicas de las empresas para mantener todo ordenado:

- **Personas:** Aquí guardas nombres, teléfonos, y correos electrónicos de tus contactos.
- **Organizaciones:** Si le vendes a empresas (B2B), puedes crear una organización (ej. "Coca-Cola") y vincular múltiples "Personas" que trabajen dentro de ella.

---

## 6. Automatización de WhatsApp

Tu CRM cuenta con un sistema vinculado de WhatsApp en segundo plano. Esto permite que el sistema envíe mensajes cuando sea necesario y mantenga una comunicación ágil con tus clientes utilizando tu propio número. (Recuerda mantener el comando `php artisan serve` activo para que este servicio siga corriendo en paralelo).

### 6.1 Campaña Masiva de Promoción de Hotel
Si deseas enviar una campaña masiva personalizada sobre el **Sistema de Gestión de Hoteles** a tus contactos, puedes hacerlo ejecutando el siguiente comando en tu terminal:

```bash
php artisan app:send-hotel-promo
```

**Características del envío:**
- **Mensaje Personalizado:** Utiliza el primer nombre del contacto para un trato más cercano y profesional.
- **Formateador Inteligente:** Ajusta automáticamente los números móviles de México añadiendo el prefijo `521` exigido por WhatsApp.
- **Envío Seguro:** Tiene una pausa de 3 segundos entre envíos para evitar bloqueos por parte de WhatsApp o límites de tasa de la API.
- **Resistente a Errores:** Si un número es fijo o inválido, la API de OpenWA registrará el fallo, pero la campaña continuará enviando a los demás contactos de forma ininterrumpida.

---

## 7. Configuración General

Si necesitas ajustar etiquetas, flujos de trabajo, etapas de venta (columnas del tablero), o agregar usuarios para tu equipo de ventas, ve al menú **Configuraciones** (el ícono de engranaje) en el panel izquierdo.

