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

$grades = [];
$average = 0;

if ($result->num_rows === 1) {
    $student = $result->fetch_assoc();
    $student_id = $student['id'];

    $grades_sql = "SELECT subject, grade, date FROM grades WHERE student_id = $student_id ORDER BY date DESC";
    $grades_result = $conn->query($grades_sql);

    if ($grades_result && $grades_result->num_rows > 0) {
        while ($row = $grades_result->fetch_assoc()) {
            $row['grade'] = is_numeric($row['grade']) ? floatval($row['grade']) : 0;
            $grades[] = $row;
        }

        // حساب المتوسط
        $total = array_sum(array_column($grades, 'grade'));
        $count = count($grades);
        $average = $count > 0 ? round($total / $count, 2) : 0;
    }
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
    <title>درجاتي</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow mx-auto" style="max-width: 700px;">
        <div class="card-header bg-primary text-white text-center">
            <h4>📚 الدرجات الخاصة بي</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($grades)): ?>
                <ul class="list-group mb-4">
                    <?php foreach ($grades as $row): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($row['subject']); ?></strong><br>
                                <small class="text-muted">📅 <?php echo $row['date']; ?></small>
                            </div>
                            <span class="badge bg-success fs-6"><?php echo $row['grade']; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="mb-4 text-center">
                    <h5>🔢 متوسط الدرجات: <span class="text-primary"><?php echo $average; ?></span></h5>
                </div>

                <canvas id="gradeChart" height="300"></canvas>

                <script>
                    const ctx = document.getElementById('gradeChart').getContext('2d');
                    const gradeChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: <?= json_encode(array_column($grades, 'subject')) ?>,
                            datasets: [{
                                label: 'الدرجات',
                                data: <?= json_encode(array_column($grades, 'grade')) ?>,
                                backgroundColor: [
                                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { font: { size: 14 } }
                                }
                            }
                        }
                    });
                </script>

            <?php else: ?>
                <div class="alert alert-info text-center">لا توجد درجات مسجلة.</div>
            <?php endif; ?>
        </div>
        <div class="card-footer text-center">
            <a href="dashboard.php" class="btn btn-outline-secondary">🔙 العودة إلى لوحة التحكم</a>
        </div>
    </div>
</div>

</body>
</html>
