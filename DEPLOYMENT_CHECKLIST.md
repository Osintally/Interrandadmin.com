# Corporate Memos Deployment Checklist

## Pre-Deployment Requirements

### 1. Environment Setup
- [ ] PHP 8.1+ installed and configured
- [ ] Laravel application running successfully
- [ ] Database connection established
- [ ] File storage configured (local/S3/MinIO)
- [ ] Mail configuration for notifications

### 2. Dependencies
- [ ] Spatie ActivityLog package installed (`composer require spatie/laravel-activitylog`)
- [ ] TinyMCE editor assets available in public directory
- [ ] Select2 library loaded in AdminLTE layout
- [ ] jQuery and Bootstrap dependencies available

### 3. Database Migration
```bash
# Run the new memo migrations
php artisan migrate --path=Modules/Essentials/Database/Migrations

# Verify tables were created
php artisan tinker
>>> Schema::hasTable('essentials_memos')
>>> Schema::hasTable('essentials_memo_recipients') 
>>> Schema::hasTable('essentials_memo_attachments')
```

### 4. File Storage Configuration
- [ ] Configure storage disk in `config/filesystems.php`
- [ ] Set appropriate permissions for storage directories
- [ ] Test file upload functionality
- [ ] Configure virus scanning (ClamAV or cloud service)

### 5. Permissions & Security
- [ ] Verify user permissions for memo access
- [ ] Test file download access control
- [ ] Validate CSRF protection on forms
- [ ] Check file type validation and size limits

## Deployment Steps

### 1. Code Deployment
```bash
# Pull latest changes
git pull origin main

# Install/update dependencies
composer install --no-dev --optimize-autoloader

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 2. Database Updates
```bash
# Run migrations
php artisan migrate --force

# Seed sample data (optional)
php artisan db:seed --class=Modules\\Essentials\\Database\\Seeders\\MemoSeeder
```

### 3. Asset Compilation
```bash
# Compile frontend assets if needed
npm run production
```

### 4. Cache Optimization
```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Post-Deployment Verification

### 1. Functional Testing
- [ ] Navigate to `/essentials/memos` - page loads without errors
- [ ] Click "Compose Memo" - modal opens with all fields
- [ ] Select recipients using To/CC/BCC dropdowns
- [ ] Rich text editor (TinyMCE) loads and functions
- [ ] File upload works and shows preview
- [ ] Save draft functionality works
- [ ] Send memo triggers notifications
- [ ] Recipients can view memo and download attachments
- [ ] Access control prevents unauthorized access

### 2. Security Verification
- [ ] File upload validates allowed types only
- [ ] File size limits enforced (50MB default)
- [ ] Unauthorized users get 403 when accessing memos
- [ ] File downloads require proper authentication
- [ ] XSS protection in rich text content
- [ ] CSRF tokens present on forms

### 3. Performance Testing
- [ ] Page load times acceptable (<3 seconds)
- [ ] File upload progress indicators work
- [ ] Large file uploads don't timeout
- [ ] Database queries optimized (check query log)

## Rollback Plan

If issues occur after deployment:

```bash
# Rollback database migrations
php artisan migrate:rollback --step=3

# Revert to previous code version
git checkout previous-stable-commit

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Environment Variables

Add these to your `.env` file:

```env
# File Storage
FILESYSTEM_DISK=local
# or for S3:
# FILESYSTEM_DISK=s3
# AWS_ACCESS_KEY_ID=your_key
# AWS_SECRET_ACCESS_KEY=your_secret
# AWS_DEFAULT_REGION=us-east-1
# AWS_BUCKET=your_bucket

# File Upload Limits
MAX_FILE_SIZE=52428800  # 50MB in bytes
ALLOWED_FILE_TYPES=pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt

# Virus Scanning (if enabled)
VIRUS_SCAN_ENABLED=false
CLAMAV_SOCKET=/var/run/clamav/clamd.ctl
```

## Monitoring & Maintenance

### 1. Log Monitoring
- Monitor Laravel logs for memo-related errors
- Check file upload failures
- Monitor notification delivery

### 2. Database Maintenance
- Regular cleanup of old attachments
- Archive old memos based on retention policy
- Monitor database size growth

### 3. Storage Maintenance
- Clean up orphaned attachment files
- Monitor storage usage
- Backup attachment files regularly

## Support Contacts

- **Developer**: @Osintally (analytictosin@gmail.com)
- **Devin Session**: https://app.devin.ai/sessions/7ad735410c6f4eb294e679c79fa6f685
- **Repository**: https://github.com/Osintally/Interrandadmin.com
