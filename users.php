<?php
require_once __DIR__ . '/config/bootstrap.php';
require_admin();

$db = db();
$result = $db->query("SELECT id, username, role FROM users ORDER BY id ASC");
$users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت کاربران</title>
    <link rel="stylesheet" href="assets/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="assets/css/users.css">
</head>
<body>

<div class="top-menu">
    <div class="menu-links">
        <a href="index.php">مدیریت رمزها</a>
        <a href="phonebook.php">دفترچه تلفن</a>
        <a href="users.php" id="usersMenu" class="admin-menu active">مدیریت کاربران</a>
    </div>
    <div class="user-part">
        <span id="currentUser">کاربر: <?= htmlspecialchars(current_username(), ENT_QUOTES, 'UTF-8') ?></span>
        <a href="logout.php" class="logout" id="logoutBtn">خروج</a>
    </div>
</div>

<div class="main-box">
    <div class="page-head">
        <div>
            <h2>مدیریت کاربران</h2>
            <p>ایجاد و مدیریت حساب‌های ورود سامانه</p>
        </div>
        <button type="button" class="btn add-btn" id="addUserBtn">+ افزودن کاربر</button>
    </div>

    <div class="table-responsive">
        <table class="table user-table">
            <thead>
                <tr>
                    <th>نام کاربری</th>
                    <th>نقش</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody id="userList">
                <?php foreach ($users as $u): ?>
                <tr data-id="<?= (int)$u['id'] ?>">
                    <td><?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="<?= $u['role'] === 'Admin' ? 'role-admin' : 'role-user' ?>"><?= htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td>
                        <button class="btn edit-btn">ویرایش</button>
                        <?php if ((int)$u['id'] !== current_user_id()): ?>
                        <button class="btn delete-btn">حذف</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content user-box">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">افزودن کاربر</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="username" class="form-label">نام کاربری</label>
                    <input type="text" class="form-control user-input" id="username" placeholder="مثلا ali" autocomplete="username">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">رمز عبور</label>
                    <input type="text" class="form-control user-input" id="password" placeholder="رمز عبور" autocomplete="new-password">
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">نقش</label>
                    <select id="role" class="form-control user-input">
                        <option value="User">User</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                <p class="user-note">حساب کاربر برای ورود به سامانه ایجاد می‌شود.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel-btn" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn save-user-btn" id="saveUserBtn">ذخیره</button>
            </div>
        </div>
    </div>
</div>

<script>window.APP = {csrf: <?= json_encode($csrf) ?>};</script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/users.js"></script>
</body>
</html>
