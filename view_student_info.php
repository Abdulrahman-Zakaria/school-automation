<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'parent') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['student_id'])) {
    die("رقم الطالب غير موجود.");
}

$student_id = (int)$_GET['student_id'];
$conn = new mysqli("localhost", "root", "", "school_db");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// جلب اسم الطالب
$name_sql = "SELECT u.name FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = $student_id";
$name_result = $conn->query($name_sql);
$student_name = ($name_result && $name_result->num_rows > 0) ? $name_result->fetch_assoc()['name'] : "غير معروف";

// جلب الحضور مع المادة
$attendance_sql = "SELECT date, status, subject FROM attendance WHERE student_id = $student_id ORDER BY date DESC";
$attendance_result = $conn->query($attendance_sql);

// جلب الدرجات
$grades_sql = "SELECT subject, grade, date FROM grades WHERE student_id = $student_id ORDER BY date DESC";
$grades_result = $conn->query($grades_sql);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بيانات الطالب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="text-center mb-4">
        <h3 class="text-primary">📄 بيانات الطالب: <span class="text-dark"><?php echo htmlspecialchars($student_name); ?></span></h3>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header bg-info text-white text-center">
            <h4>📅 سجل الحضور</h4>
        </div>
        <div class="card-body">
            <?php if ($attendance_result->num_rows > 0): ?>
                <ul class="list-group">
                    <?php while($row = $attendance_result->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>📚 <?php echo htmlspecialchars($row['subject']); ?></strong><br>
                                <small class="text-muted">📅 <?php echo $row['date']; ?></small>
                            </div>
                            <span class="badge bg-<?php echo $row['status'] === 'present' ? 'success' : 'danger'; ?>">
                                <?php echo $row['status'] === 'present' ? 'حاضر' : 'غائب'; ?>
                            </span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="alert alert-warning text-center">لا توجد سجلات حضور.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4>📚 سجل الدرجات</h4>
        </div>
        <div class="card-body">
            <?php if ($grades_result->num_rows > 0): ?>
                <ul class="list-group">
                    <?php while($row = $grades_result->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($row['subject']); ?></strong><br>
                                <small class="text-muted">📅 <?php echo $row['date']; ?></small>
                            </div>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($row['grade']); ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="alert alert-info text-center">لا توجد درجات مسجلة.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="dashboard.php" class="btn btn-outline-secondary">🔙 العودة إلى لوحة التحكم</a>
    </div>
</div>

</body>
</html>

<?php $conn->close(); ?>
