# Plantilla Estándar de Requerimientos del Sistema

### 3. Requerimientos del Sistema

#### 3.1. Requerimientos Funcionales (RF)

##### Requerimientos de [Nombre del Módulo / Proceso]
* **RF-01:** El software [verbo en futuro/presente] [acción/proceso], mostrando en pantalla [datos/resultados].
* **RF-02:** El S.I. permitirá [función], solicitando [campos obligatorios] y verificando [criterio de validación].
* **RF-03:** Se asignará [código/ID] con el cual se [control/regla de negocio].
* *Excepción/Regla:* Se podrán modificar los datos a excepción de [campos inmutables].

##### Requerimientos de [Proceso de Check-out / Facturación / Contabilidad]
* **RF-04:** [Nombre del proceso]: 1) [Paso principal de verificación]
  * i) [Condición A]: El sistema ejecutará [Acción A].
  * ii) [Condición B]: El sistema aplicará [Acción B/Multa/Reversión].

---

#### 3.2. Requerimientos No Funcionales (RNF)

##### Desempeño
* **RNF-01:** El sistema debe responder a las solicitudes de usuarios con un tiempo de respuesta uniforme en periodos de alta concurrencia.

##### Operatividad y Seguridad
* **RNF-02:** El acceso al sistema estará restringido mediante credenciales individuales asociadas a roles y perfiles asignados.

##### Validación e Integridad
* **RNF-03:** El sistema validará la información de entrada en los formularios mediante reglas de obligatoriedad, longitud máxima y tipo de datos.
