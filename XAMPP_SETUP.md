# XAMPP Setup Guide - دليل تشغيل المشروع

## ✅ All Files Updated!

The project has been updated to work properly with XAMPP. All CSS, JS, and navigation links now use the correct base URL.

## 🚀 Quick Start

### 1. Copy Project to XAMPP
```
C:\xampp\htdocs\online-bookstore\
```

### 2. Start XAMPP
- Open XAMPP Control Panel
- Start **Apache**
- Start **MySQL**

### 3. Create Database
1. Go to: `http://localhost/phpmyadmin`
2. Create database: `online_bookstore`
3. Collation: `utf8mb4_unicode_ci`
4. Import: `database/schema.sql`
5. Import: `database/sample_data.sql`

### 4. Access Website
```
http://localhost/online-bookstore/
```

## 🔧 Configuration

If you change the folder name or move to production, edit:
**`includes/config.php`** - Line 8:

```php
// For XAMPP in htdocs/online-bookstore:
define('BASE_URL', '/online-bookstore');

// For production (root domain):
define('BASE_URL', '');

// For different folder name:
define('BASE_URL', '/your-folder-name');
```

## 👤 Demo Accounts

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | password |
| Customer | mohammed | password |

## 📁 What Was Fixed

✅ CSS loading path  
✅ JavaScript loading path  
✅ All navigation links  
✅ Form actions  
✅ Image paths  
✅ Redirects after login/logout  
✅ Category links  
✅ Book detail links  

## 🔍 Troubleshooting

### CSS Not Loading?
1. Check folder is `C:\xampp\htdocs\online-bookstore\`
2. Check file exists: `C:\xampp\htdocs\online-bookstore\assets\css\style.css`
3. Clear browser cache (Ctrl+F5)
4. Check `includes/config.php` BASE_URL matches your folder name

### Database Error?
1. Make sure MySQL is running in XAMPP
2. Verify database `online_bookstore` exists
3. Check `includes/db.php` credentials (default: root / no password)

### 404 Errors?
1. Ensure Apache is running
2. Check BASE_URL in `includes/config.php`
3. Verify folder name matches BASE_URL

## 📞 Support

If issues persist:
1. Check `http://localhost/online-bookstore/diagnostics.php`
2. View browser console (F12) for JavaScript errors
3. Check Apache error logs in XAMPP

---

**Project is now ready to run! 🎉**
