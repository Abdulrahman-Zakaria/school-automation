<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("رقم المعلم غير موجود.");
}

$teacher_id = (int)$_GET['id'];
$message = "";

$conn = new mysqli("localhost", "root", "", "school_db");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// حذف المعلم إذا تم الطلب
if (isset($_POST['delete'])) {
    $del_stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'teacher'");
    $del_stmt->bind_param("i", $teacher_id);
    if ($del_stmt->execute()) {
        header("Location: view_teacher_info.php?deleted=1");
        exit();
    } else {
        $message = "<div class='alert alert-danger text-center'>❌ فشل الحذف: " . $del_stmt->error . "</div>";
    }
    $del_stmt->close();
}

// جلب بيانات المعلم
$stmt = $conn->prepare("SELECT name, email, subject FROM users WHERE id = ? AND role = 'teacher'");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();
$stmt->close();

if (!$teacher) {
    die("❌ المعلم غير موجود.");
}

// عند الضغط على زر الحفظ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $new_name = $_POST['name'];
    $new_email = $_POST['email'];
    $new_subject = $_POST['subject'];

    $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, subject = ? WHERE id = ?");
    $update_stmt->bind_param("sssi", $new_name, $new_email, $new_subject, $teacher_id);

    if ($update_stmt->execute()) {
        $message = "<div class='alert alert-success text-center'>✅ تم تحديث بيانات المعلم بنجاح.</div>";
        $teacher['name'] = $new_name;
        $teacher['email'] = $new_email;
        $teacher['subject'] = $new_subject;
    } else {
        $message = "<div class='alert alert-danger text-center'>❌ حدث خطأ أثناء التحديث.</div>";
    }

    $update_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعديل بيانات المعلم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <script>
        function confirmDelete() {
            return confirm("هل أنت متأكد أنك تريد حذف هذا المعلم؟ لا يمكن التراجع.");
        }
    </script>
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4>✏️ تعديل بيانات المعلم</h4>
        </div>
        <div class="card-body">
            <?php echo $message; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">الاسم:</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($teacher['name']); ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">المادة:</label>
                    <input type="text" name="subject" value="<?php echo htmlspecialchars($teacher['subject']); ?>" class="form-control" required>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" name="update" class="btn btn-success">💾 حفظ التعديلات</button>
                    <button type="submit" name="delete" class="btn btn-danger" onclick="return confirmDelete()">🗑️ حذف المعلم</button>
                </div>
            </form>
        </div>
        <div class="card-footer text-center">
            <a href="view_teacher_info.php" class="btn btn-outline-secondary">🔙 العودة</a>
        </div>
    </div>
</div>
</body>
</html>
