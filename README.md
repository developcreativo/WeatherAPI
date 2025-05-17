# API de Clima Laravel

## Acerca de Este Proyecto

Esta API de Laravel proporciona datos meteorológicos utilizando WeatherAPI e incluye gestión de usuarios con autenticación, historial de búsqueda de clima y funciones de ciudades favoritas. El proyecto demuestra una arquitectura limpia, optimización de API y prácticas de seguridad adecuadas.

## Características Principales

- **Gestión de Usuarios**: Registro y autenticación utilizando Laravel Sanctum
- **Datos Meteorológicos**: Obtener temperatura actual, condiciones, viento, humedad y hora local
- **Historial de Búsqueda**: Almacenar y ver búsquedas de clima pasadas
- **Favoritos**: Guardar y gestionar ciudades favoritas
- **Seguridad y Optimización**: Autenticación de API, almacenamiento en caché de respuestas y manejo de errores

## Implementación Técnica

- **Arquitectura**: Controladores, servicios y modelos para una clara separación de responsabilidades
- **Integración de API**: Cliente HTTP Guzzle para la integración de WeatherAPI
- **Base de Datos**: Migraciones y relaciones entre usuarios, búsquedas y favoritos
- **Pruebas**: Suite de pruebas PHPUnit completa para servicios y endpoints

## Instalación

### Prerrequisitos

- Docker y Docker Compose
- Clave de API de [WeatherAPI](https://www.weatherapi.com/)

### Configuración

1. Clonar el repositorio:

```bash
git clone <url-del-repositorio>
cd weather-api
```

2. Configurar variables de entorno:

```bash
cp .env.example .env
```

3. Agregar tu clave de WeatherAPI al archivo `.env`:

```
WEATHER_API_KEY=tu_clave_api_aqui
```

4. Iniciar la aplicación con Laravel Sail:

```bash
./vendor/bin/sail up -d
```

5. Instalar dependencias y ejecutar migraciones:

```bash
./vendor/bin/sail composer install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

## Endpoints de la API

### Autenticación

- `POST /api/register` - Registrar un nuevo usuario
- `POST /api/login` - Iniciar sesión y obtener token de autenticación
- `POST /api/logout` - Cerrar sesión (requiere autenticación)
- `GET /api/profile` - Obtener perfil del usuario actual (requiere autenticación)

### Clima

- `GET /api/weather?city={ciudad}` - Obtener datos meteorológicos actuales para una ciudad (requiere autenticación)
- `GET /api/weather/history` - Obtener historial de búsqueda (requiere autenticación)
- `DELETE /api/weather/history` - Borrar historial de búsqueda (requiere autenticación)

### Favoritos

- `GET /api/favorites` - Listar todas las ciudades favoritas (requiere autenticación)
- `POST /api/favorites` - Agregar una ciudad a favoritos (requiere autenticación)
- `DELETE /api/favorites/{id}` - Eliminar una ciudad de favoritos (requiere autenticación)

## Pruebas

Ejecutar las pruebas usando Laravel Sail:

```bash
./vendor/bin/sail artisan test
```

## Licencia

Este proyecto es software de código abierto bajo la [licencia MIT](https://opensource.org/licenses/MIT).
# WeatherAPI
