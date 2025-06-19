<?php
// add_schedule.php - صفحة إضافة جدول دراسي (للمدير)
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "school_db");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = $_POST['class_name'];
    $subject = $_POST['subject'];
    $day = $_POST['day_of_week'];
    $start = $_POST['time_start'];
    $end = $_POST['time_end'];

    $stmt = $conn->prepare("INSERT INTO schedule (class_name, subject, day_of_week, time_start, time_end) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $class_name, $subject, $day, $start, $end);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>✅ تمت إضافة الحصة إلى الجدول بنجاح.</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ حدث خطأ أثناء الإضافة: " . $stmt->error . "</div>";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة جدول</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4>📅 إضافة حصة إلى الجدول الدراسي</h4>
        </div>
        <div class="card-body">
            <?php echo $message; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label">اسم الصف:</label>
                    <input type="text" name="class_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">المادة:</label>
                    <input type="text" name="subject" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">اليوم:</label>
                    <select name="day_of_week" class="form-select" required>
                        <option value="السبت">السبت</option>
                        <option value="الأحد">الأحد</option>
                        <option value="الاثنين">الاثنين</option>
                        <option value="الثلاثاء">الثلاثاء</option>
                        <option value="الأربعاء">الأربعاء</option>
                        <option value="الخميس">الخميس</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">من الساعة:</label>
                        <input type="time" name="time_start" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">إلى الساعة:</label>
                        <input type="time" name="time_end" class="form-control" required>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success w-50">➕ إضافة الحصة</button>
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
