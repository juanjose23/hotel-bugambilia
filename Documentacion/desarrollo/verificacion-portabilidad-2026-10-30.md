# Verificación de portabilidad — 30 de octubre de 2026

## Resultados observados

| Comprobación | Resultado |
| --- | --- |
| Suite PHP completa en Linux/Docker | 749 aprobadas, 1 fallo y 14 omitidas; 3.894 comprobaciones, 1.321,19 s |
| Repetición tras corregir el único fallo, incluyendo una nueva regresión | 3 aprobadas, 36 comprobaciones, 348,14 s |
| Dusk con Chromium/Selenium y PostgreSQL aislado | 16 aprobadas, 34 comprobaciones, 504,17 s |
| PHPStan nivel 9 | Sin errores |
| Pre-commit nativo de Windows | Formato, ESLint, TypeScript y Pint aprobados |
| Pre-commit mediante el modo Docker de Husky | Aprobado; Pint comprobó 2.494 archivos |
| Imágenes locales | `hotel-bugambilia:local` y `hotel-bugambilia:tools` construidas |
| Aplicación con la imagen final | HTTP 200 en `/`, `/admin/login` y `/up`; migraciones al día |

El fallo completo era una violación de `uq_stock_variante`: Espacios podía crear existencias de una variante sin `producto_id`, mientras el cargador de bebidas buscaba incluyendo ese campo e intentaba insertar un duplicado. Se corrigió la búsqueda para usar espacio, variante y lote; el producto se actualiza como dato del registro. La nueva prueba reproduce esa combinación y ejecuta la carga dos veces. También pasaron los dos escenarios completos de siembra de espacios. La suite completa no se ejecutó una segunda vez; la repetición se limitó a los escenarios afectados.

Las imágenes finales se compararon con el código local mediante SHA-256 para el lanzador, el cargador de bebidas, su nueva regresión, DuskTestCase y las pruebas de navegador de restaurante: los cinco archivos coincidieron. La configuración personal `.hotel-checks.json` quedó excluida de la imagen y de Git.

## Cobertura que sigue pendiente

- Trece pruebas de documentos de compras, inventario y activos se omiten cuando faltan registros de ejemplo. Necesitan crear sus propios datos sintéticos; estas omisiones no se cuentan como aprobaciones.
- Una prueba de carga se omite si no está disponible su servidor web. Su umbral de rendimiento no quedó validado en esta ejecución.
- Los flujos de CI quedaron configurados; GitHub Actions no se ejecutó desde esta sesión.
- La compilación terminó correctamente y mantiene advertencias sobre paquetes de JavaScript mayores de 500 kB.

## Entornos

La aplicación Docker utiliza su propio PostgreSQL y abre en http://127.0.0.1:8080. Las pruebas usan otro servicio PostgreSQL, con bases separadas para PHP y Dusk. No se migró, importó ni limpió la base real del hotel.

Las imágenes se construyeron en este equipo; no se publicaron en un registro. Los pasos para reproducir el entorno y elegir ejecución nativa o Docker están en `entorno-portable.md`.

Configuración vigente tras el ajuste solicitado: `pre-commit` y `pre-push` se ejecutan siempre de forma nativa. La comprobación Docker de la tabla corresponde a la configuración anterior; los hooks actuales no construyen ni ejecutan imágenes.
