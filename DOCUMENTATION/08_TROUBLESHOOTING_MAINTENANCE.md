# Pig Farm Management System - Troubleshooting & Maintenance Guide

## 1. Common Issues & Solutions

### 1.1 Null Reference Errors

#### Issue: "Attempt to read property 'X' on null"
```
ErrorException: Attempt to read property 'batch_code' on null
in pig_sales/index.blade.php line 239
```

**Cause:**
- Relationship returns null when foreign key is null
- Template tries to access property without checking

**Solution:**
```blade
❌ WRONG:
{{ $sale->batch->batch_code }}

✅ CORRECT (Safe Navigation):
{{ $sale->batch?->batch_code ?? '-' }}
```

**Files Fixed (Phase 17):**
- ✅ pig_sales/index.blade.php (lines 239, 452)
- ✅ inventory_movements/index.blade.php (lines 199, 303)
- ✅ payment_approvals/index.blade.php (lines 122, 169, 295, 423, 466)
- ✅ payment_approvals/detail.blade.php (lines 45, 73, 74)
- ✅ pig_entry_records/index.blade.php (line 224)

#### Prevention:
```php
// In views, always use safe navigation:
{{ $object?->property ?? 'default' }}

// In controllers, eager load:
$sales = PigSale::with(['batch'])->get();

// In models, use accessors:
protected $appends = ['batch_code_display'];

public function getBatchCodeDisplayAttribute() {
    return $this->batch?->batch_code ?? '-';
}
```

---

### 1.2 Database Connection Issues

#### Issue: "SQLSTATE[HY000]: General error: 2006 MySQL server has gone away"

**Cause:**
- MySQL connection timeout
- Server restart/restart
- Connection pool exhausted

**Solution:**
```php
// config/database.php
'mysql' => [
    // ... other config ...
    'sticky' => true,
    'options' => [
        PDO::ATTR_TIMEOUT => 5,
    ],
],
```

**Or in .env:**
```env
DB_CONNECTION=mysql
DB_MAX_CONNECTIONS=100
DB_WAIT_TIMEOUT=28800
```

**Quick Fix:**
```bash
php artisan cache:clear
php artisan config:clear
```

---

### 1.3 View Cache Issues

#### Issue: Changes to views not appearing in browser

**Cause:**
- Blade view cache is stale
- Assets not rebuilt

**Solution:**
```bash
# Clear view cache
php artisan view:clear

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Full reset
php artisan cache:clear && \
php artisan config:clear && \
php artisan route:clear && \
php artisan view:clear
```

**Browser Cache (JavaScript):**
```
Ctrl+Shift+Delete (open DevTools)
→ Settings → Network → Disable cache
→ Hard refresh: Ctrl+Shift+R
```

---

### 1.4 File Upload Failures

#### Issue: "Failed to upload file to Cloudinary"

**Cause:**
- Cloudinary credentials incorrect
- File too large
- Invalid file type
- Network timeout

**Debug Steps:**
```php
// In controller or tinker
dd(env('CLOUDINARY_NAME'));  // Check credentials
dd(env('CLOUDINARY_KEY'));
dd(env('CLOUDINARY_SECRET'));

// Test upload
$url = \Cloudinary\Uploader::upload($filePath);
```

**Solution:**
```env
# Verify in .env
CLOUDINARY_NAME=your_cloud_name
CLOUDINARY_KEY=your_api_key
CLOUDINARY_SECRET=your_api_secret
```

**File Size Limits:**
- Receipts: 5MB max
- Invoices: 10MB max
- Images: 2MB max

---

### 1.5 Permission Denied Errors

#### Issue: "Permission denied" when accessing file/directory

**Cause:**
- File permissions incorrect
- Wrong owner/group
- Laravel cannot write to storage

**Solution:**
```bash
# Fix storage permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/

# Or for development
chmod -R 777 storage/
chmod -R 777 bootstrap/cache/
```

---

### 1.6 Batch Not Found in Selection

#### Issue: Batch doesn't appear in dropdown when creating expense

**Common Causes:**
1. Batch status is 'archived' or 'completed'
2. Batch belongs to different farm
3. Batch date is in past/future

**Debug:**
```php
// Check batch status
$batch = Batch::find($batch_id);
dd($batch);  // Check: farm_id, status, created_at

// Check farm
dd($batch->farm()->get());

// Get available batches
$batches = Batch::where('farm_id', $farm_id)
    ->where('status', '!=', 'archived')
    ->get();
```

**Solution:**
- Check batch status (must be 'active' or 'ongoing')
- Verify batch belongs to correct farm
- Check batch date range

---

### 1.7 Cost Not Auto-Approved

#### Issue: Cost created but still waiting for approval (should be auto-approved)

**Cause:**
- Cost type is 'piglet' (requires manual approval)
- Observer not triggered
- CostPayment upload failed

**Affected Cost Types:**
```php
// Auto-approved (7 types):
'feed', 'medicine', 'wage', 'electric_bill', 
'water_bill', 'other', 'shipping'

// Manual approval required (1 type):
'piglet'
```

**Debug in CostObserver:**
```php
// Check if observer is registered
// app/Providers/AppServiceProvider.php
Cost::observe(CostObserver::class);

// Check cost type
$cost = Cost::find($id);
dd($cost->cost_type);  // Should be in auto-approve list
```

**Solution:**
```php
// Manually trigger observer
$cost = Cost::find($id);
event(new Illumin ate\Database\Events\ModelCreated($cost));

// Or run job manually
PaymentService::recordCostPayment($cost);
```

---

### 1.8 Profit Calculation Issues

#### Issue: Profit not updating after cost/sale changes

**Cause:**
- Observer not triggered
- RevenueHelper error
- Batch has no profit record

**Debug:**
```php
// Check profit record exists
$profit = Profit::where('batch_id', $batch_id)->first();
if (!$profit) {
    dd("Profit record missing for batch");
}

// Check if batch is completed
$batch = Batch::find($batch_id);
dd($batch->status);  // Should have profit calculation

// Manual recalculation
RevenueHelper::calculateAndRecordProfit($batch);
```

**Solution:**
```php
// In tinker or command
$batch = Batch::find($batch_id);
RevenueHelper::calculateAndRecordProfit($batch);

// Verify result
$profit = Profit::where('batch_id', $batch_id)->first();
dd($profit);
```

---

### 1.9 Email Not Sending

#### Issue: Notification emails not received

**Cause:**
- SMTP configuration incorrect
- Email address invalid
- Mail service not running

**Debug:**
```php
// Check email config
config('mail');  // Verify MAIL_MAILER, MAIL_HOST, etc.

// Test send
\Mail::raw('Test email', function($message) {
    $message->to('test@example.com')->subject('Test');
});

// Check logs
tail storage/logs/laravel-*.log
```

**Solution:**
```env
# Verify SMTP settings
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=app_specific_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@pigfarm.com
```

**Test with Laravel Mailtrap:**
```bash
composer require symfony/var-dumper
php artisan tinker
>>> Mail::to('test@example.com')->send(new App\Mail\ResetPasswordMail());
```

---

### 1.10 Pagination Not Working

#### Issue: "Call to undefined method Illuminate\Database\Eloquent\Builder::paginate()"

**Cause:**
- Not using paginate() method correctly
- Custom query builder without paginate support
- Collection instead of query builder

**Wrong Usage:**
```php
❌ $items = Model::all()->paginate(15);
   // all() returns collection, not query builder
```

**Correct Usage:**
```php
✅ $items = Model::paginate(15);
✅ $items = Model::where('status', 'active')->paginate(15);
✅ $items = Model::with(['relation'])->paginate(15);
```

---

---

## 2. Performance Issues

### 2.1 Slow Dashboard Load

**Cause:**
- N+1 query problems
- Missing indexes
- Large dataset queries

**Debug:**
```php
// Enable query logging
DB::enableQueryLog();

// Load dashboard
// ... controller code ...

// Check queries
dd(DB::getQueryLog());  // Should not have duplicate queries
```

**Solution:**
```php
// In DashboardController
$batches = Batch::with([
    'batch_metric',
    'profit',
    'costs',
    'sales'
])->get();

// Add indexes
ALTER TABLE batches ADD INDEX idx_farm_id (farm_id);
ALTER TABLE costs ADD INDEX idx_batch_id (batch_id);
ALTER TABLE profits ADD INDEX idx_batch_id (batch_id);
```

### 2.2 Large CSV Export Slow

**Cause:**
- Loading all data into memory
- Processing each row individually

**Solution:**
```php
// Use chunking
foreach (PigSale::chunk(500) as $sales) {
    foreach ($sales as $sale) {
        // Process
    }
}

// Or use lazy collection
PigSale::lazy()->each(function($sale) {
    // Process
});
```

### 2.3 Batch Restore Slow

**Cause:**
- Restoring many records at once
- Cascading updates

**Solution:**
```php
// Batch update instead of loop
Batch::whereIn('id', $ids)->restore();

// Or queue the job
dispatch(new RestoreBatchesJob($ids));
```

---

## 3. Data Issues

### 3.1 Duplicate Data

#### Issue: Duplicate batch codes or pig sales

**Debug:**
```php
// Find duplicates
$duplicates = Batch::selectRaw('batch_code, COUNT(*) as count')
    ->groupBy('batch_code')
    ->havingRaw('count > 1')
    ->pluck('batch_code');

dd($duplicates);
```

**Solution:**
```php
// Delete duplicates (keep earliest)
$batches = Batch::where('batch_code', $code)
    ->orderBy('created_at')
    ->skip(1)
    ->delete();
```

### 3.2 Orphaned Records

#### Issue: Records with deleted parent (FK constraint)

**Debug:**
```php
// Find orphaned costs
$orphaned = Cost::whereNotIn('batch_id', 
    Batch::pluck('id')
)->get();

dd($orphaned);
```

**Solution:**
```php
// Delete orphaned records
Cost::whereNotIn('batch_id', Batch::pluck('id'))
    ->delete();

// Or enable cascading delete in migration
$table->foreign('batch_id')
    ->references('id')
    ->on('batches')
    ->onDelete('cascade');
```

### 3.3 Inconsistent KPI Values

#### Issue: Batch metric doesn't match calculated value

**Verify Calculation:**
```php
$batch = Batch::with('batch_metric', 'costs', 'sales')->find($id);

// Expected values
$expected_mortality = $batch->costs->where('cost_type', 'pig_death')->sum('quantity');
$expected_revenue = $batch->sales->sum('total_revenue');

// Actual values
$actual = $batch->batch_metric;

// Compare
if ($actual->pig_death !== $expected_mortality) {
    dd("Mortality mismatch");
}
```

**Recalculate:**
```php
RevenueHelper::calculateKPIMetrics($batch);
```

---

## 4. Security Issues

### 4.1 SQL Injection Prevention

**Always Use Parameterized Queries:**
```php
❌ User::where('email', $email)->first();
   // Safe but example

❌ DB::raw("SELECT * FROM users WHERE email = '$email'");
   // DANGEROUS - SQL injection vulnerability

✅ DB::table('users')->where('email', $email)->first();
✅ User::whereEmail($email)->first();
```

### 4.2 Unauthorized Access Prevention

**Check Permissions in Controller:**
```php
// Check if user owns the farm
if (auth()->user()->farm_id !== $farm_id) {
    abort(403, 'Unauthorized');
}

// Or use authorization policies
$this->authorize('view', $batch);
```

### 4.3 CSRF Protection

**All Forms Must Include CSRF Token:**
```blade
<form method="POST" action="/batch">
    @csrf
    <!-- form fields -->
</form>
```

### 4.4 File Upload Security

**Validate File Type & Size:**
```php
$request->validate([
    'receipt' => 'required|file|mimes:pdf,jpg,png|max:5120',
    'invoice' => 'required|file|mimes:pdf|max:10240',
]);
```

---

## 5. Deployment Issues

### 5.1 500 Error After Deployment

**Debug:**
```bash
# Check error logs
tail -f storage/logs/laravel-*.log

# Check permissions
ls -la storage/
ls -la bootstrap/cache/

# Check database connection
php artisan migrate --step --dry-run
```

**Solution:**
```bash
# Set permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild autoloader
composer dump-autoload

# Run migrations
php artisan migrate
```

### 5.2 Assets Not Loading (CSS/JS)

**Cause:**
- Assets not built for production
- Wrong URL paths
- Cache busting issues

**Solution:**
```bash
# Build assets for production
npm run build

# Verify APP_URL in .env
APP_URL=https://yourdomain.com

# Clear cache
php artisan cache:clear
php artisan config:clear

# Check asset mix manifest
ls -la public/mix-manifest.json
```

### 5.3 HTTPS/SSL Issues

**Redirect HTTP to HTTPS:**
```env
# In .env or config/app.php
APP_URL=https://yourdomain.com
FORCE_HTTPS=true
```

**Apache:**
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

---

## 6. Backup & Recovery

### 6.1 Database Backup

```bash
# Manual backup
mysqldump -u user -p -h host pig_farm_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup with structure and data
mysqldump -u user -p -h host \
    --single-transaction \
    --routines \
    --triggers \
    pig_farm_db > backup.sql

# Compressed backup
mysqldump -u user -p pig_farm_db | gzip > backup_$(date +%Y%m%d).sql.gz
```

### 6.2 Database Restore

```bash
# Restore from backup
mysql -u user -p pig_farm_db < backup.sql

# Restore from compressed backup
gunzip < backup.sql.gz | mysql -u user -p pig_farm_db

# Restore specific table
mysql -u user -p pig_farm_db < backup.sql --tables batches
```

### 6.3 File Backup

```bash
# Backup entire project
tar -czf pigfarm_backup_$(date +%Y%m%d).tar.gz /var/www/pigfarm/

# Backup storage only
tar -czf storage_backup_$(date +%Y%m%d).tar.gz /var/www/pigfarm/storage/

# Backup .env (keep secure)
cp .env .env.backup.secure
```

### 6.4 Automated Backup (Cron Job)

```bash
# Add to crontab
crontab -e

# Daily backup at 2 AM
0 2 * * * mysqldump -u user -p pig_farm_db | gzip > /backups/db_$(date +\%Y\%m\%d).sql.gz

# Weekly full backup
0 3 * * 0 tar -czf /backups/full_$(date +\%Y\%m\%d).tar.gz /var/www/pigfarm/
```

---

## 7. Monitoring & Health Checks

### 7.1 Application Health Check

```php
// Add route for monitoring
Route::get('/health', function() {
    return [
        'status' => 'ok',
        'database' => DB::connection()->getDatabaseName(),
        'timestamp' => now(),
    ];
});
```

### 7.2 Database Health

```php
// Check connection
try {
    DB::connection()->getPdo();
    echo "Database connected";
} catch (\Exception $e) {
    echo "Database connection failed: " . $e->getMessage();
}
```

### 7.3 File Permissions Check

```bash
# Check storage
find storage -type f ! -perm 0644 -exec chmod 644 {} \;
find storage -type d ! -perm 0755 -exec chmod 755 {} \;

# Check bootstrap cache
find bootstrap/cache -type f ! -perm 0644 -exec chmod 644 {} \;
```

### 7.4 Error Log Monitoring

```bash
# Watch error logs in real-time
tail -f storage/logs/laravel-*.log | grep -i error

# Count errors
grep -c error storage/logs/laravel-*.log

# Find recent errors
grep "error\|exception" storage/logs/laravel-*.log | tail -50
```

---

## 8. Maintenance Tasks

### 8.1 Regular Maintenance Schedule

**Daily:**
- Check error logs
- Verify backups created
- Monitor disk space

**Weekly:**
- Run database optimization
- Clear old logs
- Review failed jobs

**Monthly:**
- Database cleanup
- Test backup restoration
- Update dependencies
- Security review

### 8.2 Database Optimization

```bash
# Run optimization
php artisan tinker
>>> DB::statement('OPTIMIZE TABLE batches');
>>> DB::statement('OPTIMIZE TABLE costs');
>>> DB::statement('ANALYZE TABLE batches');
```

### 8.3 Clear Old Logs

```bash
# Delete logs older than 30 days
find storage/logs -name "*.log" -mtime +30 -delete

# Or use Laravel log channel
config/logging.php - set single channel max_files: 10
```

### 8.4 Update Dependencies

```bash
# Check for updates
composer outdated

# Update packages safely
composer update --safe-only

# Update npm packages
npm outdated
npm update
```

---

## 9. Common Command Reference

### Quick Fixes

```bash
# Clear everything
php artisan cache:clear && \
php artisan config:clear && \
php artisan route:clear && \
php artisan view:clear

# Optimize
composer dump-autoload -o
php artisan config:cache
php artisan route:cache

# Database
php artisan migrate
php artisan migrate:refresh --seed
php artisan db:seed --class=DatabaseSeeder

# Testing
php artisan test
php artisan test --filter=BatchTest
```

---

## 10. Support & Escalation

### When to Contact Support

1. **Database Corruption**
   - Cannot restore from backup
   - Referential integrity errors
   - Data inconsistency

2. **Security Breach**
   - Unauthorized access detected
   - File modification found
   - Credential compromise

3. **Performance Crisis**
   - Response time > 5 seconds
   - CPU usage > 80%
   - Disk space < 10%

4. **Data Loss**
   - Cannot restore from backup
   - Accidental deletion
   - Corruption unrecoverable

### Escalation Process

1. Document the issue
2. Check logs and errors
3. Try recovery procedures
4. Contact system administrator
5. Restore from backup if needed

---

**Last Updated:** November 8, 2025
**Version:** 1.0
