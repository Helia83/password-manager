<?php
require_once __DIR__ . '/config/bootstrap.php';
require_login();

$db = db();
$sql = "SELECT s.id, s.url, s.url_name, s.account, s.password, s.last_modified_time, u.username AS modified_by
        FROM sites s
        LEFT JOIN users u ON u.id = s.modified_by_user_id
        ORDER BY s.id ASC";
$result = $db->query($sql);
$sites = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت رمزها</title>
    <link rel="stylesheet" href="assets/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>

<div class="top-menu">
    <div class="menu-links">
        <a href="index.php" class="active">مدیریت رمزها</a>
        <a href="phonebook.php">دفترچه تلفن</a>
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
    <div class="page-title">
        <div>
            <h2>مدیریت رمزها</h2>
            <p>لیست حساب‌های مشترک سایت‌ها</p>
        </div>
        <button type="button" class="btn add-btn" id="addBtn">+ افزودن سایت</button>
    </div>

    <div class="search-box">
        <input type="text" id="search" class="form-control search-input" placeholder="جستجو در همه اطلاعات و زدن Enter...">
    </div>

    <div class="table-responsive">
        <table class="table password-table">
            <colgroup>
                <col class="col-site"><col class="col-url"><col class="col-password"><col class="col-date"><col class="col-user"><col class="col-actions">
            </colgroup>
            <thead>
                <tr>
                    <th>نام / حساب</th>
                    <th>URL</th>
                    <th>رمز عبور</th>
                    <th>آخرین تغییر</th>
                    <th>آخرین تغییر توسط</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody id="siteList">
                <?php foreach ($sites as $s): ?>
                <tr data-id="<?= (int)$s['id'] ?>">
                    <td class="site-account"><strong class="site-name"><?= htmlspecialchars($s['url_name'], ENT_QUOTES, 'UTF-8') ?></strong><span class="account-text"><?= htmlspecialchars($s['account'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td class="url-cell"><?= htmlspecialchars($s['url'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="password"><?= htmlspecialchars($s['password'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td><?= $s['last_modified_time'] ? htmlspecialchars(date('Y/m/d - H:i', strtotime($s['last_modified_time'])), ENT_QUOTES, 'UTF-8') : '-' ?></td>
                    <td><?= htmlspecialchars($s['modified_by'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="action-cell"><button class="btn edit-btn">ویرایش</button><button class="btn history-btn">تاریخچه</button><button class="btn delete-btn">حذف</button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="no-result" id="noResult" style="display:none">موردی پیدا نشد.</p>
</div>

<div class="modal fade" id="siteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content site-box">
            <div class="modal-header">
                <h5 class="modal-title" id="siteModalTitle">افزودن سایت</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="siteName" class="form-label">نام سایت</label>
                    <input type="text" id="siteName" class="form-control site-input" placeholder="مثلا Gmail">
                </div>
                <div class="mb-3">
                    <label for="siteUrl" class="form-label">آدرس سایت</label>
                    <input type="text" id="siteUrl" class="form-control site-input" placeholder="https://gmail.com">
                </div>
                <div class="mb-3">
                    <label for="siteUser" class="form-label">حساب کاربری</label>
                    <input type="text" id="siteUser" class="form-control site-input" placeholder="company@gmail.com">
                </div>
                <div class="mb-3">
                    <label for="sitePass" class="form-label">رمز عبور</label>
                    <input type="text" id="sitePass" class="form-control site-input" placeholder="رمز عبور" autocomplete="new-password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel-btn" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn save-btn" id="saveSite">ذخیره</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content history-box">
            <div class="modal-header">
                <h5 class="modal-title" id="historyTitle">تاریخچه رمز</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table history-table">
                        <thead>
                            <tr>
                                <th>تاریخ و ساعت</th>
                                <th>کاربر</th>
                                <th>رمز قبلی</th>
                                <th>رمز جدید</th>
                            </tr>
                        </thead>
                        <tbody id="historyList"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel-btn" data-bs-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>

<script>window.APP = {csrf: <?= json_encode($csrf) ?>};</script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/index.js"></script>
</body>
</html>
