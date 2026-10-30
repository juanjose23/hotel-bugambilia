# Tesorería activada en el proyecto principal

Fecha: 29/10/2026.

## Aplicación principal

Proyecto: `D:\Developer\laravel\hotel-bugambilia`.

Acceso: https://hotel-bugambilia.test/admin/tesoreria/fondos

Gestión de cajas: https://hotel-bugambilia.test/admin/tesoreria/cajas

Usa tu cuenta habitual del hotel. Estas direcciones corresponden al proyecto principal; `127.0.0.1:8029` continúa siendo la demostración independiente.

## Cambios aplicados

- Migración de tesorería aplicada en la base principal local `hotel_bugambilias`, PostgreSQL en `127.0.0.1:5432`.
- Activación mediante `TESORERIA_HABILITADO=true` en la configuración principal, conservando las demás variables.
- Permisos integrados con los roles existentes del hotel. Se mantienen los permisos que ya tenían; no se reemplazaron las asignaciones anteriores.
- Preparación de roles incluida en el código del proyecto para futuras instalaciones mediante `TesoreriaRolesSeeder`.
- No se ejecutó el seeder general de datos ni se copiaron usuarios, claves, cajas, bancos, comprobantes o saldos de demostración.

## Roles preparados

| Perfil | Funciones nuevas |
|---|---|
| Recepcionista, encargado de recepción, encargado de restaurante y cajero | Apertura y operación de turno, cobros, cierre, entrega de efectivo y solicitudes de fondo o devolución |
| Supervisor de recepción y supervisor de caja | Revisión de diferencias, devoluciones y entrega de fondos, además de operaciones de caja |
| Administración, gerencia, contabilidad y tesorería | Administración de fondos y bancos, caja chica, comprobantes, conciliación y control financiero |
| Responsable de caja chica | Solicitudes de vales de su fondo |
| Auditor financiero | Consulta y exportación, sin autorización para operar dinero |

Los superadministradores existentes conservan acceso administrativo. No se crearon usuarios ni se cambiaron contraseñas o indicadores de acceso al panel.

## Primer uso con datos del hotel

1. El administrador crea las cajas reales desde **Gestión de cajas** y define sus monedas.
2. En **Fondos y bancos**, registra cuentas y fondos con sus responsables, respaldando los saldos iniciales con documentos reales.
3. Asigna los perfiles necesarios a las personas que realizarán cada función. Un rol financiero no sustituye la habilitación de acceso al panel administrativo del usuario.
4. El cajero abre un turno con el efectivo que realmente tiene. Si espera recibir el fondo, abre en cero y solicita su entrega desde **Fondos para turnos**.
5. Las autorizaciones que requieren otro responsable siguen separadas: nadie aprueba su propia solicitud por tener más de un rol.

Las tablas nuevas quedaron vacías. No se atribuyeron fondos o movimientos a una persona para simular una puesta en marcha.

## Respaldo y conservación

Antes de activar se generó un respaldo de aproximadamente 38 MB, se verificó su contenido y se restauró en una copia aislada. La migración y los permisos se ensayaron primero sobre esa copia.

Se compararon cantidad de registros y huellas del contenido completo de **18 tablas históricas**, antes y después. Se preservaron usuarios, reservas, cuentas, pagos, ventas, facturas, folios, autorizaciones fiscales y registros previos de caja.

La integración creó **12 tablas nuevas vacías** y dejó **cero migraciones pendientes**. En un proceso nuevo se comprobó que la configuración principal está activa y que las **11 pantallas** de caja y tesorería se renderizan usando los datos de la base principal. Esta comprobación fue de lectura y no generó operaciones monetarias.

Los archivos del respaldo y las evidencias técnicas están en `.local/activacion-real/` dentro del proyecto. La copia del archivo de entorno y el respaldo contienen información del proyecto; esa carpeta está excluida del control de versiones.

## Alcance

Se activó la aplicación principal **local**, que mantiene `APP_ENV=local`. No se desplegó un servidor remoto ni se verificó una integración fiscal o bancaria externa. La configuración de bancos, cajas, responsables y saldos corresponde a los datos reales del hotel.

La guía de operación detallada continúa disponible en `Documentacion/auditoria/tesoreria-bancos-caja-chica-comprobantes.md`. Sus accesos de demostración no son credenciales de esta aplicación principal.

## Validación final

La regresión de caja, tesorería, cuentas y facturación se ejecutó en la base separada de pruebas: **91 pruebas aprobadas y 431 verificaciones**. El análisis estático del código de integración no encontró errores. No se ejecutó la batería de pruebas destructiva sobre la base principal.
