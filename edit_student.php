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

$message = "";

// حذف الطالب
if (isset($_POST['delete']) && isset($_POST['student_id'])) {
    $student_id = (int)$_POST['student_id'];

    // حذف الطالب والمستخدم المرتبط به
    $stmt = $conn->prepare("DELETE FROM users WHERE id = (SELECT user_id FROM students WHERE id = ?)");
    $stmt->bind_param("i", $student_id);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>✅ تم حذف الطالب بنجاح.</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ فشل الحذف: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// تعديل الطالب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $student_id = (int)$_POST['student_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $grade_level = $_POST['grade_level'];
    $class_name = $_POST['class_name'];

    // تحديث جدول users
    $stmt1 = $conn->prepare("UPDATE users u JOIN students s ON u.id = s.user_id SET u.name=?, u.email=? WHERE s.id=?");
    $stmt1->bind_param("ssi", $name, $email, $student_id);
    $stmt1->execute();
    $stmt1->close();

    // تحديث جدول students
    $stmt2 = $conn->prepare("UPDATE students SET grade_level=?, class_name=? WHERE id=?");
    $stmt2->bind_param("ssi", $grade_level, $class_name, $student_id);
    $stmt2->execute();
    $stmt2->close();

    $message = "<div class='alert alert-success'>✅ تم تحديث بيانات الطالب بنجاح.</div>";
}

// جلب بيانات الطالب
$studentData = null;
if (isset($_GET['student_id']) && is_numeric($_GET['student_id'])) {
    $student_id = (int)$_GET['student_id'];
    $stmt = $conn->prepare("
        SELECT s.id, u.name, u.email, s.grade_level, s.class_name
        FROM students s
        JOIN users u ON s.user_id = u.id
        WHERE s.id = ?
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $studentData = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعديل بيانات الطالب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <script>
        function confirmDelete() {
            return confirm("هل أنت متأكد أنك تريد حذف هذا الطالب؟ لا يمكن التراجع!");
        }
    </script>
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4>✏️ تعديل بيانات الطالب</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($message)) echo $message; ?>

            <?php if ($studentData): ?>
                <form method="post">
                    <input type="hidden" name="student_id" value="<?= $studentData['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">الاسم:</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($studentData['name']) ?>" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">البريد الإلكتروني:</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($studentData['email']) ?>" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">الصف الدراسي:</label>
                        <input type="text" name="grade_level" value="<?= htmlspecialchars($studentData['grade_level']) ?>" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">اسم الصف:</label>
                        <input type="text" name="class_name" value="<?= htmlspecialchars($studentData['class_name']) ?>" class="form-control" required>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" name="update" class="btn btn-success">💾 حفظ التعديلات</button>
                        <button type="submit" name="delete" class="btn btn-danger" onclick="return confirmDelete();">🗑️ حذف الطالب</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-warning text-center">⚠️ لم يتم العثور على الطالب.</div>
            <?php endif; ?>
        </div>
        <div class="card-footer text-center">
            <a href="view_student_admin.php" class="btn btn-outline-secondary">🔙 العودة</a>
        </div>
    </div>
</div>
</body>
</html>
