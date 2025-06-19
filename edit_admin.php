<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "school_db");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$admin_id = $_SESSION['user']['id'];
$message = "";

// تحديث البيانات عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    if ($password) {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=? AND role='admin'");
        $stmt->bind_param("sssi", $name, $email, $password, $admin_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=? AND role='admin'");
        $stmt->bind_param("ssi", $name, $email, $admin_id);
    }

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>✅ تم تحديث البيانات بنجاح.</div>";
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;
    } else {
        $message = "<div class='alert alert-danger'>❌ حدث خطأ أثناء التحديث.</div>";
    }
    $stmt->close();
}

// جلب بيانات المدير
$stmt2 = $conn->prepare("SELECT name, email FROM users WHERE id=? AND role='admin'");
$stmt2->bind_param("i", $admin_id);
$stmt2->execute();
$stmt2->bind_result($name, $email);
$stmt2->fetch();
$stmt2->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعديل بيانات المدير</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow mx-auto" style="max-width: 600px;">
        <div class="card-header bg-primary text-white text-center">
            <h4>✏️ تعديل بيانات المدير</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($message)) echo $message; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label">الاسم:</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني:</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">كلمة المرور الجديدة (اختياري):</label>
                    <input type="password" name="password" class="form-control" placeholder="اتركه فارغًا إن لم ترغب في تغييره">
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-warning">💾 حفظ التعديلات</button>
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
