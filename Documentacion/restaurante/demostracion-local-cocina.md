# Cocina: demostración local y guía del encargado

Ampliación: [asistente de recetas y menú por platillos](asistente-recetas-menu-buffet.md).

La interfaz está disponible en http://127.0.0.1:8029/admin/restaurante/control-cocina.

Acceso exclusivo de ejemplo: `cocina@demo.local`, contraseña `CocinaDemo2026!`. Esta cuenta tiene permisos de cocina; no es superadministrador.

## Recorrido diario

1. **Control de cocina:** revisar comandas pendientes, preparaciones sin confirmar, buffets de hoy, reposiciones y vencimientos.
2. **Preparar o porcionar:** elegir bodega, insumo, presentación y cantidad. Registrar cada resultado con su unidad y destino. Revisar y guardar. La confirmación retira insumos y registra resultados, costo y pérdidas dentro de una transacción.
3. **Ejemplo de carne:** retirar 1 kg de carne sin limpiar; obtener cuatro porciones de 200 g. El sistema registra cuatro unidades y 200 g de pérdida de rendimiento. No es necesario introducir costos manuales.
4. **Bebidas:** abrir una botella de 750 ml y obtener 750 ml de bebida abierta; o abrir una caja de 24 y registrar 24 unidades. El peso, volumen y contenido del empaque deben estar configurados en cada presentación.
5. **Elaborar receta:** trabajar primero en borrador; confirmar únicamente cuando se conozca el resultado real. Un borrador no consume inventario.
6. **Buffets y eventos:** crear servicio por persona, paquete o modalidad mixta. Enviar o reponer alimentos desde cocina/bar. Al finalizar, introducir asistentes reales, sobrantes y desperdicios medidos (escribir 0 si no hubo). Lo servido se calcula como enviado menos sobrante menos merma. Los sobrantes quedan separados para registrar su disposición.
7. **Cobro mixto:** agregar a una cuenta abierta de la misma moneda indicando paquetes y personas adicionales. No cobrar otra vez las personas cubiertas por el paquete.

## Aislamiento

- Base independiente: `hotel_cocina_demo`, PostgreSQL `127.0.0.1:55448`.
- Servidor HTTP: `127.0.0.1:8029`, accesible solo desde este equipo.
- Configuración, clave de aplicación, cookies, sesiones y vistas compiladas independientes en `.local/interfaz-cocina`.
- Correo en memoria, cola local, sin cobros externos de demostración.
- La configuración se carga desde `.env.cocina-demo`; no se lee la configuración del entorno real.
- No se modificó `.env` ni se ejecutaron migraciones contra la base real.
- Las cuatro migraciones de cocina/buffet se aplicaron sobre la base ficticia.
- El navegador de comprobación creó una preparación de muestra; el buffet permanece abierto para practicar.

## Encender y apagar

Desde PowerShell:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File 'D:\Developer\laravel\hotel-bugambilia\.local\interfaz-cocina\iniciar.ps1'
powershell -NoProfile -ExecutionPolicy Bypass -File 'D:\Developer\laravel\hotel-bugambilia\.local\interfaz-cocina\detener.ps1'
```

Los datos se conservan al apagar. Los archivos locales están ignorados por Git. El sembrado `sembrar.php` comprueba nombre de base, host, puerto y modo demo antes de escribir, y no reinicia datos si la cuenta de ejemplo ya existe. PostgreSQL es compartido con las pruebas sintéticas y no se detiene automáticamente con el servidor web.

## Comprobaciones

Se verificaron el asistente y el cierre en navegador, la conversión de kilos a porciones, la adaptación a teléfono de 390 px, los permisos, el filtro por bodega y las mediciones obligatorias del buffet. Los resultados detallados de las pruebas y del análisis están en `.local/interfaz-cocina`.
