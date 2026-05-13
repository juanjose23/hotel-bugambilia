# Hotel Bugambilias - Sistema de Gestión

Este proyecto es un sistema de administración integral para el **Hotel Bugambilias**, ubicado en la ciudad de **Estelí, Nicaragua**.

## 📍 Ubicación y Referencias
El **Hotel Bugambilias** es un referente de hospitalidad en la ciudad de **Estelí, Nicaragua**. 
- **Servicios**: Restaurante, bar, piscina, hot tub y traslados al aeropuerto.
- **Entorno**: Ubicado estratégicamente para viajeros de negocios y turistas en la región norte.
- **Referencia**: [Información del Hotel](https://www.bugambiliashotel.com/)

## 📖 Documentación del Proyecto
Para mantener la consistencia y calidad, este proyecto sigue estándares estrictos definidos en la carpeta `docs/`:

- **[Módulo de Compras (P2P)](docs/MODULO_COMPRAS.md)**: Flujo de Solicitudes, Cotizaciones, Órdenes y Recepciones.
- **[Reportes y Notificaciones](docs/REPORTES_Y_NOTIFICACIONES.md)**: Correcciones aplicadas y sistema de notificaciones del módulo de Compras.
- **[Catálogo de Reportes del Sistema](app/Docs/REPORTES.md)**: Todos los reportes con código, concepto, filtros y motor de generación.
- **[Seguridad y Permisos](docs/INICIALIZACION_SEGURIDAD.md)**: Matriz de roles y control de acceso (Filament Shield).
- **[Guía de Usuarios](docs/MODULO_USUARIOS.md)**: Creación, edición y asignación de roles y credenciales.
- **[Arquitectura de Casos de Uso](docs/use-case-architecture.md)**: Clean Architecture simplificada.
- **[Manual de Estilo](docs/README.md)**: Metodología de diseño y patrones recomendados.

## 🚀 Tecnologías
Este sistema está construido con el stack moderno de Laravel:
- **PHP 8.4+**
- **Laravel 13**
- **Filament PHP v5** (Panel de Administración)
- **DomPDF** (Generación de PDF para Catálogos — serie HTB-CP)
- **Spatie PDF / Browsershot** (Generación de PDF para Compras — serie HTB-COM)
- **Maatwebsite Excel** (Exportación de Datos)
- **Tailwind CSS** (Estilizado)
- **MySQL / PostgreSQL** (Base de Datos)

## 📊 Sistema de Reportes
El sistema cuenta con dos suites de reportes estandarizados:

| Serie | Ámbito | Motor | Documentación |
|-------|--------|-------|---------------|
| **HTB-CP** | Catálogos y Productos | DomPDF | [`app/Docs/REPORTES.md`](app/Docs/REPORTES.md) |
| **HTB-COM** | Compras (P2P) | Spatie PDF | [`app/Docs/REPORTES.md`](app/Docs/REPORTES.md) |

- **PDF**: Reportes con diseño corporativo, encabezados persistentes y paginación calculada.
- **Excel**: Exportaciones nativas mediante `maatwebsite/excel`.
- **Auditoría**: Registro completo de interacciones en español (`auditoria_reportes`).

## 🏗️ Arquitectura y Flujo
El proyecto sigue un flujo basado en Casos de Uso (Use Case Driven Development):
`Controller` → `UseCase / Service` → `Model`

Esto garantiza que la lógica de negocio esté aislada y sea fácil de testear.

## 🛠️ Instalación y Configuración

Sigue estos pasos para configurar el entorno de desarrollo:

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/juanjose23/hotel-bugambilia.git
   cd hotel-bugambilia
   ```

2. **Instalar dependencias de PHP:**
   ```bash
   composer install
   ```

3. **Instalar dependencias de Node:**
   ```bash
   npm install
   ```

4. **Configurar el entorno:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Ejecutar migraciones y seeders:**
   ```bash
   php artisan migrate --seed
   ```

6. **Compilar activos:**
   ```bash
   npm run dev
   ```

## 🛡️ Estándares de Código y Desarrollo

Mantenemos la calidad del código mediante las siguientes herramientas automatizadas (configuradas con Husky):

- **Laravel Pint**: Para el estilo de código PHP.
- **PHPStan**: Para el análisis estático y prevención de errores.
- **Prettier**: Para el formato de archivos JS/CSS.

### Comandos Útiles
- Ejecutar análisis estático: `composer run phpstan`
- Corregir estilo de código: `composer run pint`
- Corregir formato JS: `npm run format:js`

## 👥 Contribución
Para contribuir al proyecto, asegúrate de seguir los estándares de **Conventional Commits**. El sistema utiliza `commitlint` para validar los mensajes de commit.

---
© 2026 Hotel Bugambilias - Estelí, Nicaragua.
