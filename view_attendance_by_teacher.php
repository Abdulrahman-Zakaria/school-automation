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

// جلب بيانات الحضور الخاصة بالمعلم
$sql = "
    SELECT a.date, a.status, a.subject, u.name AS student_name
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE a.teacher_id = ?
    ORDER BY a.date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$records = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>سجل حضور الطلاب لمادتي</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4>📋 حضور الطلاب لمادتي</h4>
        </div>
        <div class="card-body">
            <?php if (count($records) > 0): ?>
                <table class="table table-bordered table-striped text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>👨‍🎓 الطالب</th>
                            <th>📚 المادة</th>
                            <th>📅 التاريخ</th>
                            <th>✅ الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['student_name']) ?></td>
                                <td><?= htmlspecialchars($row['subject']) ?></td>
                                <td><?= $row['date'] ?></td>
                                <td>
                                    <span class="badge bg-<?= $row['status'] === 'present' ? 'success' : 'danger' ?>">
                                        <?= $row['status'] === 'present' ? 'حاضر' : 'غائب' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info text-center">لا توجد سجلات حضور لمادتك حتى الآن.</div>
            <?php endif; ?>
        </div>
        <div class="card-footer text-center">
            <a href="dashboard.php" class="btn btn-outline-secondary">🔙 العودة إلى لوحة التحكم</a>
        </div>
    </div>
</div>

</body>
</html>
