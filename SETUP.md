# Standalone BPLO Portal

This Laravel app exposes the BPLO staff portal: application review, requirement
checks, payment receipt viewing, and permit release. Configure it to use the
same database and private storage as the Applicant and Inspector apps so the
complete application-to-permit workflow stays connected.

## Requirements

- PHP 8.3+ with `pdo_sqlite` (or `pdo_mysql`), `mbstring`, `fileinfo`, `gd`, and `zip`
- Composer

## Setup

Run these commands from the `BPLO_SYSTEM` folder:

```powershell
composer install
copy .env.example .env
php artisan key:generate
```

When the three folders are side by side locally, `.env.example` points this app
to the Applicant app's SQLite database and private storage. For deployments
from separate GitHub repositories or servers, configure all three apps with
the same hosted MySQL database credentials and the same mounted private-storage
directory or private S3 bucket. Keep each app's `APP_KEY`, session cookie, and
cache prefix unique.

`APP_URL` is the BPLO portal address (default `http://localhost:8001`).
`PERMIT_PUBLIC_URL` must match the Applicant app's phone-reachable public URL,
because the applicant-facing verification route reads the shared permit record.
Never use `localhost` for QR codes scanned on another device. Configure an SMTP
mailer if BPLO staff need password-reset emails; the sample environment logs
outgoing mail locally.

Run schema migrations and seed initial users/configuration once from the
Applicant app against the shared database. Do not seed this standalone portal:
seeders would create duplicate users and workflow settings. Sign in here with
an active shared user whose role is `bplo`; registration and non-BPLO
dashboards are not available here.

## Run

```powershell
php artisan serve --port=8001
```

Open `http://localhost:8001`.

Run the portal's focused tests with `php artisan test`.

## Shared data and deployment

Applicants submit and track in the Applicant app, BPLO reviews and releases
here, and Inspectors monitor and scan in the Inspector app. All three read the
same records and private files. Back up the shared database and storage, use
HTTPS, set `APP_DEBUG=false`, and do not expose private uploads publicly.
