<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher = $_SESSION['user'];
$message = "";

// تحديث كلمة المرور
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $newPassword = $_POST['new_password'];
    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

    $conn = new mysqli("localhost", "root", "", "school_db");
    if ($conn->connect_error) {
        die("فشل الاتصال: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed, $teacher['id']);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success text-center'>✅ تم تحديث كلمة المرور بنجاح.</div>";
    } else {
        $message = "<div class='alert alert-danger text-center'>❌ حدث خطأ أثناء التحديث.</div>";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الملف الشخصي للمعلم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow mx-auto" style="max-width: 600px;">
        <div class="card-header bg-primary text-white text-center">
            <h4>👨‍🏫 الملف الشخصي للمعلم</h4>
        </div>
        <div class="card-body">
            <?= $message ?>
            <p><strong>👤 الاسم:</strong> <?= htmlspecialchars($teacher['name']) ?></p>
            <p><strong>📧 البريد الإلكتروني:</strong> <?= htmlspecialchars($teacher['email']) ?></p>
            <p><strong>📚 المادة:</strong> <?= htmlspecialchars($teacher['subject'] ?? 'غير محددة') ?></p>

            <hr>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">🔒 كلمة مرور جديدة:</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-warning">🔄 تحديث كلمة المرور</button>
                </div>
            </form>
        </div>
        <div class="card-footer text-center">
            <a href="dashboard.php" class="btn btn-outline-secondary">🔙 العودة إلى لوحة التحكم</a>
        </div>
    </div>
</div>

</body>
</html>
