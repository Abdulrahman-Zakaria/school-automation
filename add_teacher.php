<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "school_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['teacher_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (id, name, email, password, role, subject) VALUES (?, ?, ?, ?, 'teacher', ?)");
    $stmt->bind_param("issss", $id, $name, $email, $password, $subject);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>✅ تمت إضافة المعلم بنجاح.</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ حدث خطأ أثناء إضافة المعلم: " . $stmt->error . "</div>";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة معلم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4>➕ إضافة معلم جديد</h4>
        </div>
        <div class="card-body">
            <?php echo $message; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label">رقم المعلم (ID):</label>
                    <input type="number" name="teacher_id" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">الاسم الكامل:</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">المادة:</label>
                    <input type="text" name="subject" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">كلمة المرور:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success w-50">➕ إضافة المعلم</button>
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
