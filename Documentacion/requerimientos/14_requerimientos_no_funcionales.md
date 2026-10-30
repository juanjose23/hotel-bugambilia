# Requerimientos No Funcionales (RNF) del Sistema

### 3. Requerimientos del Sistema

#### 3.2. Requerimientos No Funcionales (RNF)

##### Desempeño y Escalabilidad
* **RNF-01:** El sistema debe responder a las solicitudes de usuarios con un tiempo de respuesta uniforme inferior a 1.5 segundos en el 95% de las transacciones web y API en periodos de alta concurrencia (hasta 150 usuarios simultáneos en recepción, restaurante y portal público).
* **RNF-02:** Los reportes masivos, exportaciones contables y cálculos de inventario deben procesarse exclusivamente mediante trabajos asíncronos en segundo plano (Background Jobs) a fin de prevenir la degradación del rendimiento o bloqueo del hilo principal de ejecución HTTP.
* **RNF-03:** El sistema implementará almacenamiento en caché para datos de solo lectura frecuentes (catálogos, configuraciones de monedas y tipos de habitación) con invalidación automática al registrarse modificaciones en el panel administrativo.

##### Operatividad y Seguridad
* **RNF-04:** El acceso al sistema estará restringido mediante credenciales individuales asociadas a roles y perfiles asignados (Spatie Permission / Filament Shield), aplicando de manera estricta el principio de menor privilegio.
* **RNF-05:** Las contraseñas se almacenarán cifradas utilizando algoritmos robustos (Bcrypt o Argon2id con factor de coste adecuado). Se forzará la expiración y renovación de sesiones inactivas tras 30 minutos sin interacción.
* **RNF-06:** Toda comunicación entre el cliente (navegador/dispositivo) y el servidor se realizará a través de protocolos seguros HTTPS con certificados TLS 1.3 vigentes y cabeceras de seguridad estrictas (HSTS, CSP, X-Frame-Options).
* **RNF-07:** La integración con pasarelas de pago externas (Stripe) cumplirá con el estándar PCI-DSS mediante el uso de tokens efímeros y formularios alojados, garantizando que el sistema nunca almacene números de tarjeta ni códigos CVV en sus bases de datos.

##### Validación e Integridad de Datos
* **RNF-08:** El sistema validará la información de entrada en los formularios mediante reglas de obligatoriedad, longitud máxima y tipo de datos tanto en el frontend (esquemas Zod en React 19) como en el backend (Form Requests en Laravel 13), garantizando protección activa contra inyecciones SQL, ataques XSS y manipulación de parámetros.
* **RNF-09:** Toda operación que afecte múltiples tablas de saldo (habitaciones, reservas, inventario, kardex, facturación y caja) debe ejecutarse obligatoriamente bajo transacciones de base de datos (`DB::transaction`) con aislamiento ACID y bloqueos pesimistas o claves de idempotencia para garantizar la integridad referencial y prevenir condiciones de carrera.

##### Disponibilidad, Mantenibilidad y Respaldos
* **RNF-10:** El sistema garantizará una tasa de disponibilidad del 99.5% durante las 24 horas del día, los 365 días del año (24/7/365), soportando la operación continua del hotel y restaurante.
* **RNF-11:** Se ejecutarán copias de seguridad automáticas diarias de la base de datos PostgreSQL y del almacenamiento de documentos digitales, manteniendo retención mínima de 30 días y garantizando un tiempo objetivo de recuperación (RTO) inferior a 1 hora y un punto objetivo de recuperación (RPO) inferior a 24 horas.
* **RNF-12:** El código fuente del sistema mantendrá estricto cumplimiento del nivel 9 de análisis estático en PHPStan, tipado estricto (`declare(strict_types=1);`), arquitectura por capas (Filament/Controller → Interactor → BusinessLogic → Repository → Model) y pruebas automatizadas con Pest y Dusk.
