# Pig Farm Management System - Project Structure & Configuration

## 1. Directory Structure Overview

```
PigFarmManagementSystem/
│
├── 📁 app/                             # Application logic layer
│   ├── 📁 Actions/                     # Form actions (Jetstream/Fortify)
│   ├── 📁 Console/                     # Artisan commands
│   │   └── Commands/
│   ├── 📁 Exceptions/                  # Exception handling
│   ├── 📁 Helpers/                     # Business logic helpers (7 files)
│   ├── 📁 Http/
│   │   ├── Controllers/                # Request handlers (23 controllers)
│   │   ├── Middleware/                 # HTTP middleware
│   │   └── Kernel.php                  # HTTP kernel
│   ├── 📁 Mail/                        # Email templates (4 mail classes)
│   ├── 📁 Models/                      # Eloquent models (33 models)
│   ├── 📁 Observers/                   # Model observers (3 observers)
│   ├── 📁 Providers/                   # Service providers
│   ├── 📁 Services/                    # Business logic services (4+ services)
│   └── 📁 View/                        # View composers/service providers
│
├── 📁 bootstrap/                       # Bootstrap files
│   ├── app.php
│   └── cache/
│
├── 📁 config/                          # Configuration files
│   ├── app.php                         # App configuration
│   ├── auth.php                        # Auth guards & providers
│   ├── cache.php                       # Cache config
│   ├── database.php                    # Database connections
│   ├── filesystems.php                 # Storage config (Cloudinary)
│   ├── mail.php                        # Mail driver config
│   ├── session.php                     # Session config
│   └── [other configs]                 # Queue, logging, etc.
│
├── 📁 database/                        # Database layer
│   ├── 📁 factories/                   # Model factories (testing)
│   ├── 📁 migrations/                  # Database migrations (30+ migrations)
│   ├── 📁 seeders/                     # Database seeders
│   └── database.sqlite                 # SQLite (if testing)
│
├── 📁 lang/                            # Localization files
│   └── en/
│
├── 📁 public/                          # Public assets
│   ├── index.php                       # Entry point
│   ├── 📁 admin/                       # Admin panel assets
│   ├── 📁 assets/                      # CSS, JS, images
│   ├── 📁 js/
│   └── 📁 fonts/
│
├── 📁 resources/                       # View & asset sources
│   ├── 📁 css/
│   │   └── app.css
│   ├── 📁 js/
│   │   └── app.js
│   ├── 📁 layouts/                     # Blade layouts
│   └── 📁 views/                       # Blade templates (40+ views)
│       ├── auth/
│       ├── [feature views]
│       └── components/
│
├── 📁 routes/                          # Route definitions
│   ├── web.php                         # Web routes (200+ routes)
│   ├── api.php                         # API routes (50+ endpoints)
│   └── console.php                     # Console commands
│
├── 📁 storage/                         # Runtime data storage
│   ├── app/                            # File uploads (temp)
│   ├── fonts/                          # PDF fonts
│   ├── framework/                      # Framework cache
│   └── logs/                           # Application logs
│
├── 📁 tests/                           # Test suite
│   ├── 📁 Feature/                     # Feature tests
│   ├── 📁 Unit/                        # Unit tests
│   └── CreatesApplication.php
│
├── 📁 backups/                         # Database backups
│   └── backup_.sql
│
├── 📁 DOCUMENTATION/                   # Project documentation (created)
│   ├── 01_WORKFLOW_DIAGRAM.md
│   ├── 02_ER_DIAGRAM.md
│   ├── 03_HTA.md
│   ├── 04_ARCHITECTURE_OBSERVERS_SERVICES_HELPERS.md
│   ├── 05_DATA_DICTIONARY.md
│   └── 06_ROUTES_API.md
│
├── 📄 artisan                          # Artisan CLI
├── 📄 composer.json                    # PHP dependencies
├── 📄 composer.lock                    # Locked dependency versions
├── 📄 package.json                     # Node dependencies
├── 📄 package-lock.json
├── 📄 phpunit.xml                      # PHPUnit configuration
├── 📄 postcss.config.js                # PostCSS config
├── 📄 tailwind.config.js               # Tailwind CSS config
├── 📄 .env                             # Environment variables
├── 📄 .env.example                     # Environment template
├── 📄 .gitignore                       # Git ignore rules
└── 📄 README.md                        # Project readme
```

---

## 2. Key Configuration Files

### 2.1 composer.json - PHP Dependencies

**Core Framework:**
```json
{
  "require": {
    "php": "^8.1",
    "laravel/framework": "^9.19",
    "laravel/jetstream": "^3.0",
    "laravel/sanctum": "^3.0",
    "laravel/tinker": "^2.8"
  }
}
```

**Database:**
```json
{
  "doctrine/dbal": "^3.10"
}
```

**File Storage:**
```json
{
  "cloudinary/cloudinary_php": "^2.0"
}
```

**PDF Generation:**
```json
{
  "barryvdh/laravel-dompdf": "^2.2"
}
```

**Excel Export:**
```json
{
  "maatwebsite/excel": "^3.1"
}
```

**Authentication:**
```json
{
  "laravel/fortify": "^1.19"
}
```

**See `composer.json` for complete list**

### 2.2 package.json - Node Dependencies

**Frontend Build:**
```json
{
  "devDependencies": {
    "axios": "^1.1",
    "laravel-vite-plugin": "^0.7.0",
    "tailwindcss": "^3.0",
    "postcss": "^8.0",
    "vite": "^4.0"
  }
}
```

**Chart Library:**
```json
{
  "dependencies": {
    "chart.js": "^3.9"
  }
}
```

### 2.3 .env Configuration

```env
# App
APP_NAME="Pig Farm Management System"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://yourapp.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pig_farm_db
DB_USERNAME=pig_farm_user
DB_PASSWORD=secure_password

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=username
MAIL_PASSWORD=password
MAIL_FROM_ADDRESS=noreply@pigfarm.com
MAIL_FROM_NAME="Pig Farm System"

# File Storage (Cloudinary)
CLOUDINARY_NAME=your_cloud_name
CLOUDINARY_KEY=your_api_key
CLOUDINARY_SECRET=your_api_secret

# Queue
QUEUE_CONNECTION=sync (or 'database', 'redis')

# Cache
CACHE_DRIVER=redis (or 'file', 'database')

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

### 2.4 Database Configuration (config/database.php)

```php
'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE', 'pig_farm_db'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'unix_socket' => env('DB_SOCKET', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => 'InnoDB',
        'options' => [],
    ],
],
```

### 2.5 Mail Configuration

**SMTP Setup (Production):**
- MAILER: SMTP
- HOST: smtp.gmail.com or custom SMTP
- PORT: 465 (SSL) or 587 (TLS)
- AUTHENTICATION: Username + Password

**Local Testing:**
```env
MAIL_MAILER=log
```

### 2.6 Cloudinary Configuration (config/cloudinary.php)

```php
return [
    'cloud' => [
        'cloud_name' => env('CLOUDINARY_NAME'),
        'api_key' => env('CLOUDINARY_KEY'),
        'api_secret' => env('CLOUDINARY_SECRET'),
    ]
];
```

**Usage:** 
- Receipt uploads
- Batch photos
- Invoice PDFs

### 2.7 Authentication Configuration (config/auth.php)

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
],
```

---

## 3. Application Layers

### Layer 1: Entry Point
```
public/index.php
↓
bootstrap/app.php
```

### Layer 2: Request Handler
```
routes/web.php | routes/api.php
↓
Http/Middleware/
↓
Http/Controllers/
```

### Layer 3: Business Logic
```
Services/               # Main business operations
↓
Helpers/                # Utility functions
↓
Observers/              # Event listeners
```

### Layer 4: Data Access
```
Models/                 # Eloquent models
↓
database.php            # Connection config
```

### Layer 5: Database
```
MySQL Database
↓
32+ Tables with relationships
```

### Layer 6: Presentation
```
Blade Templates (resources/views/)
↓
Bootstrap 5 UI + Chart.js
↓
JavaScript (app.js)
```

### Layer 7: External Services
```
Cloudinary (file storage)
Email (SMTP)
PDF Generation (DomPDF)
```

---

## 4. Middleware Chain

### Web Middleware Stack (Http/Kernel.php)

```
HttpKernel::$middleware
↓
EncryptCookies
↓
AddQueuedCookiesToResponse
↓
StartSession
↓
ShareErrorsFromSession
↓
VerifyCsrfToken
↓
SubstituteBindings

↓

RouteMiddleware::$middleware
├── auth (Authenticate)
├── guest (RedirectIfAuthenticated)
├── permission (CheckPermission)
├── prevent.cache (PreventCache)
└── [custom middleware]
```

---

## 5. Service Provider Registration

### AppServiceProvider (app/Providers/AppServiceProvider.php)

```php
register() {
    // Service binding
    $this->app->bind(PaymentService::class, function () { ... });
}

boot() {
    // Model observers
    Batch::observe(BatchObserver::class);
    Cost::observe(CostObserver::class);
    InventoryMovement::observe(InventoryMovementObserver::class);
    PigDeath::observe(PigDeathObserver::class);
    
    // Policies
    // Route macros
}
```

### AuthServiceProvider

```php
boot() {
    // Define authorization policies
    // Define authorization gates for permissions
}
```

---

## 6. Database Migrations

### Migration File Naming Convention
```
YYYY_MM_DD_HHMMSS_action_description.php

Examples:
2025_01_01_000000_create_users_table.php
2025_01_01_000001_create_farms_table.php
2025_01_01_000002_create_batches_table.php
...
```

### Migration Order
1. Users & Roles (foundation)
2. Farms, Barns, Pens (infrastructure)
3. Batches (batch management)
4. Entry/Dairy records (daily operations)
5. Costs & Payments (financial)
6. Sales (revenue)
7. Treatments (health)
8. Inventory (storehouse)
9. Notifications (system)

### Running Migrations
```bash
# Run all pending migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Rollback all
php artisan migrate:reset

# Refresh (reset + seed)
php artisan migrate:refresh --seed
```

---

## 7. Controllers Organization

### Structure
```
Http/Controllers/
├── Controller.php              # Base controller
├── AuthController.php          # Auth routes
├── DashboardController.php     # Dashboard/home
│
├── FarmController.php          # Farm CRUD
├── BarnController.php          # Barn CRUD
├── PenController.php           # Pen CRUD
│
├── BatchController.php         # Batch operations
├── BatchMetricController.php   # KPI metrics
├── BatchPenController.php      # Allocations
├── BatchTreatmentController.php # Treatments
│
├── DairyRecordController.php   # Daily operations
├── PigEntryRecordController.php # Pig entries
│
├── PigSaleController.php       # Sales
├── CustomerController.php      # Customer mgmt
│
├── CostController.php          # Cost recording
├── PaymentController.php       # Payment recording
├── PaymentApprovalController.php # Admin approvals
│
├── StoreHouseController.php    # Inventory items
├── InventoryMovementController.php # Movements
│
├── NotificationController.php  # Notifications
├── UserManagementController.php # Admin user mgmt
│
├── ReportController.php        # Reports/exports
└── API/
    └── [API Controllers]       # API endpoints
```

---

## 8. Models & Relationships

### Model Location
```
app/Models/

User.php
  ├── has_many: roles, notifications
  ├── belongs_to_many: permissions
  └── relationships: farms

Farm.php
  ├── has_many: batches, barns, pens, costs
  ├── belongs_to: user
  └── relationships: inventory

Batch.php
  ├── has_many: entries, dairy_records, costs, sales
  ├── has_one: batch_metric, profit
  ├── belongs_to_many: pens (via allocation)
  └── relationships: treatments

Cost.php
  ├── has_one: cost_payment
  ├── belongs_to: batch, farm
  └── observer: CostObserver

PigSale.php
  ├── has_many: sale_details, payments
  ├── has_one: profit
  ├── belongs_to: batch, farm, customer
  └── relationships: notifications

[32+ total models]
```

---

## 9. Artisan Commands

### Built-in Commands Used

```bash
# Database
php artisan migrate                    # Run migrations
php artisan migrate:rollback           # Rollback migrations
php artisan migrate:refresh --seed     # Fresh database with seeds
php artisan seed:refresh              # Run seeders

# Cache Management
php artisan cache:clear               # Clear all cache
php artisan config:cache              # Cache config
php artisan view:clear                # Clear view cache
php artisan route:cache               # Cache routes

# Development
php artisan tinker                    # Interactive shell
php artisan serve                     # Dev server

# Production
php artisan config:cache              # Production config cache
php artisan route:cache               # Production route cache
php artisan optimize                  # Optimization

# Testing
php artisan test                      # Run test suite
php artisan test --filter=TestClass   # Specific test
```

### Custom Commands (if any)
```
app/Console/Commands/

(Can be used for batch operations, data import, etc.)
```

---

## 10. File Organization Best Practices

### Controllers
- One controller per major feature
- Keep controllers lean (business logic in services)
- Use dependency injection

### Models
- One model per database table
- Put relationships in model
- Use model factories & seeders for testing

### Services
- One service per major operation
- Service = collection of related business logic
- Inject into controller or other service

### Helpers
- Utility functions used across services
- Static methods for stateless operations
- Related functions in same helper class

### Views
- Organized by feature/module
- Use view components for reusable UI
- Blade templating with proper escaping

### Database
- One migration per table/change
- Use timestamps for tracking
- Add soft deletes where needed

---

## 11. Security Considerations

### Configuration
```env
APP_DEBUG=false              # NEVER true in production
APP_KEY=base64:...          # Random, unique per environment
HTTPS_REDIRECT=true         # Force HTTPS

DB_PASSWORD=secure          # Strong database password
MAIL_PASSWORD=...           # Use app-specific passwords
```

### Middleware Protection
- CSRF protection on all POST/PUT/DELETE
- Session validation
- Authentication checks
- Permission-based authorization

### Database Security
- Prepared statements (Eloquent automatic)
- Input validation on all user input
- SQL injection prevention

### File Upload Security
- Type validation
- Size limits
- Cloudinary storage (not local disk)

---

## 12. Development Workflow

### Local Setup
```bash
# Clone repository
git clone <repo>
cd PigFarmManagementSystem

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan seed:DatabaseSeeder

# Run dev server
php artisan serve
npm run dev
```

### Deployment Steps
```bash
# 1. Environment setup
cp .env.example .env
# Edit .env with production values

# 2. Dependencies
composer install --optimize-autoloader --no-dev
npm ci

# 3. Build assets
npm run build

# 4. Database
php artisan migrate --force

# 5. Cache
php artisan config:cache
php artisan route:cache

# 6. File permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

---

## 13. Backup & Recovery

### Database Backup
```bash
# Manual backup
mysqldump -u user -p database_name > backup.sql

# Restore
mysql -u user -p database_name < backup.sql
```

### File Backup
- /storage/app/ (user uploads)
- /config/ (configuration)
- database backups

### Cloudinary
- Automatically stores all uploaded files
- Can be recovered from Cloudinary dashboard

---

## 14. Monitoring & Logging

### Log Files
```
storage/logs/

laravel-YYYY-MM-DD.log     # Application logs
```

### Log Levels
- debug
- info
- notice
- warning
- error
- critical
- alert
- emergency

### Query Logging (Debug)
```php
// In tinker or code
DB::enableQueryLog();
// ... your code ...
dd(DB::getQueryLog());
```

---

## 15. Performance Optimization

### Caching Strategy
- **Config Cache**: `php artisan config:cache`
- **Route Cache**: `php artisan route:cache`
- **Class Map**: `composer dump-autoload -o`

### Query Optimization
- Use eager loading: `with(['relation'])`
- Index frequently queried columns
- Avoid N+1 queries

### Database Indexes
```sql
CREATE INDEX idx_batch_farm ON batches(farm_id);
CREATE INDEX idx_cost_batch ON costs(batch_id);
CREATE UNIQUE INDEX uq_batch_code ON batches(batch_code);
```

---

**Last Updated:** November 8, 2025
**Version:** 1.0
