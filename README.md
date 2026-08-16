# Prueba técnica Emisión - Desarrollador de Software Mid
### Julian Andres Montoya Carvajal

## Instrucciones etapa de desarrollo

1) Preferiblemente trabajar en Ubuntu 22.04 LTS (puede utilizar WSL2 en
   Windows para instalar Ubuntu)
2) Tener instalado Docker y Docker Compose para la orquestación de los
   servicios: https://www.digitalocean.com/community/tutorials/how-to-install-and-use-docker-on-ubuntu-22-04
3) Clone el repositorio con el siguiente comando:
   ```bash
   git clone https://github.com/andresprogramacion123/Prueba-tecnica-emision.git
   ```
4) Ingrese a la carpeta del proyecto:
   ```bash
   cd Prueba-tecnica-emision
   ```
5) Cree el archivo de variables de entorno `.env` en la raíz del proyecto a
   partir del `.env.example` incluido:
   ```bash
   cp .env.example .env
   ```
   El `.env.example` trae `DB_CONNECTION=sqlite` por defecto y las variables
   de MySQL comentadas. Antes de levantar Docker, edite el `.env` y
   ajuste/agregue las siguientes variables para que coincidan con lo que
   espera `docker-compose.yml`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=emision
   DB_USERNAME=emision_user
   DB_PASSWORD=secret
   DB_ROOT_PASSWORD=root_secret
   ```
   - `DB_HOST` debe ser `mysql` (nombre del servicio en `docker-compose.yml`,
     no `127.0.0.1`).
   - `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` los usan tanto Laravel como
     el contenedor `mysql` y `phpmyadmin` para crear la base de datos y el
     usuario.
   - `DB_ROOT_PASSWORD` es exclusivo del contenedor `mysql` (usuario `root`,
     usado también en su healthcheck) y no viene en el `.env.example`, debe
     agregarse manualmente.

## Levantar los servicios con Docker

El proyecto se levanta con Docker Compose, que orquesta 4 servicios: la
aplicación Laravel (PHP-FPM), Nginx, MySQL y phpMyAdmin.

```bash
sudo docker compose up -d --build
```

Al iniciar, el contenedor `app` espera a que MySQL esté disponible, genera la
`APP_KEY` si hace falta, corre las migraciones (`php artisan migrate --force`)
y cachea configuración y rutas automáticamente.

### Comandos de limpieza

Si algo falla o se quiere reiniciar todo desde cero:

```bash
sudo docker compose down
```
Detiene y elimina los contenedores (mantiene los volúmenes, es decir, los
datos de la base de datos).

```bash
sudo docker compose down -v
```
Igual al anterior, pero además borra los volúmenes (se pierden los datos de
la base de datos).

```bash
sudo docker compose down --rmi all
```
Borra también las imágenes construidas por el proyecto.

```bash
sudo docker builder prune -a -f
```
Limpia la caché de build de Docker.

## Enlaces disponibles

Con los servicios levantados:

- Aplicación web (frontend): http://localhost:8000
- Documentación Swagger de la API: http://localhost:8000/api/documentation
- phpMyAdmin: http://localhost:8080

## Comandos importantes

Generar datos de prueba (seed) - crea un usuario, clientes, abogados y casos
de ejemplo:
```bash
sudo docker compose exec app php artisan db:seed
```

Generar un Bearer Token de prueba (Sanctum) para un usuario, creándolo si no
existe - lo necesitará para consultar el detalle de un caso:
```bash
sudo docker compose exec app php artisan token:generate tu@email.com
```

Generar el reporte Excel de casos por abogado por línea de comando (una hoja
por abogado activo), persistiendo una copia en `storage/app/reportes/`:
```bash
sudo docker compose exec app php artisan reporte:excel-abogados
```

## Estructura de proyecto

```
.
├── docker-compose.yml
├── docker/
│   ├── php/
│   │   └── Dockerfile
│   ├── entrypoint.sh
│   └── nginx/
│       └── default.conf
├── app/
│   ├── Models/
│   │   ├── Cliente.php
│   │   ├── Abogado.php
│   │   ├── Caso.php
│   │   └── User.php
│   ├── Http/Controllers/
│   │   ├── Api/
│   │   │   ├── CasoController.php
│   │   │   └── ReporteController.php
│   │   └── CasoWebController.php
│   ├── Exports/
│   │   ├── ReporteCasosPorAbogadoExport.php
│   │   └── CasosPorAbogadoSheetExport.php
│   ├── Console/Commands/
│   │   ├── GenerateApiToken.php
│   │   └── ReporteExcelAbogados.php
│   ├── Services/
│   │   ├── CasoListingService.php
│   │   └── ReporteCasosPorAbogadoService.php
│   └── Enums/
│       └── EstadoCaso.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   │   └── DatabaseSeeder.php
│   ├── factories/
│   └── sql/
│       ├── schema.sql       # Esquema de la base de datos (punto 1)
│       ├── datos.sql        # Datos de prueba (punto 1)
│       └── consultas.sql    # Consultas SQL pedidas (punto 1)
├── routes/
│   ├── api.php
│   └── web.php
├── resources/js/
│   ├── pages/casos/
│   │   ├── index.tsx        # Listado de casos (vista pública)
│   │   └── show.tsx         # Detalle de un caso
│   └── lib/
├── storage/app/reportes/    # Excel generados por el comando artisan
└── tests/Feature/
```

El backend usa **Laravel 13** (`laravel/framework: ^13.17`) con la
arquitectura MVC estándar del framework y Eloquent como ORM.

El frontend usa **React 19** con TypeScript sobre **Inertia.js v3**
(`@inertiajs/react: ^3.0.0`). Inertia permite que las páginas React en
`resources/js/pages` sean renderizadas directamente por rutas y controladores
Laravel, sin necesidad de construir una API REST separada para servir el
frontend.

Los tres archivos pedidos en el punto 1 de la prueba (esquema, datos de
prueba y consultas SQL) están en `database/sql/`: `schema.sql` define las
tablas, `datos.sql` las puebla con datos de ejemplo y `consultas.sql` contiene
las consultas solicitadas.

Los reportes Excel se generan a partir de `ReporteCasosPorAbogadoExport` (una
hoja por abogado, vía `CasosPorAbogadoSheetExport`). El comando artisan
`reporte:excel-abogados` los persiste en `storage/app/reportes/`; el endpoint
de la API (`GET /api/reportes/casos-por-abogado`) los genera al vuelo y los
descarga sin guardarlos en disco.

## Decisiones de diseño

- El listado de casos (`GET /api/casos` y la vista web `/`) es público.
- El detalle de un caso (`GET /api/casos/{id}`) requiere Bearer Token vía
  Sanctum. Como no hay sistema de login para este flujo, el token se genera
  con el comando artisan `token:generate` y se pega manualmente en la vista
  de detalle del frontend.
- Se usa `softDeletes()` en las tablas de negocio (`clientes`, `abogados`,
  `casos`) en vez de `DELETE` físico, cumpliendo el requisito de no eliminar
  registros.
- El endpoint de descarga de reporte Excel (`GET /api/reportes/casos-por-abogado`)
  genera el archivo en memoria y lo descarga sin persistirlo en disco, para no
  acumular archivos en cada descarga. El comando artisan `reporte:excel-abogados`
  sí persiste una copia en `storage/app/reportes/`.

## Cómo correr los tests

```bash
sudo docker compose exec app php artisan test
```
