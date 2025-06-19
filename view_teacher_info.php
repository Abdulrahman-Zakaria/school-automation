<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";
$teacher = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['teacher_id'])) {
    $teacher_id = (int)$_POST['teacher_id'];

    $conn = new mysqli("localhost", "root", "", "school_db");
    if ($conn->connect_error) {
        die("فشل الاتصال: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT id, name, email, subject, role FROM users WHERE id = ? AND role = 'teacher'");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacher = $result->fetch_assoc();

    if (!$teacher) {
        $message = "<div class='alert alert-danger text-center'>❌ لم يتم العثور على معلم بهذا الرقم.</div>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>عرض معلومات المعلم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="card mb-4 shadow">
        <div class="card-header bg-primary text-white text-center">
            <h5>📄 عرض بيانات المعلم</h5>
        </div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <div class="col-md-9">
                    <input type="number" name="teacher_id" class="form-control" placeholder="رقم المعلم" required>
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-success">🔍 عرض</button>
                </div>
            </form>
        </div>
    </div>
            <?php echo $message; ?>

            <?php if ($teacher): ?>
                 <div class="card shadow mb-3">
                      <div class="card-header bg-info text-white">👤 بيانات المعلم</div>
                    <div class="card-body">
                <p><strong>الاسم:</strong> <?= htmlspecialchars($teacher['name']) ?></p>
                <p><strong>البريد الإلكتروني:</strong> <?= htmlspecialchars($teacher['email']) ?></p>
                <p><strong>المادة</strong> <?= htmlspecialchars($teacher['subject']) ?></p>
                </div>
                </div>
                <div class="text-center mt-3">
                    <a href="edit_teacher.php?id=<?php echo $teacher['id']; ?>" class="btn btn-warning me-2">✏️ تعديل بيانات المعلم</a>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer text-center">
            <a href="dashboard.php" class="btn btn-outline-secondary" style="margin-left :0.5rem;">🔙 العودة إلى لوحة التحكم</a>
        </div>
    </div>
</div>
</body>
</html>
