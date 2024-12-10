# BillsDeskDaw

Este proyecto es una aplicación web diseñada para **gestionar y corregir facturas** de manera eficiente. La arquitectura se basa en una **API desarrollada con Laravel** y un **frontend interactivo construido con Vue.js**, ambos contenedorizados mediante Docker para garantizar un entorno de desarrollo y producción consistente y escalable.

---

## Funcionalidades Principales

1. **Gestión de Usuarios, Compañías y Contactos**:
   - Administra usuarios con roles y permisos específicos.
   - Relaciona contactos con compañías para un mejor manejo de datos.

2. **Gestor de Ficheros**:
   - Permite subir facturas en formato CSV o Excel para ser corregidas.
   - Organización y almacenamiento eficiente de los archivos.

3. **Sistema de Autenticación**:
   - Registro e inicio de sesión para usuarios.
   - Autenticación segura basada en tokens (Laravel Sanctum).

4. **Gestor de Facturas**:
   - Descarga de facturas corregidas.
   - Posibilidad de asignar contactos específicos a facturas para una gestión personalizada.

5. **Mapeo de Columnas y Templates**:
   - Herramienta para mapear columnas del archivo CSV a los campos del sistema.
   - Uso de templates de mapeo predefinidos para ahorrar tiempo.

6. **Validaciones y Resaltado**:
   - Validaciones que resaltan celdas con colores para identificar errores o datos importantes.
   - Asegura una correcta interpretación de los datos.

7. **Creación de Nuevas Columnas**:
   - Genera nuevas columnas basadas en datos existentes mediante:
     - Fórmulas personalizadas.
     - Valores estáticos.

8. **Corrector de Columnas**:
   - Identifica y corrige valores erróneos en las columnas del archivo.
   - Facilita la estandarización y limpieza de datos.


## 🧑‍🏫 Instalacion

Sigue los pasos detallados a continuación para configurar y ejecutar la aplicación.

---

### 1. Clonar el Repositorio
Primero, clona el repositorio en tu máquina local:

```bash
git clone https://github.com/Danniellgm03/BillsDeskDaw.git
cd to_project
```

### 2. Configurar .env

Encuentra el archivo .env.prueba en el directorio del proyecto de laravel billsdesk-laravel. 
Y deberas cambiarle el nombre al `.env.prueba` a `.env`

```bash
cd billsdesk-laravel
mv .env.prueba .env
```

Ademas deberas revisar y actualizar las configuraciones necesarias en el archivo .env.
Especialmente las conexiones a base de datos y credenciales

### 3. Levantar Servicios con Docker

El proyecto utiliza docker asi que para gestionar sus servicios tendras que seguir estos pasos:

```bash
docker-compose up -d
```

Este comando iniciara todos los servicios

## 4. Configuracion inicial BD laravel

Para preparar los datos inicales de la base de datos deberas ejecutar los siguientes comando

### 4.1 Introducirte en el docker a traves de bash
```bash
docker exec -it laravel-app bash
```

### 4.2 Ejecutar las migraciones

```bash
php artisan migrate
```

### 4.3 Ejecutar los seeders

```bash
php artisan db:seed
```

### 4.4 🪙 COMODIN

Puedes migrar y reiniciar la base de datos desde ceso, usa:

```bash
php artisan migrate:fresh --seed
```

## 5. Comandos Utiles

### 5.1 Para ejecutar los tests

```bash
php artisan test
```

Y en caso de que quieras unarchivo en especifico

```bash
php artisan test tests/Unit/TuArchivoDeTest.php
```

### 5.2 Parar los servicios

```bash
docker kill id-container
```

### 5.3 Prune Docker

Comandos para borrar las caches de construccion

```bash
docker builder prune -f
docker system prune -f --volumes
```

Eliminar imagenes no utilizadas 

```bash
docker image prune -a
```


## 6. Diagrama

PostgreSQL: Se muestra el esquema relacional tradicional con sus tablas, claves primarias (PK), claves foráneas (FK) y las relaciones entre ellas.

1. **Roles (`roles`)**
   - **PK**: `id`
   - **Relaciones**:
     - Relación 1 a muchos con la tabla `users`. Un rol puede estar asociado con múltiples usuarios.

2. **Usuarios (`users`)**
   - **PK**: `id`
   - **FKs**:
     - `role_id` → `roles.id`: Relación muchos a 1. Un usuario tiene un único rol.
     - `company_id` → `companies.id`: Relación muchos a 1. Un usuario puede pertenecer a una empresa.
   - **Relaciones**:
     - Relación 1 a muchos con `sessions` (para rastrear actividad).
     - Relación 1 a muchos con `files` (campos `created_by`, `updated_by`, y `deleted_by`).

3. **Empresas (`companies`)**
   - **PK**: `id`
   - **Relaciones**:
     - Relación 1 a muchos con `users`. Una empresa puede tener múltiples usuarios.
     - Relación 1 a muchos con `invitations`. Las invitaciones están asociadas a una empresa.
     - Relación 1 a muchos con `files`. Los archivos subidos están vinculados a una empresa.
     - Relación 1 a muchos con `invoices`. Una empresa puede tener múltiples facturas.

4. **Archivos (`files`)**
   - **PK**: `id`
   - **FKs**:
     - `company_id` → `companies.id`: Relación muchos a 1. Los archivos están asociados con una empresa.
     - `created_by`, `updated_by`, `deleted_by` → `users.id`: Relación muchos a 1 con `users`.
   - **Relaciones**:
     - Relación muchos a 1 con `invoices`. Las facturas están vinculadas a un archivo.

5. **Facturas (`invoices`)**
   - **PK**: `id`
   - **FKs**:
     - `company_id` → `companies.id`: Relación muchos a 1. Una factura pertenece a una empresa.
     - `user_id` → `users.id`: Relación muchos a 1. Una factura está asignada a un usuario.
     - `file_id` → `files.id`: Relación muchos a 1. Una factura está vinculada a un archivo.

6. **Invitaciones (`invitations`)**
   - **PK**: `id`
   - **FKs**:
     - `role_id` → `roles.id`: Relación muchos a 1. Una invitación está asociada a un rol.
     - `company_id` → `companies.id`: Relación muchos a 1. Una invitación está vinculada a una empresa.

7. **Contactos (`contacts`)**
   - **PK**: `id`
   - **Relaciones**:
     - Relación independiente (sin claves foráneas explícitas), pero útil para asociar contactos con usuarios, empresas o facturas de forma lógica.


![CLASES_RELACIONAL](/images/relacional.png)

MongoDB: Se incluye un diagrama conceptual que destaca cómo se organizan los documentos y sus relaciones dinámicas en la base de datos NoSQL.

---

#### 1. **Invoice Templates (`invoice_templates`)**
   - **Descripción**:  
     Esta colección almacena las plantillas de facturas que definen cómo se mapearán las columnas, las reglas de validación y las fórmulas aplicadas a los datos de entrada.
   - **Campos Principales**:
     - `company_id`: Identificador de la empresa asociada.
     - `template_name`: Nombre de la plantilla.
     - `column_mappings`: Configuración de mapeo de columnas entre los datos de entrada y los campos del sistema.
     - `formulas`: Fórmulas personalizadas para calcular valores.
     - `validation_rules`: Reglas de validación para destacar errores o resaltar datos relevantes.
     - `aggregations`: Reglas de agregación para combinar o procesar datos.
     - `created_at`, `updated_at`: Fechas de creación y última actualización.
   - **Relaciones**:
     - Relación uno a muchos con `correction_rules` (reglas de corrección) mediante el campo `template_id`.

---

#### 2. **Correction Rules (`correction_rules`)**
   - **Descripción**:  
     Esta colección almacena reglas específicas para corregir datos en las plantillas de facturas.
   - **Campos Principales**:
     - `company_id`: Identificador de la empresa asociada.
     - `rule_name`: Nombre de la regla de corrección.
     - `conditions`: Condiciones que deben cumplirse para aplicar las correcciones.
     - `corrections`: Transformaciones o ajustes que se aplicarán a los datos.
     - `template_id`: Relación con la plantilla de factura asociada (`invoice_templates`).
     - `created_at`, `updated_at`: Fechas de creación y última actualización.
   - **Relaciones**:
     - Relación muchos a uno con `invoice_templates` mediante el campo `template_id`.

---

#### Relación Principal entre Colecciones

1. **Invoice Templates ↔ Correction Rules**:
   - Una plantilla de factura (`invoice_templates`) puede tener múltiples reglas de corrección (`correction_rules`).
   - Cada regla de corrección pertenece a una única plantilla.

**Ejemplo Conceptual:**
- Una empresa crea una plantilla para mapear las columnas de una factura.
- La plantilla define las reglas de validación y fórmulas necesarias.
- Se asocian múltiples reglas de corrección para transformar o corregir los valores erróneos de los datos cargados.

---

![CLASES_NO_RELACIONAL](/images/mongo.png)


## 7. Dependencias Laravel

Este proyecto utiliza varias dependencias tanto para su funcionalidad principal como para el desarrollo. A continuación, se listan las dependencias divididas en **producción** y **desarrollo**.

---

### Dependencias de Producción (`require`)

Estas son las dependencias necesarias para que el proyecto funcione correctamente en producción:

1. **PHP** (`^8.2`)  
   - Lenguaje base requerido para Laravel y sus dependencias.

2. **jenssegers/mongodb** (`^5.1`)  
   - Paquete para integrar MongoDB como base de datos en Laravel.

3. **laravel/framework** (`^11.9`)  
   - El núcleo del framework Laravel.

4. **laravel/sanctum** (`^4.0`)  
   - Paquete para manejar la autenticación basada en tokens API.

5. **laravel/tinker** (`^2.9`)  
   - Herramienta para interactuar con la aplicación desde la línea de comandos.

6. **maatwebsite/excel** (`^3.1.57`)  
   - Paquete para trabajar con archivos Excel, como importar y exportar datos.

7. **markrogoyski/math-php** (`^2.10`)  
   - Librería para realizar cálculos matemáticos avanzados.

---

### Dependencias de Desarrollo (`require-dev`)

Estas dependencias están diseñadas para el desarrollo y pruebas del proyecto:

1. **fakerphp/faker** (`^1.23`)  
   - Generador de datos ficticios para pruebas y desarrollo.

2. **laravel/pail** (`^1.1`)  
   - Herramienta para ver y analizar logs en tiempo real.

3. **laravel/pint** (`^1.13`)  
   - Formateador de código PHP para mantener un estilo consistente.

4. **laravel/sail** (`^1.26`)  
   - Configuración sencilla de un entorno Docker para Laravel.

5. **mockery/mockery** (`^1.6`)  
   - Framework para crear mocks y realizar pruebas unitarias.

6. **nunomaduro/collision** (`^8.1`)  
   - Mejora la experiencia de depuración mostrando errores en la línea de comandos.

7. **phpunit/phpunit** (`^11.0.1`)  
   - Framework para pruebas unitarias y funcionales en PHP.


## 8. Dependencias Vue

Este proyecto utiliza varias dependencias tanto para su funcionalidad principal como para el desarrollo. A continuación, se listan las dependencias divididas en **producción** y **desarrollo**.

---

### Dependencias de Producción (`dependencies`)

Estas son las dependencias necesarias para que el proyecto Vue funcione correctamente en producción:

1. **@primevue/themes** (`^4.2.1`)  
   - Temas personalizados para los componentes de PrimeVue.

2. **js-cookie** (`^3.0.5`)  
   - Librería para manejar cookies en el navegador.

3. **pinia** (`^2.2.8`)  
   - Sistema de gestión de estado para Vue.

4. **primeicons** (`^7.0.0`)  
   - Iconos utilizados con los componentes de PrimeVue.

5. **primevue** (`^4.2.1`)  
   - Biblioteca de componentes UI para Vue.js.

6. **vue** (`^3.5.12`)  
   - Framework base para construir la aplicación.

7. **vue-i18n** (`^10.0.5`)  
   - Soporte de internacionalización para la aplicación Vue.

8. **vue-router** (`^4.4.5`)  
   - Enrutador para manejar la navegación entre las vistas de la aplicación.

---

### Dependencias de Desarrollo (`devDependencies`)

Estas dependencias están diseñadas para el desarrollo del proyecto:

1. **@vitejs/plugin-vue** (`^5.1.4`)  
   - Plugin oficial para integrar Vue.js con Vite.

2. **sass** (`^1.80.6`)  
   - Preprocesador CSS utilizado para personalizar estilos.

3. **vite** (`^5.4.8`)  
   - Herramienta de construcción rápida para aplicaciones modernas.


## 9. Pruebas

Se han realizado 301 test unitarios para garantizar el correcto funcionamiento de la api (Laravel)

![TESTS](/images/tests.png)