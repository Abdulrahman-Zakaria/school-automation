<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "school_db");
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$studentData = null;
$grades = [];
$attendance = [];
$message = "";

// عرض بيانات الطالب
if (isset($_GET['student_id']) && is_numeric($_GET['student_id'])) {
    $student_id = (int)$_GET['student_id'];

    $stmt = $conn->prepare("
        SELECT s.id, u.name, u.email, s.grade_level, s.class_name, s.qr_code
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
        // الدرجات
        $grades_stmt = $conn->prepare("SELECT subject, grade, date FROM grades WHERE student_id = ? ORDER BY date DESC");
        $grades_stmt->bind_param("i", $student_id);
        $grades_stmt->execute();
        $grades = $grades_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $grades_stmt->close();

        // الحضور
        $att_stmt = $conn->prepare("SELECT date, status FROM attendance WHERE student_id = ? ORDER BY date DESC");
        $att_stmt->bind_param("i", $student_id);
        $att_stmt->execute();
        $attendance = $att_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $att_stmt->close();
    } else {
        $message = "<div class='alert alert-warning'>⚠️ لم يتم العثور على بيانات للطالب.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>عرض بيانات الطالب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="card mb-4 shadow">
        <div class="card-header bg-primary text-white text-center">
            <h5>📄 عرض بيانات الطالب</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-9">
                    <input type="number" name="student_id" class="form-control" placeholder="رقم الطالب" required>
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-success">🔍 عرض</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($message)) echo $message; ?>

    <?php if (!empty($studentData)): ?>
        <div class="card shadow mb-3">
            <div class="card-header bg-info text-white">👤 بيانات الطالب</div>
            <div class="card-body">
                <p><strong>الاسم:</strong> <?= htmlspecialchars($studentData['name']) ?></p>
                <p><strong>البريد الإلكتروني:</strong> <?= htmlspecialchars($studentData['email']) ?></p>
                <p><strong>الصف الدراسي:</strong> <?= htmlspecialchars($studentData['grade_level']) ?></p>
                <p><strong>اسم الصف:</strong> <?= htmlspecialchars($studentData['class_name']) ?></p>
                <?php if (!empty($studentData['qr_code'])): ?>
                    <p><strong>رمز QR:</strong><br>
                        <img src="qrcodes/<?= htmlspecialchars($studentData['qr_code']) ?>.png" width="150">
                    </p>
                <?php endif; ?>
                <div class="text-center mt-3">
                    <a href="edit_student.php?student_id=<?= $studentData['id'] ?>" class="btn btn-warning">✏️ تعديل بيانات الطالب</a>
                </div>
            </div>
        </div>

        <div class="card shadow mb-3">
            <div class="card-header bg-secondary text-white">📊 الدرجات</div>
            <div class="card-body">
                <?php if (!empty($grades)): ?>
                    <ul class="list-group">
                        <?php foreach ($grades as $g): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><?= htmlspecialchars($g['subject']) ?> (<?= $g['date'] ?>)</span>
                                <span class="badge bg-success"><?= $g['grade'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="alert alert-info">لا توجد درجات.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow mb-3">
            <div class="card-header bg-dark text-white">📅 الحضور</div>
            <div class="card-body">
                <?php if (!empty($attendance)): ?>
                    <ul class="list-group">
                        <?php foreach ($attendance as $a): ?>
                            <li class="list-group-item">
                                <?= $a['date'] ?> - <?= $a['status'] === 'present' ? '✅ حاضر' : '❌ غائب' ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="alert alert-info">لا توجد سجلات حضور.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="text-center mt-3">
        <a href="dashboard.php" class="btn btn-outline-secondary">🔙 العودة إلى لوحة التحكم</a>
    </div>
</div>
</body>
</html>
