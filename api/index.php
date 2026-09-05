<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/bootstrap.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'login') {
    verify_csrf();

    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        json_response(['ok' => false, 'message' => 'نام کاربری و رمز عبور را وارد کنید.'], 422);
    }

    $stmt = db()->prepare('SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || !hash_equals((string)$user['password'], $password)) {
        json_response(['ok' => false, 'message' => 'نام کاربری یا رمز عبور نادرست است.'], 401);
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['login_at'] = time();
    $_SESSION['csrf'] = bin2hex(random_bytes(32));

    json_response(['ok' => true, 'redirect' => 'index.php']);
}

require_login();
$db = db();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    verify_csrf();
}

if ($action === 'site_save') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim((string)($_POST['url_name'] ?? ''));
    $url = trim((string)($_POST['url'] ?? ''));
    $account = trim((string)($_POST['account'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($name === '' || $url === '' || $account === '' || $password === '') {
        json_response(['ok' => false, 'message' => 'لطفاً همه اطلاعات را وارد کنید.'], 422);
    }

    if ($id > 0) {
        $stmt = $db->prepare('SELECT password FROM sites WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();

        if (!$old) {
            json_response(['ok' => false, 'message' => 'رکورد پیدا نشد.'], 404);
        }

        if ((string)$old['password'] !== $password) {
            $stmt = $db->prepare('INSERT INTO password_history(site_id,changed_by_user_id,old_password,new_password,changed_at) VALUES(?,?,?,?,NOW())');
            $uid = current_user_id();
            $stmt->bind_param('iiss', $id, $uid, $old['password'], $password);
            $stmt->execute();
        }

        $stmt = $db->prepare('UPDATE sites SET url=?,url_name=?,account=?,password=?,last_modified_time=NOW(),modified_by_user_id=? WHERE id=?');
        $uid = current_user_id();
        $stmt->bind_param('ssssii', $url, $name, $account, $password, $uid, $id);
        $stmt->execute();

        json_response(['ok' => true, 'message' => 'سایت با موفقیت ویرایش شد.', 'id' => $id]);
    }

    $stmt = $db->prepare('INSERT INTO sites(url,url_name,account,password,last_modified_time,modified_by_user_id) VALUES(?,?,?,?,NOW(),?)');
    $uid = current_user_id();
    $stmt->bind_param('ssssi', $url, $name, $account, $password, $uid);
    $stmt->execute();

    json_response([
        'ok' => true,
        'message' => 'سایت اضافه شد.',
        'id' => $stmt->insert_id,
        'date' => date('Y/m/d - H:i'),
        'user' => current_username(),
    ]);
}

if ($action === 'site_delete') {
    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        json_response(['ok' => false, 'message' => 'شناسه نامعتبر است.'], 422);
    }

    $stmt = $db->prepare('DELETE FROM sites WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    json_response(['ok' => true, 'message' => 'سایت حذف شد.']);
}

if ($action === 'site_history') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare(
        "SELECT h.changed_at, h.old_password, h.new_password, COALESCE(u.username,'-') username
         FROM password_history h
         LEFT JOIN users u ON u.id = h.changed_by_user_id
         WHERE h.site_id = ?
         ORDER BY h.id DESC"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();

    json_response(['ok' => true, 'items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
}

if ($action === 'contact_save') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim((string)($_POST['contact_name'] ?? ''));
    $dept = trim((string)($_POST['department'] ?? ''));
    $mobile = trim((string)($_POST['mobile'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));

    if ($name === '' || $mobile === '') {
        json_response(['ok' => false, 'message' => 'نام مخاطب و شماره موبایل الزامی است.'], 422);
    }

    if ($id > 0) {
        $stmt = $db->prepare('UPDATE contacts SET contact_name=?,department=?,mobile=?,phone=?,email=? WHERE id=?');
        $stmt->bind_param('sssssi', $name, $dept, $mobile, $phone, $email, $id);
        $stmt->execute();
        json_response(['ok' => true, 'id' => $id]);
    }

    $stmt = $db->prepare('INSERT INTO contacts(contact_name,department,mobile,phone,email) VALUES(?,?,?,?,?)');
    $stmt->bind_param('sssss', $name, $dept, $mobile, $phone, $email);
    $stmt->execute();

    json_response(['ok' => true, 'id' => $stmt->insert_id]);
}

if ($action === 'contact_delete') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $db->prepare('DELETE FROM contacts WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    json_response(['ok' => true]);
}

if ($action === 'user_get') {
    require_admin();

    $id = (int)($_POST['id'] ?? 0);
    $stmt = $db->prepare('SELECT password FROM users WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    json_response(['ok' => true, 'password' => $row['password'] ?? '']);
}

if ($action === 'user_save') {
    require_admin();

    $id = (int)($_POST['id'] ?? 0);
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $role = (string)($_POST['role'] ?? 'User');

    if ($username === '') {
        json_response(['ok' => false, 'message' => 'نام کاربری الزامی است.'], 422);
    }
    if (!in_array($role, ['Admin', 'User'], true)) {
        $role = 'User';
    }

    if ($id > 0) {
        if ($id === current_user_id() && $role !== 'Admin') {
            json_response(['ok' => false, 'message' => 'نقش حساب Admin فعلی را نمی‌توان به User تغییر داد.'], 422);
        }

        $stmt = $db->prepare('SELECT id, password FROM users WHERE username=? AND id<>?');
        $stmt->bind_param('si', $username, $id);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            json_response(['ok' => false, 'message' => 'این نام کاربری قبلاً استفاده شده است.'], 409);
        }

        if ($password === '') {
            $stmt = $db->prepare('UPDATE users SET username=?,role=? WHERE id=?');
            $stmt->bind_param('ssi', $username, $role, $id);
        } else {
            $stmt = $db->prepare('UPDATE users SET username=?,password=?,role=? WHERE id=?');
            $stmt->bind_param('sssi', $username, $password, $role, $id);
        }
        $stmt->execute();

        if ($id === current_user_id()) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
        }

        json_response(['ok' => true, 'id' => $id]);
    }

    if ($password === '') {
        json_response(['ok' => false, 'message' => 'برای کاربر جدید، رمز عبور الزامی است.'], 422);
    }

    $stmt = $db->prepare('SELECT id FROM users WHERE username=?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) {
        json_response(['ok' => false, 'message' => 'این نام کاربری قبلاً وجود دارد.'], 409);
    }

    $stmt = $db->prepare('INSERT INTO users(username,password,role) VALUES(?,?,?)');
    $stmt->bind_param('sss', $username, $password, $role);
    $stmt->execute();

    json_response(['ok' => true, 'id' => $stmt->insert_id]);
}

if ($action === 'user_delete') {
    require_admin();

    $id = (int)($_POST['id'] ?? 0);
    if ($id === current_user_id()) {
        json_response(['ok' => false, 'message' => 'حساب کاربری فعلی قابل حذف نیست.'], 422);
    }

    $stmt = $db->prepare('DELETE FROM users WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    json_response(['ok' => true]);
}

json_response(['ok' => false, 'message' => 'عملیات ناشناخته است.'], 404);
