# Prompt 09 — Shared Configuration, Infrastructure & Dev Setup

## Context
Shared setup instructions for both `ekontrak-api` and `ekontrak-web` projects. Follow these to ensure both apps work correctly together.

---

## Directory Structure

```
/ekontrak/
  ├── ekontrak-api/        ← Laravel 12 API project
  └── ekontrak-web/        ← Laravel 12 Web project
```

---

## Nginx Configuration (v1.29.3)

Two server blocks in `/etc/nginx/sites-available/`:

### ekontrak-api (port 8001)
```nginx
server {
    listen 8001;
    server_name localhost;
    root /var/www/ekontrak/ekontrak-api/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### ekontrak-web (port 8000)
```nginx
server {
    listen 8000;
    server_name localhost;
    root /var/www/ekontrak/ekontrak-web/public;
    index index.php;

    # Same location blocks as above
    # root → ekontrak-web/public
}
```

---

## GitHub Repository Structure

```
ekontrak/
├── ekontrak-api/
│   ├── .env.example
│   ├── README.md
│   └── ... (Laravel project)
└── ekontrak-web/
    ├── .env.example
    ├── README.md
    └── ... (Laravel project)
```

**Branch strategy**:
- `main` — production
- `develop` — active development
- `feature/{module-name}` — per feature

---

## ApiService — Full Implementation (ekontrak-web)

Create `app/Services/ApiService.php`:

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class ApiService
{
    protected string $baseUrl;
    protected ?string $token = null;

    public function __construct()
    {
        $this->baseUrl = config('api.base_url');
    }

    public function withAuth(): static
    {
        $clone = clone $this;
        $clone->token = session('api_token');
        return $clone;
    }

    protected function client()
    {
        $client = Http::baseUrl($this->baseUrl)
            ->timeout(30)
            ->acceptJson();

        if ($this->token) {
            $client = $client->withToken($this->token);
        }

        return $client;
    }

    public function get(string $endpoint, array $params = []): array
    {
        $response = $this->client()->get($endpoint, $params);
        return $this->handle($response);
    }

    public function post(string $endpoint, array $data = []): array
    {
        $response = $this->client()->post($endpoint, $data);
        return $this->handle($response);
    }

    public function put(string $endpoint, array $data = []): array
    {
        $response = $this->client()->put($endpoint, $data);
        return $this->handle($response);
    }

    public function delete(string $endpoint): array
    {
        $response = $this->client()->delete($endpoint);
        return $this->handle($response);
    }

    protected function handle(Response $response): array
    {
        if ($response->status() === 401) {
            session()->forget(['api_token', 'user', 'roles']);
            abort(redirect()->route('login')->with('error', 'Sesi anda telah tamat. Sila log masuk semula.'));
        }

        return $response->json() ?? ['success' => false, 'message' => 'Tiada respons dari pelayan.'];
    }
}
```

Bind in `AppServiceProvider`:
```php
$this->app->singleton(ApiService::class, fn() => new ApiService());
```

Inject into controllers via constructor:
```php
public function __construct(protected ApiService $apiService) {}
```

---

## Shared Blade Components (ekontrak-web)

Create these reusable Blade components:

### `resources/views/components/stat-card.blade.php`
```blade
@props(['label', 'value', 'icon', 'colour' => 'blue', 'clickable' => false])
<div class="stat-card stat-card--{{ $colour }} {{ $clickable ? 'stat-card--clickable' : '' }}" {{ $attributes }}>
    <div class="stat-card__icon">{{ $icon }}</div>
    <div class="stat-card__value">{{ $value }}</div>
    <div class="stat-card__label">{{ $label }}</div>
</div>
```

### `resources/views/components/alert-section.blade.php`
```blade
@props(['type' => 'warning', 'count', 'message', 'contracts' => []])
<div class="alert-section alert-section--{{ $type }}">
    <div class="alert-section__header" onclick="toggleAlert(this)">
        <span class="alert-section__icon">...</span>
        <span>({{ $count }} Kontrak) — {{ $message }}</span>
        <span class="alert-section__chevron">›</span>
    </div>
    <div class="alert-section__body">
        {{ $slot }}
    </div>
</div>
```

### `resources/views/components/modal.blade.php`
```blade
@props(['id', 'title', 'subtitle' => null, 'size' => 'lg'])
<div id="{{ $id }}" class="modal modal--{{ $size }}" style="display:none;">
    <div class="modal__overlay" onclick="closeModal('{{ $id }}')"></div>
    <div class="modal__container">
        <div class="modal__header">
            <div>
                <h2 class="modal__title">{{ $title }}</h2>
                @if($subtitle)<p class="modal__subtitle">{{ $subtitle }}</p>@endif
            </div>
            <button class="modal__close" onclick="closeModal('{{ $id }}')">×</button>
        </div>
        <div class="modal__body">{{ $slot }}</div>
    </div>
</div>
```

### `resources/views/components/toast.blade.php`
```blade
<div id="toast-container" class="toast-container"></div>
<script>
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.textContent = message;
    document.getElementById('toast-container').appendChild(toast);
    setTimeout(() => toast.classList.add('toast--show'), 10);
    setTimeout(() => { toast.classList.remove('toast--show'); setTimeout(() => toast.remove(), 300); }, 3000);
}
</script>
```

---

## CSS Design System (`public/css/ekontrak.css`)

```css
:root {
    /* Brand colours */
    --color-primary: #1e40af;       /* Deep blue */
    --color-primary-dark: #1e3a8a;
    --color-primary-light: #3b82f6;
    --color-accent: #06b6d4;        /* Cyan accent */
    --color-sidebar: #0f172a;       /* Dark navy sidebar */
    --color-sidebar-text: #cbd5e1;
    --color-sidebar-active: #1e40af;

    /* Status colours */
    --color-status-draf: #6b7280;
    --color-status-aktif: #2563eb;
    --color-status-selesai: #16a34a;
    --color-status-eot: #7c3aed;
    --color-status-warning: #d97706;
    --color-status-danger: #dc2626;

    /* Neutral */
    --color-bg: #f8fafc;
    --color-surface: #ffffff;
    --color-border: #e2e8f0;
    --color-text: #0f172a;
    --color-text-muted: #64748b;

    /* Layout */
    --sidebar-width: 220px;
    --topbar-height: 56px;
    --border-radius: 6px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
    --shadow-md: 0 4px 12px rgba(0,0,0,0.12);
}
```

---

## Security Checklist

Both projects must implement:

**ekontrak-api**:
- [ ] Rate limiting on `/api/v1/auth/login` (5 attempts per minute per IP)
- [ ] All responses include `Content-Type: application/json`
- [ ] CORS configured for `ekontrak-web` origin only
- [ ] Sanctum token expiry set (e.g. 8 hours)
- [ ] Password hashing: bcrypt with cost 12
- [ ] SQL injection: always use Eloquent / Query Builder (never raw string concatenation)
- [ ] Input validation on every POST/PUT endpoint
- [ ] Audit log on all write operations

**ekontrak-web**:
- [ ] CSRF protection enabled (all forms use `@csrf`)
- [ ] Session fixation prevention (regenerate session on login)
- [ ] XSS: always use `{{ }}` not `{!! !!}` unless explicitly safe
- [ ] API token stored only in server-side session (never in cookies or JS)
- [ ] Auto-logout after 120 minutes of inactivity

---

## Development Commands

```bash
# ekontrak-api
cd ekontrak-api
php artisan migrate --seed
php artisan serve --port=8001

# ekontrak-web
cd ekontrak-web
php artisan serve --port=8000

# Run scheduler (for contract expiry checks)
cd ekontrak-api
php artisan schedule:work

# Run queue worker (for emails)
cd ekontrak-api
php artisan queue:work
```

---

## .env.example files

**ekontrak-api/.env.example**:
```
APP_NAME=eKontrak-API
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8001

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ekontrak
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@kpkt.gov.my
MAIL_FROM_NAME="eKontrak KPKT"

QUEUE_CONNECTION=database
SANCTUM_STATEFUL_DOMAINS=localhost:8000
```

**ekontrak-web/.env.example**:
```
APP_NAME=eKontrak
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

API_BASE_URL=http://localhost:8001/api/v1

SESSION_DRIVER=file
SESSION_LIFETIME=120
```
