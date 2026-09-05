<?php
declare(strict_types=1);

const DB_HOST = 'db';
const DB_NAME = 'password_manager';
const DB_USER = 'password_manager';
const DB_PASS = 'password_manager';

const SESSION_LIFETIME = 86400; // 24 hours

function db(): mysqli {
    static $db = null;
    if ($db instanceof mysqli) return $db;
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_errno) {
        http_response_code(500);
        exit('خطا در اتصال به پایگاه داده.');
    }
    $db->set_charset('utf8mb4');
    // Keep MySQL-generated DATETIME values aligned with the application timezone.
    $db->query("SET time_zone = '+03:30'");
    return $db;
}
