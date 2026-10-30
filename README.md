# BudgetEasy API

API REST de BudgetEasy (Laravel 13 · PHP 8.5 · PostgreSQL). Convenciones y reglas del dominio en [CLAUDE.md](CLAUDE.md).

## Requisitos

- PHP 8.5 y Composer (Laravel Herd)
- PostgreSQL (Docker)

## Instalación

```sh
composer install
cp .env.example .env
php artisan key:generate
# Configura DB_USERNAME / DB_PASSWORD en .env y crea las bases `budgeteasy` y `budgeteasy_testing`
php artisan migrate
herd link budgeteasy   # sirve la API en http://budgeteasy.test
```

## Documentación del API

En local: http://budgeteasy.test/docs/api (OpenAPI en `/docs/api.json`). Exportar: `php artisan scramble:export`.

## Pruebas y formato

```sh
php artisan test
vendor/bin/pint
```

## Versionado

SemVer. Ver [CHANGELOG.md](CHANGELOG.md) y `config/versioning.php`.
