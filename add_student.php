<?php
session_start();
include("conn.php");
require_once "phpqrcode/qrlib.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['student_name'], $_POST['grade_level'], $_POST['email'], $_POST['password'], $_POST['student_id'], $_POST['class_name']) &&
        !empty($_POST['student_name']) && !empty($_POST['grade_level']) &&
        !empty($_POST['email']) && !empty($_POST['password']) &&
        !empty($_POST['student_id']) && !empty($_POST['class_name'])
    ) {
        $studentName = $_POST['student_name'];
        $gradeLevel = $_POST['grade_level'];
        $email = $_POST['email'];
        $studentId = $_POST['student_id'];
        $className = $_POST['class_name'];
        $raw_password = $_POST['password'];
        $password = password_hash($raw_password, PASSWORD_DEFAULT);
        $qr_code = uniqid('QR_');
        $role = 'student';

        $conn = new mysqli("localhost", "root", "", "school_db");
        if ($conn->connect_error) {
            die("فشل الاتصال: " . $conn->connect_error);
        }

        $stmt1 = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt1->bind_param("ssss", $studentName, $email, $password, $role);

        if ($stmt1->execute()) {
            $user_id = $stmt1->insert_id;

            $stmt2 = $conn->prepare("INSERT INTO students (id, user_id, grade_level, qr_code, class_name) VALUES (?, ?, ?, ?, ?)");
            $stmt2->bind_param("iisss", $studentId, $user_id, $gradeLevel, $qr_code, $className);
            if ($stmt2->execute()) {
                $qrDir = "qrcodes/";
                if (!is_dir($qrDir)) mkdir($qrDir);
                $qrFile = $qrDir . $qr_code . ".png";
                QRcode::png(base64_encode($qr_code), $qrFile);

                $message = "
                    <div class='alert alert-success'>
                        ✅ تم إضافة الطالب بنجاح.<br>
                        📧 البريد الإلكتروني: <strong>$email</strong><br>
                        🔐 كلمة المرور: <strong>$raw_password</strong><br>
                        <img src='$qrFile' alt='QR Code' class='mt-3'>
                    </div>
                ";
            } else {
                $message = "<div class='alert alert-danger'>❌ خطأ أثناء إضافة الطالب: {$stmt2->error}</div>";
            }
            $stmt2->close();
        } else {
            $message = "<div class='alert alert-danger'>❌ خطأ أثناء إضافة المستخدم: {$stmt1->error}</div>";
        }

        $stmt1->close();
        $conn->close();
    } else {
        $message = "<div class='alert alert-warning'>❌ يرجى ملء جميع الحقول.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة طالب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4>➕ إضافة طالب جديد</h4>
        </div>
        <div class="card-body">
            <?php echo $message; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">اسم الطالب:</label>
                    <input type="text" name="student_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">رقم الطالب (ID):</label>
                    <input type="number" name="student_id" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">الصف الدراسي:</label>
                    <input type="text" name="grade_level" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">اسم الصف:</label>
                    <input type="text" name="class_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">كلمة المرور:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success w-50">إضافة وتوليد QR</button>
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
