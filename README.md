# سیستم مدیریت رمز عبور و دفترچه تلفن

نسخه PHP + MySQL پروژه، بر اساس UI موجود و Schema ارائه‌شده پیاده‌سازی شده است.

## ۱. طراحی UI

سامانه ۳ صفحهٔ اصلی (به‌علاوه صفحهٔ ورود) دارد که همگی از یک منوی بالای صفحه مشترک (مدیریت رمزها / دفترچه تلفن / مدیریت کاربران برای Admin) استفاده می‌کنند:

| صفحه | مسیر | شرح |
|---|---|---|
| ورود | `login.php` | فرم نام کاربری و رمز عبور |
| مدیریت رمزها (صفحهٔ اصلی) | `index.php` | لیست تک‌صفحه‌ای (بدون Pagination) تمام سایت‌ها با ستون‌های نام سایت/حساب، URL، رمز عبور، آخرین تغییر، آخرین‌تغییر‌توسط و عملیات (ویرایش/تاریخچه/حذف)؛ یک تکست‌باکس جستجوی سراسری بالای جدول که با Enter، بدون بارگذاری مجدد صفحه، بین تمام فیلدهای نمایش‌داده‌شده فیلتر می‌کند؛ افزودن/ویرایش سایت در یک Modal؛ تاریخچهٔ رمز هر سایت در یک Modal جدا |
| مدیریت کاربران (فقط Admin) | `users.php` | لیست کاربران سامانه با نقش (Admin/User) و افزودن/ویرایش/حذف در Modal |
| دفترچه تلفن (مد دوم/تب مستقل) | `phonebook.php` | همان الگوی صفحهٔ اصلی (لیست تک‌صفحه‌ای + جستجوی زندهٔ Enter) برای مخاطبین: نام، واحد، موبایل، تلفن ثابت، ایمیل |

هر دو مد (مدیریت رمز و دفترچه تلفن) به‌صورت صفحات/تب‌های جدا در همان منوی بالا پیاده‌سازی شده‌اند (نیازمندی امتیازی ۲).

## ۲. شمای دیتابیس (MySQL)

```
users              sites                      password_history          contacts
--------------     ---------------------      ---------------------     -----------------
id PK              id PK                      id PK                     id PK
username UNIQUE    url                        site_id FK -> sites.id    contact_name
password           url_name                   changed_by_user_id FK     department
role (Admin/User)  account                       -> users.id            mobile
                   password                    old_password              phone
                   last_modified_time          new_password              email
                   modified_by_user_id FK      changed_at
                     -> users.id
```

- `sites.modified_by_user_id` و `password_history.changed_by_user_id` به `users.id` ارجاع دارند (ON DELETE SET NULL) تا حذف یک کاربر، تاریخچهٔ رمزها را از بین نبرد.
- `password_history.site_id` با `ON DELETE CASCADE` به `sites.id` وصل است.
- رمزهای سایت‌ها و رمز ورود کاربران طبق نیازمندی پروژه رمزگذاری نمی‌شوند (بند ۹ نیازمندی‌ها).
- جزئیات کامل در `database.sql`.

## امکانات
- ورود کاربران با Session سمت سرور
- Session با طول عمر 24 ساعت
- نقش‌های Admin و User
- مدیریت کاربران توسط Admin
- مدیریت سایت‌ها برای همه کاربران واردشده
- ثبت زمان و کاربر آخرین تغییر رمز
- ثبت تاریخچه رمز شامل رمز قبلی، رمز جدید، کاربر و زمان
- حذف و ویرایش سایت
- جستجو در همان صفحه با Enter
- دفترچه تلفن در صفحه جداگانه/مد مستقل
- CRUD دفترچه تلفن
- timezone سامانه روی Asia/Tehran (UTC+03:30) تنظیم شده است
- تشخیص نقش Admin به‌صورت case-insensitive
- Prepared Statements و کنترل CSRF برای درخواست‌های تغییردهنده
- PHP + MySQL و Docker

## روش‌های نصب و اجرا

### روش ۱: با Docker (پیشنهادی)
در پوشه پروژه اجرا کنید:

```bash
docker compose up -d --build
```

سپس:
- سامانه: http://localhost:18080
- phpMyAdmin: http://localhost:18081
- Database host داخل Docker: `db`
- Database: `password_manager`
- Database user: `password_manager`
- Database password: `password_manager`

### روش ۲: بدون Docker (نصب مستقیم روی XAMPP / Laragon و مشابه آن)
```bash
# ۱. نصب PHP (نسخه ۸.۱ به بالا) و MySQL/MariaDB
# ۲. کلون پروژه
git clone <repo-url>
cd password-manager

# ۳. ساخت کاربر و دیتابیس (دقیقا با همان مقادیر پیش‌فرض config/config.php)
mysql -u root -p -e "
CREATE DATABASE password_manager;
CREATE USER 'password_manager'@'localhost' IDENTIFIED BY 'password_manager';
GRANT ALL PRIVILEGES ON password_manager.* TO 'password_manager'@'localhost';
FLUSH PRIVILEGES;"

# ۴. وارد کردن schema و داده‌های نمونه
mysql -u root -p password_manager < database.sql

# ۵. تنظیم Host دیتابیس در config/config.php
#    مقدار DB_HOST را از 'db' به 'localhost' تغییر دهید.
#    (اگر یوزر/پسورد دلخواه دیگری ساختید، DB_USER و DB_PASS را هم اصلاح کنید.)

# ۶. اجرا با سرور توکار PHP (یا کپی پوشه در htdocs/www ابزارهایی مثل XAMPP)
php -S localhost:8080
```
سپس مرورگر را روی `http://localhost:8080` باز کنید.

## ورود اولیه
- Admin — username: `admin` / password: `admin123`
- User (کاربر عادی نمونه) — username: `ali` / password: `123`

پس از ورود، از بخش مدیریت کاربران رمز و حساب‌های موردنیاز را تغییر دهید.

## اصلاح زمان‌های دیتابیس موجود
اگر این پروژه قبلاً با همین Volume اجرا شده و زمان رکوردهای قبلی ۳ ساعت و ۳۰ دقیقه عقب است، فایل `migrate_timezone.sql` را فقط یک بار روی همان دیتابیس اجرا کنید.

## نکته مهم درباره رمزها
طبق Requirement و Schema پروژه، رمزهای سایت‌ها به‌صورت رمزگذاری‌شده ذخیره نمی‌شوند. رمزهای سامانه کاربران نیز در این نسخه مطابق همین Requirement به‌صورت متن ذخیره شده‌اند.

## قابلیت Chrome Password Update
یک وب‌سایت PHP به‌تنهایی اجازه دسترسی مستقیم به مخزن رمزهای ذخیره‌شده Chrome را ندارد. بنابراین «گرفتن خودکار رمز جدید از Chrome و ثبت آن» بدون افزونه مرورگر یا ابزار بومی قابل پیاده‌سازی کامل نیست. فرم تغییر رمز پروژه برای autofill مرورگر آماده است، اما دسترسی مستقیم به Password Store Chrome عمداً ادعا نشده است.
