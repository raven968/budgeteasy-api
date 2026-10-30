# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- `POST /api/v1/auth/register`: creates a user and returns a Sanctum token for the device.
- `POST /api/v1/auth/login`: returns a token for the device; throttled to 5 attempts per minute per email and IP.
- `POST /api/v1/auth/logout`: revokes only the current device token.
- `GET /api/v1/me`: returns the authenticated user.
- Password policy: minimum 8 characters with letters and numbers; leaked passwords are rejected in production.
- Domain schema: `budgets`, `budget_members`, `budget_invitations`, `categories`, `budget_category_limits` and `expenses`, with models, relationships, factories and the `BudgetRole` enum. Money stored as integer cents; category names unique per user (case-insensitive) among active categories.
- Categories CRUD (`/api/v1/categories`): limit of 10 active categories per user enforced with a row lock, case-insensitive unique names, soft delete; list includes `meta.max`.
- Localization: responses in Spanish (default) or English based on the `Accept-Language` header, returned in `Content-Language`. Spanish translations from Laravel Lang.

## [0.1.0] - 2026-10-29

### Added

- Laravel 13 project on PHP 8.5 with PostgreSQL.
- Sanctum for API token authentication.
- Attribute-based routing with `spatie/laravel-route-attributes`, versioned under `/api/v1`.
- `GET /api/v1/version` endpoint exposing the API version and the minimum supported mobile version.
- Pest test suite running against PostgreSQL.
- Laravel Boost (AI guidelines, skills and MCP server) for development.
