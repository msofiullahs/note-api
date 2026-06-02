# Notes API

A small REST API for a personal notes app, built on **Laravel 13** with
**Laravel Sanctum** for token-based authentication.

Responses are JSON only — there is no frontend. Each user can only see and
modify their own notes.

---

## Features

- Email/password registration and login, with Sanctum personal access tokens
- Full CRUD for notes (`title`, `content`, timestamps)
- Per-user authorization enforced by a policy (cross-user access returns 403)
- FormRequest validation (422 with `{message, errors}` on bad input)
- List endpoint supports `?search=` (LIKE on title) and `?per_page=` pagination
- Soft delete (`deleted_at`) — deleted notes disappear from the API but
  remain recoverable in the database

---

## Requirements

| Component | Version    |
|-----------|------------|
| PHP       | 8.3 or newer |
| Composer  | 2.x        |
| Database  | SQLite (default), MySQL/MariaDB, or PostgreSQL |

If you use the Docker setup below, you only need Docker installed — every
other dependency runs inside the container.

---

## API endpoints

All notes endpoints require `Authorization: Bearer <token>`.

| Method | URL               | Auth | Description                                     |
|--------|-------------------|------|-------------------------------------------------|
| POST   | `/api/register`   | —    | Create an account, returns `{user, token}`     |
| POST   | `/api/login`      | —    | Authenticate, returns `{user, token}`          |
| POST   | `/api/logout`     | yes  | Revoke the token used on the current request   |
| GET    | `/api/me`         | yes  | The authenticated user                         |
| GET    | `/api/notes`      | yes  | List the caller's notes (paginated)            |
| POST   | `/api/notes`      | yes  | Create a new note                              |
| GET    | `/api/notes/{id}` | yes  | View a single note                             |
| PUT    | `/api/notes/{id}` | yes  | Update title and/or content                    |
| DELETE | `/api/notes/{id}` | yes  | Soft-delete a note                             |

### Query parameters for `GET /api/notes`

| Param     | Type    | Default | Notes                                  |
|-----------|---------|---------|----------------------------------------|
| `search`  | string  | —       | Case-insensitive LIKE filter on title  |
| `per_page`| integer | 15      | Clamped to `1..100`                    |
| `page`    | integer | 1       | Standard Laravel pagination            |

### HTTP status codes

| Code | Meaning                                                |
|------|--------------------------------------------------------|
| 200  | OK (read or update)                                    |
| 201  | Created (register, store)                              |
| 204  | No Content (delete)                                    |
| 401  | Missing or invalid token                               |
| 403  | Authenticated, but the note belongs to another user    |
| 404  | Note does not exist (or is soft-deleted)               |
| 422  | Validation failed                                      |

---

## Installation

The Laravel side of the setup is the same on every platform:

```bash
git clone <your-repo-url> note-api
cd note-api
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

What differs per platform is **how you get PHP and Composer**, and **how
you serve the app** during development. Pick whichever section matches
your machine.

### macOS

The easiest path on macOS is Homebrew plus Laravel Valet. Valet gives
every project under a parked directory a friendly `.test` hostname and
handles PHP-FPM, dnsmasq, and HTTPS for you.

```bash
# 1. Install Homebrew if you don't have it.
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# 2. PHP + Composer
brew install php composer

# 3. Valet (optional but recommended for *.test hostnames)
composer global require laravel/valet
# Make sure ~/.composer/vendor/bin is on your PATH:
echo 'export PATH="$HOME/.composer/vendor/bin:$PATH"' >> ~/.zshrc
exec zsh
valet install

# 4. Clone and bootstrap the project
git clone <your-repo-url> ~/Sites/note-api
cd ~/Sites/note-api
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate

# 5a. Serve via Valet — http://note-api.test
valet park ~/Sites      # do once per directory
# or:
valet link note-api     # do once per project

# 5b. Or use the built-in server — http://127.0.0.1:8000
php artisan serve
```

If you prefer an all-in-one GUI installer, [Laravel Herd](https://herd.laravel.com)
bundles PHP and a Valet-equivalent web server with no Homebrew required.

### Linux (Ubuntu / Debian)

```bash
# 1. Add Ondrej's PPA for modern PHP versions (Ubuntu/Debian).
sudo apt update
sudo apt install -y software-properties-common
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update

# 2. PHP 8.3 with the extensions Laravel needs.
sudo apt install -y \
    php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl \
    php8.3-zip php8.3-sqlite3 php8.3-bcmath php8.3-intl \
    composer git unzip

# 3. Clone and bootstrap.
git clone <your-repo-url> note-api
cd note-api
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate

# 4. Run the dev server — http://127.0.0.1:8000
php artisan serve
```

For Fedora/RHEL substitute `dnf install php php-cli php-mbstring php-xml
php-pdo php-zip php-intl composer`. For Arch use `pacman -S php
php-sqlite composer`.

### Windows

You have two good options on Windows: **Laragon** (native PHP/Apache/MySQL
stack with one-click setup), or **WSL2** (run the Linux instructions above
inside a Linux subsystem).

#### Option A — Laragon (native)

1. Download and install Laragon from <https://laragon.org>.
   The "Full" installer includes PHP, Composer, and Apache.
2. Make sure PHP is at least 8.3 from Laragon's *Menu → PHP → Version*.
3. Open Laragon's terminal (*Menu → Tools → Quick add → cmder*) and run:

```powershell
cd C:\laragon\www
git clone <your-repo-url> note-api
cd note-api
composer install
copy .env.example .env
php artisan key:generate
type nul > database\database.sqlite
php artisan migrate
php artisan serve
```

With Laragon's *Auto Virtual Hosts* on, the app is also reachable at
`http://note-api.test/` after starting Laragon.

#### Option B — WSL2 (recommended for parity with production)

1. In an elevated PowerShell: `wsl --install -d Ubuntu`. Reboot.
2. Open the new Ubuntu terminal and follow the **Linux (Ubuntu/Debian)**
   section above verbatim. The dev server is reachable from Windows at
   `http://127.0.0.1:8000`.

### Docker

A `Dockerfile` and `docker-compose.yml` are included. The image installs
PHP 8.3 with the SQLite, zip, intl, and bcmath extensions, runs
`composer install`, copies the project in, and serves the app on
port 8000.

```bash
git clone <your-repo-url> note-api
cd note-api
docker compose up --build
```

The container automatically:

1. Copies `.env.example` to `.env` if `.env` does not exist
2. Generates `APP_KEY`
3. Creates an empty `database/database.sqlite` file
4. Runs `php artisan migrate`
5. Starts `php artisan serve` on `0.0.0.0:8000`

Visit <http://127.0.0.1:8000/api/me> to sanity-check (you should get a
401, since you have no token yet).

To stop the stack: `docker compose down`. To rebuild after editing the
Dockerfile: `docker compose up --build --force-recreate`.

---

## Trying it out

```bash
# Pick whichever base URL matches your setup
BASE=http://127.0.0.1:8000/api      # artisan serve / Docker
# BASE=http://note-api.test/api     # Valet / Laragon

# 1. Register
curl -s -X POST "$BASE/register" \
    -H 'Accept: application/json' -H 'Content-Type: application/json' \
    -d '{
        "name": "Alice",
        "email": "alice@example.com",
        "password": "password123",
        "password_confirmation": "password123"
    }'

# Save the token from the response, e.g.:
TOKEN="1|xxxxxxxxxxxxxxxxxxxxxxx"

# 2. Create a note
curl -s -X POST "$BASE/notes" \
    -H 'Accept: application/json' -H 'Content-Type: application/json' \
    -H "Authorization: Bearer $TOKEN" \
    -d '{"title": "Grocery list", "content": "milk, eggs, bread"}'

# 3. List notes (with pagination + search)
curl -s "$BASE/notes?search=Grocery&per_page=5" \
    -H 'Accept: application/json' \
    -H "Authorization: Bearer $TOKEN"
```

---

## Testing

```bash
php artisan test
```

The repository ships with the default Laravel test runner (PHPUnit 12).
Feature tests should hit the API through the test client rather than
mocking the database — SQLite makes a real DB cheap enough.

---

## Project layout (the parts that matter)

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php    # register / login / logout / me
│   │   └── NoteController.php    # apiResource('notes')
│   ├── Requests/
│   │   ├── Auth/
│   │   │   ├── LoginRequest.php
│   │   │   └── RegisterRequest.php
│   │   ├── StoreNoteRequest.php
│   │   └── UpdateNoteRequest.php
│   └── Resources/
│       └── NoteResource.php
├── Models/
│   ├── Note.php                  # SoftDeletes, belongsTo User
│   └── User.php                  # HasApiTokens, hasMany Note
└── Policies/
    └── NotePolicy.php            # owner-only view/update/delete

routes/api.php                    # public auth routes + Route::apiResource('notes')
database/migrations/              # users / notes / soft_deletes / sanctum tokens
```

---

## License

MIT — see the Laravel framework for upstream license terms.
