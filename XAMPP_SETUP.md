# XAMPP Setup Guide - دليل تشغيل المشروع

## ✅ All Fixed - Auto-Detection Enabled!

The project now **automatically detects** the correct folder path. No manual configuration needed!

## 🚀 Quick Start

### 1. Copy Project to XAMPP
```
C:\xampp\htdocs\online-bookstore\
```
*(You can use any folder name - it will auto-detect!)*

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

**That's it!** The CSS, images, and all links will work automatically. ✨

## 👤 Demo Accounts

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | password |
| Customer | mohammed | password |

## 📁 What Was Fixed

✅ **Auto-detection** of folder path  
✅ CSS loading path  
✅ JavaScript loading path  
✅ All navigation links  
✅ Form actions  
✅ Redirects after login/logout  
✅ Category links  
✅ Book detail links  
✅ Admin panel navigation  
✅ Customer area navigation  

## 🔧 Advanced Configuration (Optional)

If you want to manually set the base URL, edit:
**`includes/config.php`** - Lines 18-19:

```php
// Auto mode (recommended):
define('BASE_URL', $baseFolder);

// Manual mode (for special cases):
// define('BASE_URL', '/online-bookstore');  // For XAMPP
// define('BASE_URL', '');  // For production root
// define('BASE_URL', '/bookshop');  // For custom folder name
```

## 🔍 Troubleshooting

### Still Getting 404 Errors?

**Clear your browser cache:**
- Press `Ctrl + Shift + Delete`
- Or try `Ctrl + F5` (hard refresh)

**Check Apache is running:**
- Look for green indicator next to Apache in XAMPP

**Verify folder structure:**
```
C:\xampp\htdocs\online-bookstore\
    ├── assets\
    │   ├── css\
    │   │   └── style.css
    │   └── js\
    │       └── main.js
    ├── includes\
    │   ├── config.php
    │   ├── db.php
    │   ├── auth.php
    │   └── header.php
    ├── index.php
    └── ...
```

### CSS Not Loading?

1. Check file exists: `C:\xampp\htdocs\online-bookstore\assets\css\style.css`
2. Open browser console (F12) → Network tab
3. Look for 404 errors on CSS file
4. Try accessing directly: `http://localhost/online-bookstore/assets/css/style.css`

### Database Connection Error?

1. Make sure MySQL is running (green in XAMPP)
2. Verify database `online_bookstore` exists
3. Default credentials: username=`root`, password=*(empty)*
4. Check diagnostics: `http://localhost/online-bookstore/diagnostics.php`

## 📞 Diagnostic Tools

**System Status:**
```
http://localhost/online-bookstore/diagnostics.php
```
Shows PHP version, database drivers, connection status

**Browser Console:**
- Press `F12` → Console tab
- Check for JavaScript errors

**Apache Error Log:**
- XAMPP Control Panel → Apache → Logs button

---

## 🎉 **Project Features**

- 📚 12 Sample Books
- 👥 5 Demo Users (1 Admin, 4 Customers)
- 🏢 5 Publishers
- 🛒 Shopping Cart System
- 📊 Admin Dashboard
- 📈 Sales Reports
- 🇸🇦 Full Arabic RTL Support
- 💳 Credit Card Validation
- 📦 Order Management
- 🔐 Secure Authentication

---

**Everything is configured and ready to run! 🚀**

