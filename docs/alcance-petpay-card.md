# PETPAY-CARD — Alcances funcionales y técnicos

**Proyecto:** PETPAY-CARD  
**Dominio:** petpay-card.com  
**Stack inicial:** Laravel 13, PHP 8.3, MySQL/MariaDB, WAMP local, Hostinger producción  
**Documento:** Alcance base v0.1  
**Objetivo:** Definir los alcances iniciales del sistema PETPAY-CARD con base en el portal comercial petpay.mx y el journey operativo de usuario, POS/proveedor y repartidor.

---

## 1. Referencias base

Este alcance toma como referencia:

1. El portal comercial **petpay.mx**, donde se comunica la búsqueda de productos y servicios para mascotas cerca del usuario, registro/inicio de sesión, compra/vende/entrega desde app, registro de negocio, perfil de repartidor, comparación de precios, PawPoints, PawNews y Petpay Plus.
2. El documento **Petpay - Journey Registro, Usuario, POS y Reparto**, donde se describen los flujos de registro, compra, geolocalización, carrito, cálculo de comisiones, PawPoints, costo de envío, ticket de compra, ticket de confirmación, ticket de venta, ticket de entrega, asignación de repartidor, códigos de recepción/entrega/devolución y producción del repartidor.

---

## 2. Alcance general de PETPAY-CARD

PETPAY-CARD será una plataforma tipo marketplace/delivery para mascotas, con operación por ubicación y zonas.

El sistema debe permitir que un cliente busque productos o servicios para su mascota, compare opciones cercanas, compre, pague, reciba seguimiento y obtenga entrega a domicilio mediante repartidores disponibles.

La plataforma tendrá estos frentes principales:

```txt
1. Portal Admin
2. Portal Cliente / Usuario
3. Portal Proveedor / Vendedor / POS
4. Portal Repartidor
5. API pública para WordPress / página comercial
6. API móvil para app cliente, proveedor y repartidor
```

Aunque inicialmente se puede comunicar como 3 portales, funcionalmente conviene mantener 4 portales separados, porque proveedor y repartidor tienen flujos distintos.

---

## 3. Alcance visual base

El diseño debe seguir la línea visual de petpay.mx:

```txt
- Header blanco limpio
- Logo Petpay en naranja
- Botón principal negro
- Botón secundario naranja
- Fondo naranja intenso
- Patrón de huellas
- Tipografía grande, bold y redondeada
- Diseño amigable, moderno y enfocado en mascotas
- Formularios grandes y fáciles de usar
- Estilo marketplace / delivery
```

La experiencia debe sentirse cercana, amigable y enfocada en mascotas, pero con estructura suficientemente robusta para operación de marketplace, pagos, logística y administración.

---

## 4. Portal Comercial / WordPress

La página comercial actual seguirá siendo la entrada pública de marketing. PETPAY-CARD no reemplaza necesariamente esa página; el sistema nuevo debe conectarse con ella.

### 4.1 Funciones detectadas del portal comercial

```txt
- Registro de usuario
- Inicio de sesión
- Búsqueda por dirección de entrega
- Selección de horario: entregar ahora / horario programado
- Compra de productos para mascotas
- Registro de mascota
- Registro de papá/mamá de la mascota
- Compra con acumulación de huellitas / PawPoints
- Registro de negocio
- Registro de perfil repartidor
- Comparación de precios por tienda, categoría, marca o precio
- Noticias, eventos y promociones PawNews
- Petpay Plus con envíos gratis, beneficios e invitaciones
- Sección de ayuda
- Tarjeta de regalo
- Agregar tienda
- Cuenta empresarial
- Promociones
- Localización de tiendas
- Solicitud de tarjeta Petpay
```

### 4.2 APIs necesarias para WordPress

PETPAY-CARD debe exponer endpoints para que WordPress mande registros, leads y consultas.

```txt
/api/public/health
/api/public/register/client
/api/public/register/provider
/api/public/register/driver
/api/public/location/check
/api/public/search/start
/api/public/newsletter/subscribe
/api/public/petpay-plus/lead
/api/public/gift-card/lead
/api/public/business-account/lead
/api/public/contact/help
/api/public/store-request
/api/public/petpay-card-request
```

### 4.3 Datos que debe mandar WordPress

```txt
Nombre
Apellido
Correo electrónico
Teléfono
Dirección
Tipo de registro: cliente / proveedor / repartidor
Ubicación aproximada
Interés: comprar / vender / repartir / plus / noticias / tarjeta
Origen del lead
UTM campaign/source/medium
Aceptación de términos
```

### 4.4 Seguridad para APIs públicas

```txt
- Token público por integración
- Llave privada/secret para WordPress
- Firma HMAC futura
- Rate limit
- Logs por request
- Validación de origen
- Validación de campos obligatorios
- Respuesta JSON estandarizada
```

---

## 5. Portal Cliente / Usuario

Este portal es para quien compra productos o servicios para su mascota.

### 5.1 Registro y perfil

El journey define que el usuario puede entrar a una página principal sin registro, después solicitar registro, elegir registro normal o con Google/Facebook/Apple, y capturar nombre, apellido, correo, teléfono y dirección.

Alcance:

```txt
- Registro con email/teléfono
- Login
- Recuperar contraseña
- Registro social futuro: Google, Facebook, Apple
- Perfil del usuario
- Direcciones guardadas
- Ubicación principal
- Perfil de mascota
- Múltiples mascotas
- Historial de actividad
- Preferencias de compra
- Métodos de pago
- PawPoints acumulados
- Membresía Petpay Plus
```

### 5.2 Inicio sin registro

Debe permitir navegación inicial sin registro:

```txt
- Ver landing del marketplace
- Ingresar dirección
- Buscar productos/servicios
- Ver tiendas cercanas
- Ver productos destacados
- Ver precios comparativos
```

Para comprar debe pedir cuenta o login.

### 5.3 Búsqueda y geolocalización

El flujo de compra inicia con geolocalización del usuario y geolocalización de POS cercanos en la periferia del usuario.

Alcance:

```txt
- Capturar dirección manual
- Capturar ubicación del navegador/app
- Guardar coordenadas lat/lng
- Buscar proveedores/POS cercanos
- Radio inicial configurable de 3 km
- Radio mínimo de 1 km para zonas densas
- Radio ampliable si no hay resultados
- Mapa de cobertura
- Validación de cobertura antes de comprar
```

### 5.4 Catálogo y marketplace

Alcance:

```txt
- Categorías para mascotas
- Productos
- Servicios
- Tiendas cercanas
- Veterinarias
- Estéticas
- Alimentos
- Accesorios
- Medicamentos permitidos, si aplica legalmente
- Servicios a domicilio
- Productos destacados
- Selección Petpay
- Ofertas
- Promociones
- Comparador de precios
- Comparador por tienda
- Comparador por marca
- Comparador por disponibilidad
- Ratings y reseñas
```

### 5.5 Carrito y compra

El journey incluye generación de carrito, calculadora, decisión de compra y borrado de datos del carrito si el usuario no continúa.

Alcance:

```txt
- Carrito por usuario
- Carrito temporal para invitado
- Validación de stock
- Cálculo de subtotal
- Cálculo de comisiones
- Cálculo de envío
- Cálculo de método de pago
- Cálculo de PawPoints
- Total a cobrar al usuario
- Borrado o expiración del carrito
- Checkout
- Confirmación del pedido
```

### 5.6 Calculadora de cobro

El cálculo debe considerar:

```txt
- Total a pagar al POS
- Comisiones por la venta
- Cálculo de PawPoints
- Cálculo del costo del envío
- Total a pagar al repartidor
- Comisiones por la entrega
- Comisiones por el método de pago
- Total a cobrar al usuario
```

También debe considerar:

```txt
- Distancia
- Valor del pedido
- Volumetría
- Tipo de reparto
```

Alcance del motor de tarifas:

```txt
- Comisión por venta
- Comisión por entrega
- Comisión por método de pago
- Tarifa por distancia
- Tarifa por volumen/peso
- Tarifa por tipo de entrega
- Reglas para Petpay Plus
- Reglas de PawPoints
- Impuestos si aplica
- Redondeos
- Descuentos
- Cupones
```

### 5.7 Pago

Alcance:

```txt
- Cargo bancario
- Tarjeta
- Wallet futura
- Pago fallido
- Solicitud de nuevo método de pago
- Webhook de pago
- Conciliación
- Reembolso
- Cancelación
- Comprobante
```

### 5.8 Seguimiento de pedido

Alcance:

```txt
- Estado del pedido
- Pedido recibido
- POS confirmando
- Pedido confirmado
- En búsqueda de repartidor
- Repartidor asignado
- Repartidor en ruta a recolección
- Pedido recolectado
- En camino al cliente
- Entregado
- Cancelado
- Devuelto
```

---

## 6. Portal Proveedor / Vendedor / POS

Este portal es para tiendas, veterinarias, estéticas, negocios y proveedores que venden dentro de PETPAY-CARD.

### 6.1 Alta de proveedor

Alcance:

```txt
- Registro de negocio
- Datos fiscales
- Datos comerciales
- Dirección del local
- Coordenadas
- Horarios
- Categorías del negocio
- Contacto operativo
- Contacto financiero
- Validación por admin
- Estado: pendiente / aprobado / rechazado / suspendido
- Documentos
- Cuenta bancaria para pagos
```

### 6.2 Panel POS / venta

El journey POS Venta muestra apertura de aplicación, recepción de ticket de compra, contabilidad de productos solicitados, envío de ticket de confirmación, productos faltantes o sustitución, ticket de venta y rastreo del repartidor.

Alcance:

```txt
- Dashboard de ventas
- Recepción de tickets de compra
- Detalle de pedido
- Validar productos solicitados
- Confirmar disponibilidad total
- Confirmar disponibilidad parcial
- Proponer sustitutos
- Rechazar pedido
- Generar ticket de confirmación
- Generar ticket de venta
- Ver repartidor asignado
- Comunicación con repartidor
- Rastreo del avance del repartidor
```

### 6.3 Catálogo del proveedor

```txt
- Alta de productos
- Alta de servicios
- Categorías propias
- Marcas
- SKU interno
- Código de barras
- Fotos
- Descripción
- Precio
- Precio promocional
- Stock
- Disponibilidad
- Tiempo de preparación
- Volumen/peso
- Restricciones
- Carga masiva
- Inventario
```

### 6.4 Comisiones del proveedor

```txt
- Comisión por venta
- Comisión por categoría
- Comisión por proveedor
- Comisión especial por promoción
- Liquidaciones
- Saldo pendiente
- Reporte de ventas
- Facturación/comprobantes
```

---

## 7. Portal Repartidor

Este portal es para quienes generan entregas.

### 7.1 Alta de repartidor

```txt
- Registro de repartidor
- Datos personales
- Teléfono
- Correo
- Identificación
- Vehículo
- Placas, si aplica
- Método de movilidad: bici / moto / auto / caminando
- Zona de operación
- Cuenta bancaria
- Validación por admin
- Estado: pendiente / aprobado / suspendido
```

### 7.2 Disponibilidad

El journey muestra modo ON/OFF y botón TAKE.

Alcance:

```txt
- Modo disponible ON/OFF
- Última ubicación
- Estado activo/inactivo
- Aceptar entrega
- Rechazar entrega
- Botón TAKE
- Tiempo límite para aceptar
```

### 7.3 Ticket de entrega

El journey muestra recepción de ticket de entrega con monto, distancia, tiempo de recolección, tiempo de entrega y distancia total.

Alcance:

```txt
- Recepción de ticket de entrega
- Monto estimado a ganar
- Dirección de proveedor
- Dirección de cliente
- Distancia a recolección
- Distancia a entrega
- Tiempo estimado
- Tipo de pedido
- Volumen/peso
```

### 7.4 Flujo de reparto

```txt
- Inicio ruta de recolección
- Código de recepción
- Confirmación de recolección
- Inicio ruta de entrega
- Código de entrega
- Confirmación de entrega
- Evidencia de entrega
- Firma/foto opcional
- Devolución del pedido
- Código de devolución
- Ruta de devolución
- Cierre de entrega
```

### 7.5 Ingresos del repartidor

La pantalla de producción debe mostrar entregas realizadas, ingreso obtenido, ingreso semanal, ingreso mensual y detalle de producción.

Alcance:

```txt
- Ingreso por entrega
- Entregas realizadas
- Ingreso diario
- Ingreso semanal
- Ingreso mensual
- Detalle de producción
- Historial de entregas
- Liquidaciones
- Comisiones aplicadas
```

---

## 8. Portal Admin

El admin controla toda la operación.

### 8.1 Administración general

```txt
- Dashboard general
- Usuarios
- Clientes
- Proveedores
- Repartidores
- Roles y permisos
- Configuración de sistema
- Catálogos globales
- Categorías
- Marcas
- Zonas
- Cobertura
- Radios de búsqueda
```

### 8.2 Operación

```txt
- Pedidos
- Tickets de compra
- Tickets de confirmación
- Tickets de venta
- Tickets de entrega
- Estados operativos
- Asignación de repartidor
- Reasignación manual
- Incidencias
- Cancelaciones
- Devoluciones
- Reembolsos
```

### 8.3 Marketplace

```txt
- Productos globales
- Productos por proveedor
- Servicios
- Tiendas
- Veterinarias
- Estéticas
- Promociones
- Selección Petpay
- Productos destacados
- Revisión de publicaciones
```

### 8.4 Tarifas y comisiones

```txt
- Comisión por venta
- Comisión por proveedor
- Comisión por categoría
- Comisión por entrega
- Comisión por método de pago
- Reglas de distancia
- Reglas por volumen
- Reglas por tipo de reparto
- Reglas Petpay Plus
- Reglas PawPoints
```

### 8.5 Finanzas

```txt
- Pagos de clientes
- Cargos bancarios
- Liquidaciones a proveedores
- Liquidaciones a repartidores
- Comisiones retenidas
- Reportes financieros
- Conciliación
- Facturas/comprobantes
```

### 8.6 Marketing y fidelización

```txt
- PawPoints
- PawNews
- Petpay Plus
- Cupones
- Promociones
- Eventos
- Días de puntos dobles
- Tarjetas de regalo
- Campañas
```

---

## 9. PawPoints

PawPoints premia la fidelidad con puntos/huellitas, las compras acumulan huellitas en establecimientos afiliados y cada huellita puede usarse como dinero en nuevas compras.

Alcance:

```txt
- Acumulación de puntos por compra
- Conversión puntos/dinero
- Reglas por proveedor
- Reglas por categoría
- Días de puntos dobles
- Saldo de PawPoints
- Historial de movimientos
- Redención en checkout
- Expiración de puntos
- Cancelación/reverso de puntos
```

---

## 10. Petpay Plus

Petpay Plus contempla envíos gratis, beneficios exclusivos e invitaciones a eventos especiales.

Alcance:

```txt
- Registro a Petpay Plus
- Membresía mensual/anual
- Envíos gratis bajo reglas
- Beneficios exclusivos
- Invitaciones a eventos
- Promociones especiales
- Identificación de usuario Plus
- Reglas de cobertura Plus
- Cancelación de membresía
- Renovación
```

---

## 11. PawNews

PawNews contempla noticias, eventos, ofertas, días de PawPoints dobles y actividades.

Alcance:

```txt
- Suscripción a newsletter
- Gestión de campañas
- Noticias
- Eventos
- Ofertas
- Días de PawPoints dobles
- Segmentación por usuario
- Segmentación por zona
- Segmentación por mascota
```

---

## 12. Geolocalización y zonas

El journey contempla radio de búsqueda de 3 km, cálculo de distancia y radio de 1 km.

Alcance:

```txt
- Coordenadas de usuarios
- Coordenadas de proveedores
- Coordenadas de repartidores
- Radio configurable
- Radio por ciudad/zona
- Radio por tipo de entrega
- Distancia usuario-proveedor
- Distancia repartidor-proveedor
- Distancia proveedor-cliente
- Tiempo estimado
- Cobertura activa/inactiva
- Zonas bloqueadas
```

Recomendación inicial:

```txt
Radio base: 3 km
Radio compacto: 1 km
Radio ampliado: 5 km bajo configuración
```

---

## 13. Tickets y estados

El proceso operativo debe manejar doble aceptación de ticket: ticket de compra, ticket de confirmación y ticket de venta. Después se publica ticket de entrega y se asigna repartidor.

### 13.1 Tickets necesarios

```txt
Ticket de compra
Ticket de confirmación
Ticket de venta
Ticket de entrega
Ticket de recepción
Ticket de devolución
```

### 13.2 Estados de pedido

```txt
carrito
pendiente_confirmacion_pos
confirmado_pos
producto_sustitucion_pendiente
pendiente_pago
pagado
buscando_repartidor
repartidor_asignado
en_recoleccion
recolectado
en_entrega
entregado
devolucion_solicitada
en_devolucion
devuelto
cancelado
fallido
```

---

## 14. Notificaciones

El journey contempla seguimiento push a compras pendientes por app, mail y WhatsApp.

Alcance:

```txt
- Notificaciones app
- Email
- WhatsApp
- SMS futuro
- Notificación a cliente
- Notificación a proveedor
- Notificación a repartidor
- Notificación a admin
- Recordatorios de carrito
- Recordatorios de pago fallido
- Seguimiento de pedido
- Alertas de entrega
```

---

## 15. App móvil

El sistema debe nacer preparado para app móvil.

Alcance:

```txt
- API móvil cliente
- API móvil proveedor
- API móvil repartidor
- Autenticación por token
- Registro móvil
- Login móvil
- Ubicación en tiempo real
- Push notifications
- Perfil repartidor ON/OFF
- Publicación en Play Store
- Preparación futura para App Store
```

Recomendación técnica:

```txt
Backend Laravel API
App móvil Flutter o React Native
Publicación inicial Android Play Store
```

---

## 16. Alcance técnico base

```txt
Backend: Laravel
Lenguaje: PHP 8.3
Base: MySQL/MariaDB
Local: WAMP
Editor: Visual Studio / VS Code
Hosting: Hostinger
Dominio: petpay-card.com
API: REST
Frontend web: Blade inicial, preparado para crecer
App móvil: conectada por API
Integración WordPress: por endpoints públicos seguros
```

---

## 17. Bases de datos propuestas

PETPAY-CARD puede crecer con bases separadas por dominio funcional.

```txt
petpay_card_core          -> usuarios, roles, permisos, configuración general
petpay_card_marketplace   -> productos, servicios, categorías, tiendas, inventario
petpay_card_orders        -> carritos, pedidos, tickets, estados, comisiones
petpay_card_delivery      -> repartidores, rutas, asignaciones, códigos, tracking
petpay_card_payments      -> pagos, métodos de pago, cargos, conciliación, comisiones
petpay_card_api           -> tokens, integraciones WordPress, logs de API, webhooks
petpay_card_rewards       -> PawPoints, Petpay Plus, cupones, beneficios
```

En la fase inicial ya quedó activa la base principal:

```txt
petpay_card_core
```

---

## 18. Alcance por fases

### Fase 1 — Base técnica y estructura

```txt
Proyecto Laravel
MySQL
Rutas separadas
Controladores separados
Layout visual base Petpay
API health
Estructura admin/cliente/proveedor/repartidor
```

### Fase 2 — Usuarios, roles y auth

```txt
Registro
Login
Roles
Permisos
Cliente
Proveedor
Repartidor
Admin
Perfil base
```

### Fase 3 — Integración WordPress

```txt
API registro cliente
API registro proveedor
API registro repartidor
API ubicación
API newsletter
API Petpay Plus
API solicitud tarjeta
Tokens de integración
Logs
```

### Fase 4 — Marketplace

```txt
Categorías
Productos
Servicios
Proveedores
Inventario
Búsqueda
Comparador de precios
Disponibilidad por zona
```

### Fase 5 — Compra y carrito

```txt
Carrito
Checkout
Calculadora
Comisiones
PawPoints
Costo de envío
Método de pago
Pedido
Tickets
```

### Fase 6 — POS / proveedor

```txt
Recepción de ticket
Confirmación
Sustituciones
Ticket de venta
Rastreo de repartidor
Ventas
Liquidaciones
```

### Fase 7 — Repartidor

```txt
ON/OFF
Ticket de entrega
TAKE
Rutas
Códigos
Entrega
Devolución
Ingresos
Producción diaria/semanal/mensual
```

### Fase 8 — Admin operativo

```txt
Operación de pedidos
Asignación de repartidor
Comisiones
Finanzas
Incidencias
Reportes
PawPoints
Petpay Plus
PawNews
```

### Fase 9 — App móvil

```txt
App cliente
App proveedor
App repartidor
Notificaciones push
Geolocalización
Play Store
```

---

## 19. MVP recomendado

Para no hacer todo de golpe, el primer MVP debe ser:

```txt
1. Registro/login con roles
2. Portal admin
3. Portal cliente
4. Portal proveedor
5. Portal repartidor
6. Registro desde WordPress por API
7. Alta de proveedor
8. Alta de productos
9. Búsqueda por ubicación
10. Carrito
11. Pedido
12. Confirmación de proveedor
13. Cálculo básico de comisión
14. Cálculo básico de envío
15. Asignación simple de repartidor
16. Estados de pedido
17. API health y logs
```

Después entran pagos reales, PawPoints, Petpay Plus, sustituciones avanzadas, tracking, WhatsApp, app móvil y Play Store.

---

## 20. Rutas base ya creadas en local

```txt
/                    -> landing general
/admin               -> portal admin
/cliente             -> portal cliente
/proveedor           -> portal proveedor / vendedor / POS
/repartidor          -> portal repartidor
/api/public/health   -> API pública de prueba
```

Los archivos de rutas se separaron correctamente:

```txt
routes/web.php
routes/admin.php
routes/cliente.php
routes/proveedor.php
routes/repartidor.php
routes/api.php
```

Los controladores base quedaron separados:

```txt
app/Http/Controllers/Admin
app/Http/Controllers/Cliente
app/Http/Controllers/Proveedor
app/Http/Controllers/Repartidor
app/Http/Controllers/Api/Public
```

---

## 21. Próximo paso recomendado

Después de guardar este alcance dentro del proyecto, el siguiente paso técnico será crear el layout visual base:

```txt
resources/views/layouts/app.blade.php
resources/views/partials/topbar.blade.php
resources/views/partials/portal-card.blade.php
```

Y actualizar:

```txt
resources/views/portals/admin/home.blade.php
resources/views/portals/cliente/home.blade.php
resources/views/portals/proveedor/home.blade.php
resources/views/portals/repartidor/home.blade.php
```

El diseño deberá seguir la referencia visual de petpay.mx: naranja, patrón de huellas, botones negros/naranjas, header blanco y estética amigable para mascotas.

