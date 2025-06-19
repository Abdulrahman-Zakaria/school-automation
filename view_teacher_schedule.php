<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user']['id'];

$conn = new mysqli("localhost", "root", "", "school_db");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT class_name, day_of_week, time_start, time_end FROM teacher_schedule WHERE teacher_id = ? ORDER BY FIELD(day_of_week, 'السبت','الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس'), time_start");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>جدولي الدراسي</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4>📅 جدول الحصص حسب الصف</h4>
        </div>
        <div class="card-body">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>📅 اليوم</th>
                                <th>📘 الصف</th>
                                <th>⏰ من</th>
                                <th>⏰ إلى</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['day_of_week']; ?></td>
                                    <td><?php echo $row['class_name']; ?></td>
                                    <td><?php echo $row['time_start']; ?></td>
                                    <td><?php echo $row['time_end']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-center">لا يوجد جدول مسجل حالياً.</div>
            <?php endif; ?>
        </div>
        <div class="card-footer text-center no-print">
            <a href="dashboard.php" class="btn btn-outline-secondary">🔙 العودة إلى لوحة التحكم</a>
            <button onclick="window.print()" class="btn btn-success ms-2">🖨️ طباعة الجدول</button>
        </div>
    </div>
</div>

</body>
</html>

<?php
$conn->close();
?>
