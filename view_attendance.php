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

$stmt = $conn->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$attendance_result = [];

if ($result->num_rows === 1) {
    $student = $result->fetch_assoc();
    $student_id = $student['id'];

    $attendance_sql = "
        SELECT a.date, a.status, a.subject, u.name AS teacher_name
        FROM attendance a
        LEFT JOIN users u ON a.teacher_id = u.id
        WHERE a.student_id = ?
        ORDER BY a.date DESC
    ";
    $stmt2 = $conn->prepare($attendance_sql);
    $stmt2->bind_param("i", $student_id);
    $stmt2->execute();
    $attendance_result = $stmt2->get_result();
    $stmt2->close();
} else {
    die("لم يتم العثور على بيانات الطالب.");
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>سجل الحضور</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow mx-auto" style="max-width: 700px;">
        <div class="card-header bg-success text-white text-center">
            <h4>📅 سجل الحضور الخاص بي</h4>
        </div>
        <div class="card-body">
            <?php if ($attendance_result && $attendance_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>📅 التاريخ</th>
                                <th>📚 المادة</th>
                                <th>👨‍🏫 المعلم</th>
                                <th>✅ الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $attendance_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['date']; ?></td>
                                    <td><?= htmlspecialchars($row['subject'] ?? 'غير محددة'); ?></td>
                                    <td><?= htmlspecialchars($row['teacher_name'] ?? 'غير معروف'); ?></td>
                                    <td>
                                        <span class="badge bg-<?= $row['status'] === 'present' ? 'success' : 'danger'; ?>">
                                            <?= $row['status'] === 'present' ? 'حاضر' : 'غائب'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">لا توجد سجلات حضور.</div>
            <?php endif; ?>
        </div>
        <div class="card-footer text-center">
            <a href="dashboard.php" class="btn btn-outline-primary">🔙 العودة إلى لوحة التحكم</a>
        </div>
    </div>
</div>

</body>
</html>
