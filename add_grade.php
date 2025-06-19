<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "school_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$teacher_id = $_SESSION['user']['id'];

// ✅ جلب المادة الخاصة بالمعلم
$stmt = $conn->prepare("SELECT subject FROM users WHERE id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$stmt->bind_result($subject);
$stmt->fetch();
$stmt->close();

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $grade = $_POST['grade'];
    $date = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO grades (student_id, subject, grade, date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $student_id, $subject, $grade, $date);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>✅ تم تسجيل الدرجة لمادة <strong>$subject</strong> بنجاح.</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ حدث خطأ: " . $stmt->error . "</div>";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة درجة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white text-center">
            <h4>📘 إضافة درجة لمادة: <span class="text-warning"><?php echo htmlspecialchars($subject); ?></span></h4>
        </div>
        <div class="card-body">
            <?php echo $message; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label">رقم الطالب (ID)</label>
                    <input type="number" name="student_id" class="form-control" required>
                </div>

                <!-- لا يتم عرض حقل المادة لأنه مأخوذ من جلسة المعلم -->
                <input type="hidden" name="subject" value="<?php echo htmlspecialchars($subject); ?>">

                <div class="mb-3">
                    <label class="form-label">الدرجة</label>
                    <input type="text" name="grade" class="form-control" required>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success w-50">➕ إضافة الدرجة</button>
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
