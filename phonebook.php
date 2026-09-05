<?php
require_once __DIR__ . '/config/bootstrap.php';
require_login();

$db = db();
$result = $db->query("SELECT id, contact_name, department, mobile, phone, email FROM contacts ORDER BY id ASC");
$contacts = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دفترچه تلفن</title>
    <link rel="stylesheet" href="assets/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="assets/css/phonebook.css">
</head>
<body>

<div class="top-menu">
    <div class="menu-links">
        <a href="index.php">مدیریت رمزها</a>
        <a href="phonebook.php" class="active">دفترچه تلفن</a>
        <?php if (current_role() === 'Admin'): ?>
        <a href="users.php" id="usersMenu" class="admin-menu">مدیریت کاربران</a>
        <?php endif; ?>
    </div>
    <div class="user-part">
        <span id="currentUser">کاربر: <?= htmlspecialchars(current_username(), ENT_QUOTES, 'UTF-8') ?></span>
        <a href="logout.php" class="logout" id="logoutBtn">خروج</a>
    </div>
</div>

<div class="main-box">
    <div class="page-head">
        <div>
            <h2>دفترچه تلفن</h2>
            <p>لیست شماره‌ها و اطلاعات تماس</p>
        </div>
        <button type="button" class="btn add-btn" id="addContactBtn">+ افزودن مخاطب</button>
    </div>

    <div class="search-box">
        <input type="text" id="searchContact" class="form-control search-input" placeholder="جستجو در همه اطلاعات و زدن Enter...">
    </div>

    <div class="table-responsive">
        <table class="table contact-table">
            <thead>
                <tr>
                    <th>نام مخاطب</th>
                    <th>واحد / اداره</th>
                    <th>موبایل</th>
                    <th>تلفن ثابت</th>
                    <th>ایمیل</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody id="contactList">
                <?php foreach ($contacts as $c): ?>
                <tr data-id="<?= (int)$c['id'] ?>">
                    <td><?= htmlspecialchars($c['contact_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($c['department'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($c['mobile'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($c['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($c['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><button class="btn edit-btn">ویرایش</button><button class="btn delete-btn">حذف</button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="no-result" id="noContact" style="display:none">موردی پیدا نشد.</p>
</div>

<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content contact-box">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalTitle">افزودن مخاطب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="contactName" class="form-label">نام مخاطب</label>
                    <input type="text" id="contactName" class="form-control contact-input" placeholder="نام مخاطب را وارد کنید">
                </div>
                <div class="mb-3">
                    <label for="department" class="form-label">واحد / اداره</label>
                    <input type="text" id="department" class="form-control contact-input" placeholder="واحد یا اداره">
                </div>
                <div class="mb-3">
                    <label for="mobile" class="form-label">شماره موبایل</label>
                    <input type="text" id="mobile" class="form-control contact-input" placeholder="شماره موبایل">
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">شماره تلفن</label>
                    <input type="text" id="phone" class="form-control contact-input" placeholder="شماره تلفن ثابت">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">ایمیل</label>
                    <input type="email" id="email" class="form-control contact-input" placeholder="ایمیل">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel-btn" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn save-btn" id="saveContactBtn">ذخیره</button>
            </div>
        </div>
    </div>
</div>

<script>window.APP = {csrf: <?= json_encode($csrf) ?>};</script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/phonebook.js"></script>
</body>
</html>
