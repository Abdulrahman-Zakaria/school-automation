<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user']['id'];
$conn = new mysqli("localhost", "root", "", "school_db");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$studentData = null;
$grade = null;
$attendanceCount = 0;
$message = "";

// الحصول على المادة التي يدرسها المعلم
$subject = "";
$stmt = $conn->prepare("SELECT subject FROM users WHERE id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$stmt->bind_result($subject);
$stmt->fetch();
$stmt->close();

// عند البحث
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['student_id'])) {
    $student_id = (int)$_GET['student_id'];

    // بيانات الطالب
    $stmt = $conn->prepare("
        SELECT u.name, s.id as student_id
        FROM students s
        JOIN users u ON s.user_id = u.id
        WHERE s.id = ?
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $studentData = $result->fetch_assoc();
    $stmt->close();

    if ($studentData) {
        // جلب الدرجة الخاصة بهذه المادة
        $stmt2 = $conn->prepare("SELECT grade FROM grades WHERE student_id = ? AND subject = ? ORDER BY date DESC LIMIT 1");
        $stmt2->bind_param("is", $student_id, $subject);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        $grade = $res2->num_rows > 0 ? $res2->fetch_assoc()['grade'] : null;
        $stmt2->close();

        // جلب عدد مرات الحضور لهذه المادة
        $stmt3 = $conn->prepare("SELECT COUNT(*) as total FROM attendance WHERE student_id = ? AND subject = ?");
        $stmt3->bind_param("is", $student_id, $subject);
        $stmt3->execute();
        $res3 = $stmt3->get_result();
        $attendanceCount = $res3->fetch_assoc()['total'];
        $stmt3->close();
    } else {
        $message = "<div class='alert alert-warning'>⚠️ رقم الطالب غير موجود.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بحث عن طالب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4>🔍 بحث عن طالب</h4>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 mb-4">
                <div class="col-md-9">
                    <input type="number" name="student_id" class="form-control" placeholder="أدخل رقم الطالب" required>
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-success">بحث</button>
                </div>
            </form>

            <?= $message ?>

            <?php if ($studentData): ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">👨‍🎓 اسم الطالب: <?= htmlspecialchars($studentData['name']) ?></h5>
                        <p class="card-text"><strong>📚 المادة:</strong> <?= htmlspecialchars($subject) ?></p>
                        <p class="card-text"><strong>✅ الدرجة:</strong> <?= $grade ? $grade : "لا يوجد درجة مسجلة بعد" ?></p>
                        <p class="card-text"><strong>📅 عدد مرات الحضور:</strong> <?= $attendanceCount ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer text-center">
            <a href="dashboard.php" class="btn btn-outline-secondary">🔙 العودة إلى لوحة التحكم</a>
        </div>
    </div>
</div>

</body>
</html>
