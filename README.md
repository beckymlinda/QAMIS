# QAMIS — Quality Assurance Management Information System

Web-based NCHE-aligned compliance monitoring system for Malawian higher education institutions.

## Stack

- Laravel 12 (PHP 8.2+)
- MySQL 8 (SQLite supported for local dev)
- Laravel Breeze (authentication)
- Spatie Laravel Permission (RBAC)
- DomPDF + PhpWord (report export)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qamis
DB_USERNAME=root
DB_PASSWORD=
```

Or use SQLite (default in `.env.example`):

```bash
touch database/database.sqlite
```

Run migrations and seeders:

```bash
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

## Default Users

| Email | Password | Role |
|---|---|---|
| admin@qamis.mw | password | System Administrator |
| nche@qamis.mw | password | NCHE Administrator |
| admin@demo-university.mw | password | Institution Administrator |
| qa@demo-university.mw | password | QA Officer |

## Features

- Multi-institution tenancy with NCHE oversight
- Institution profile, org hierarchy, programmes, staff/student data
- Configurable NCHE standards and assessment tools (institutional + programme)
- 0–4 scoring with automated compliance engine and accreditation recommendations
- Evidence repository with version control
- Assessment workflow: Draft → Submitted → Reviewed → Approved → Locked
- Self-Assessment Report and Annual Report generation (PDF/Word)
- Corrective action tracking, dashboards, search, external evaluator invitations

## Artisan Commands

```bash
php artisan qamis:import-tools
```

Re-import assessment criteria from `Content bank/` markdown files.

## Content Bank

Requirements and NCHE documents live in `Content bank/`:

- SRS, Minimum Standards, Institutional & Programme Accreditation Tools
- Self-Assessment Report and Annual Report templates
