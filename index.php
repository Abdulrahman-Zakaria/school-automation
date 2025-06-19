<?php
session_start();

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>المدرسة الإلكترونية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('school-bg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.95);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">
<div class="container">
    <div class="card shadow text-center mx-auto" style="max-width: 500px;">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">📚 المدرسة الإلكترونية</h3>
        </div>
        <div class="card-body">
            <img src="imgs/school-logo.png" alt="شعار المدرسة" class="mb-3" style="max-width: 100px;">
            <p class="lead">مرحباً بكم في نظام المدرسة الإلكتروني</p>
            <p>يرجى تسجيل الدخول للوصول إلى النظام</p>
            <div class="d-grid gap-2 col-8 mx-auto mt-4">
                <a href="login.php" class="btn btn-success">🔐 تسجيل الدخول</a>
            </div>
        </div>
        <div class="card-footer text-muted">
            © <?php echo date("Y"); ?> جميع الحقوق محفوظة
        </div>
    </div>
</div>
</body>
</html>
