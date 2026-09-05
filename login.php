<?php
require_once __DIR__ . '/config/bootstrap.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>ورود | رمز عبور</title>
</head>
<body>
<main>
    <div class="login">
        <div class="lockImg"><img src="assets/images/lock.png" alt="قفل"></div>
        <h1>سیستم مدیریت رمز عبور</h1>
        <p>برای ورود به سامانه اطلاعات حساب خود را وارد کنید</p>
        <form class="usernamePass" id="usernamePass">
            <div class="usernameLogin">
                <label for="username">نام کاربری</label>
                <input type="text" id="username" placeholder="نام کاربری خود را وارد کنید" name="username" required autocomplete="username">
            </div>
            <div class="passwordLogin">
                <label for="password">رمز عبور</label>
                <input type="password" id="password" placeholder="رمز عبور خود را وارد کنید" name="password" required autocomplete="current-password">
            </div>
            <button type="submit">ورود به سامانه</button>
        </form>
        <p class="footerText">Shared Password Management System</p>
    </div>
</main>
<script>window.APP = {csrf: <?= json_encode($csrf) ?>};</script>
<script src="assets/js/login.js"></script>
</body>
</html>
