<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "school_db");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$user_id = $_SESSION['user']['id'];
$message = "";

// تعديل كلمة المرور
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_password, $user_id);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success text-center'>✅ تم تحديث كلمة المرور بنجاح.</div>";
    } else {
        $message = "<div class='alert alert-danger text-center'>❌ حدث خطأ أثناء تحديث كلمة المرور.</div>";
    }
    $stmt->close();
}

// جلب بيانات الطالب
$stmt = $conn->prepare("
    SELECT u.id as user_id, u.name, u.email, s.qr_code, s.id as student_id, s.class_name 
    FROM users u 
    JOIN students s ON u.id = s.user_id 
    WHERE u.id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();
$conn->close();

$qr_image_path = "qrcodes/" . $student['qr_code'] . ".png";
$school_logo = "imgs/school-logo.png";
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بطاقة الطالب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Tajawal', sans-serif;
        }
        .student-card {
            width: 100%;
            max-width: 800px;
            margin: auto;
            margin-top: 50px;
            background: linear-gradient(90deg, #ffffff, #e3f2fd);
            border: 2px solid #0d6efd;
            border-radius: 15px;
            padding: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: relative;
        }
        .qr-code img {
            width: 120px;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin-bottom: 4rem;
        }
        .details {
            flex: 1;
            padding-right: 40px;
        }
        .details p {
            font-size: 1.25rem;
            margin: 15px 0;
        }
        .icon {
            font-size: 1.6rem;
            margin-left: 10px;
        }
        .school-logo {
            width: 80px;
            position: absolute;
            bottom: 15px;
            left: 20px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                background: white;
            }
            .student-card {
                box-shadow: none;
                border: 1px solid #000;
            }
        }
        @media print {
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}

    </style>
</head>
<body>

<?php if ($message) echo $message; ?>

<div class="student-card">
    <div class="details">
        <h4 class="text-primary mb-4">🎓 بطاقة الطالب</h4>
        <p><span class="icon">👨‍🎓</span><strong>الاسم:</strong> <?= htmlspecialchars($student['name']) ?></p>
        <p><span class="icon">📧</span><strong>البريد الإلكتروني:</strong> <?= htmlspecialchars($student['email']) ?></p>
        <p><span class="icon">🆔</span><strong>رقم الطالب:</strong> <?= htmlspecialchars($student['student_id']) ?></p>
        <p><span class="icon">🏫</span><strong>الصف:</strong> <?= htmlspecialchars($student['class_name']) ?></p>
    </div>
    <div class="qr-code">
        <img src="<?= $qr_image_path ?>" alt="QR Code">
    </div>
    <img src="<?= $school_logo ?>" alt="شعار المدرسة" class="school-logo">
</div>

<div class="text-center mt-4 no-print">
    <form method="post" class="d-inline-block">
        <div class="mb-3">
            <input type="password" name="new_password" placeholder="كلمة مرور جديدة" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-warning">🔐 حفظ كلمة المرور الجديدة</button>
    </form>
    <button class="btn btn-success mx-2" onclick="window.print()">🖨️ طباعة البطاقة</button>
    <a href="dashboard.php" class="btn btn-outline-primary">🔙 العودة إلى لوحة التحكم</a>
</div>

</body>
</html>
