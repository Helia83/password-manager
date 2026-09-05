SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS password_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE password_manager;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'User',
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    CONSTRAINT chk_users_role CHECK (role IN ('Admin','User'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sites (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    url VARCHAR(500) NOT NULL,
    url_name VARCHAR(255) NOT NULL,
    account VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    last_modified_time DATETIME NULL,
    modified_by_user_id INT UNSIGNED NULL,
    PRIMARY KEY (id),
    KEY idx_sites_modified_by (modified_by_user_id),
    CONSTRAINT fk_sites_modified_by FOREIGN KEY (modified_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_history (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    site_id INT UNSIGNED NOT NULL,
    changed_by_user_id INT UNSIGNED NULL,
    old_password VARCHAR(255) NOT NULL,
    new_password VARCHAR(255) NOT NULL,
    changed_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_history_site (site_id),
    KEY idx_history_user (changed_by_user_id),
    CONSTRAINT fk_history_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_history_user FOREIGN KEY (changed_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contacts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    contact_name VARCHAR(255) NOT NULL,
    department VARCHAR(255) NULL,
    mobile VARCHAR(50) NOT NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (username,password,role)
SELECT 'admin','admin123','Admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username='admin');

INSERT INTO users (username,password,role)
SELECT 'sara','sara123','User'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username='sara');

INSERT INTO sites (url,url_name,account,password,last_modified_time,modified_by_user_id)
SELECT 'https://mail.google.com','Gmail','company@gmail.com','123456',NOW(),id FROM users WHERE username='admin'
AND NOT EXISTS (SELECT 1 FROM sites WHERE url_name='Gmail');
INSERT INTO sites (url,url_name,account,password,last_modified_time,modified_by_user_id)
SELECT 'https://github.com','GitHub','company-account','github123',NOW(),id FROM users WHERE username='admin'
AND NOT EXISTS (SELECT 1 FROM sites WHERE url_name='GitHub');
INSERT INTO sites (url,url_name,account,password,last_modified_time,modified_by_user_id)
SELECT 'https://instagram.com','Instagram','company_page','instagram987',NOW(),id FROM users WHERE username='admin'
AND NOT EXISTS (SELECT 1 FROM sites WHERE url_name='Instagram');
INSERT INTO sites (url,url_name,account,password,last_modified_time,modified_by_user_id)
SELECT 'https://web.telegram.org','Telegram','company_channel','telegram2026',NOW(),id FROM users WHERE username='sara'
AND NOT EXISTS (SELECT 1 FROM sites WHERE url_name='Telegram');
INSERT INTO sites (url,url_name,account,password,last_modified_time,modified_by_user_id)
SELECT 'https://trello.com','Trello','company-board','trello456',NOW(),id FROM users WHERE username='admin'
AND NOT EXISTS (SELECT 1 FROM sites WHERE url_name='Trello');

-- یک رکورد تاریخچهٔ نمونه برای Gmail، تا کارکرد بخش «تاریخچه رمز» از همان ابتدا قابل مشاهده باشد.
INSERT INTO password_history (site_id,changed_by_user_id,old_password,new_password,changed_at)
SELECT s.id, u.id, 'CompanyPass2023', s.password, DATE_SUB(NOW(), INTERVAL 30 DAY)
FROM sites s, users u
WHERE s.url_name='Gmail' AND u.username='admin'
AND NOT EXISTS (SELECT 1 FROM password_history h WHERE h.site_id = s.id);

INSERT INTO contacts (contact_name,department,mobile,phone,email)
SELECT 'رضا احمدی','فناوری اطلاعات','09121234567','02112345678','reza.ahmadi@example.com'
WHERE NOT EXISTS (SELECT 1 FROM contacts WHERE contact_name='رضا احمدی');
INSERT INTO contacts (contact_name,department,mobile,phone,email)
SELECT 'سارا محمدی','مالی و اداری','09359876543','02166778899','sara.mohammadi@example.com'
WHERE NOT EXISTS (SELECT 1 FROM contacts WHERE contact_name='سارا محمدی');
INSERT INTO contacts (contact_name,department,mobile,phone,email)
SELECT 'علی کریمی','پشتیبانی فنی','09123334455',NULL,'ali.karimi@example.com'
WHERE NOT EXISTS (SELECT 1 FROM contacts WHERE contact_name='علی کریمی');
