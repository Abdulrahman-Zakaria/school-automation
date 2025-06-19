<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "school_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT class_name FROM students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($class_name);
$stmt->fetch();
$stmt->close();

$schedule = [];
if ($class_name) {
    $stmt2 = $conn->prepare("SELECT subject, day_of_week, time_start, time_end FROM schedule WHERE class_name = ? ORDER BY FIELD(day_of_week, 'السبت','الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس'), time_start");
    $stmt2->bind_param("s", $class_name);
    $stmt2->execute();
    $result = $stmt2->get_result();
    while ($row = $result->fetch_assoc()) {
        $schedule[] = $row;
    }
    $stmt2->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>جدول الطالب</title>
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
            <h4>📘 جدول الطالب - الصف: <?php echo htmlspecialchars($class_name); ?></h4>
        </div>
        <div class="card-body">
            <?php if (!empty($schedule)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>📅 اليوم</th>
                                <th>📚 المادة</th>
                                <th>⏰ من</th>
                                <th>⏰ إلى</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedule as $row): ?>
                                <tr>
                                    <td><?php echo $row['day_of_week']; ?></td>
                                    <td><?php echo $row['subject']; ?></td>
                                    <td><?php echo $row['time_start']; ?></td>
                                    <td><?php echo $row['time_end']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">لا يوجد جدول مسجل لهذا الصف حالياً.</div>
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
