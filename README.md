# HEQAMIS — Higher Education Quality Assurance Management Information System

Web-based institutional quality self-assessment system. Institutions assess themselves against national minimum standards and accreditation tools, then generate compliance reports and improvement plans.

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
DB_DATABASE=heqamis
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
| admin@heqamis.mw | password | System Administrator |
| nche@heqamis.mw | password | Standards Administrator |
| admin@demo-university.mw | password | Institution Administrator |
| qa@demo-university.mw | password | QA Officer |
| student@demo-university.mw | password | Student (portal: timetable, courses, evaluations) |
| lecturer@demo-university.mw | password | Lecturer |

## Features

- Multi-institution support for HEIs managing their own self-assessments
- Institution profile, org hierarchy, programmes, staff/student data
- Configurable minimum standards and accreditation tools (institutional + programme)
- 0–4 scoring with automated compliance analysis and readiness recommendations
- Evidence repository with version control
- Assessment workflow: Draft → Submitted → Reviewed → Approved → Locked
- Self-Assessment Report and Annual Report generation (PDF/Word)
- Corrective action tracking, dashboards, search, external evaluator invitations
- **Course & student management** — courses with codes, lecturers, student accounts, timetables
- **Student portal** — students view profile, timetable, enrolled courses, and submit anonymous teaching evaluations (NCHE questionnaire)

## Student Portal & Course Management

Institution staff manage academic data from **Programmes → Courses & Students** (or open a programme and click **Manage courses & students**):

1. Add **courses** (code, title, credit hours)
2. Register **lecturers** and **students** (creates login accounts)
3. Create **course offerings** (assign lecturer, semester, enrol students)
4. Build the **timetable** (day, time, venue)
5. Open an **evaluation period** for end-of-semester teaching evaluations

Students log in at the same login page and are redirected to their portal to view their information and evaluate lecturers.

Demo student: `student@demo-university.mw` / `password` (after `migrate --seed`).

## Artisan Commands

```bash
php artisan heqamis:import-tools
php artisan heqamis:import-rubrics
```

Re-import assessment criteria from `Content bank/` markdown files.

Import per-criterion scoring rubrics from `Content bank/scoring/` (run after `heqamis:import-tools`).

## Content Bank

Requirements and NCHE documents live in `Content bank/`:

- SRS, Minimum Standards, Institutional & Programme Accreditation Tools
- Self-Assessment Report and Annual Report templates
