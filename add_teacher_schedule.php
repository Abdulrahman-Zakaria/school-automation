<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "school_db");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$message = "";

// استعلام لجلب جميع المعلمين
$teachers = $conn->query("SELECT id, name FROM users WHERE role = 'teacher'");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_POST['teacher_id'];
    $class_name = $_POST['class_name']; // تمثل رمز الصف (مثل A أو B)
    $day = $_POST['day_of_week'];
    $start = $_POST['time_start'];
    $end = $_POST['time_end'];

    $stmt = $conn->prepare("INSERT INTO teacher_schedule (teacher_id, class_name, day_of_week, time_start, time_end) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $teacher_id, $class_name, $day, $start, $end);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>✅ تم إضافة الجدول للمعلم بنجاح.</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ خطأ أثناء الإضافة: " . $stmt->error . "</div>";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة جدول للمعلم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4>➕ إضافة جدول مخصص لمعلم</h4>
        </div>
        <div class="card-body">
            <?php echo $message; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">اختر المعلم:</label>
                    <select name="teacher_id" class="form-select" required>
                        <option value="">-- اختر --</option>
                        <?php while ($row = $teachers->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">اسم الصف</label>
                    <input type="text" name="class_name" class="form-control" required>
                </div>


                <div class="mb-3">
                    <label class="form-label">اليوم:</label>
                    <select name="day_of_week" class="form-select" required>
                        <option value="السبت">السبت</option>
                        <option value="الأحد">الأحد</option>
                        <option value="الاثنين">الاثنين</option>
                        <option value="الثلاثاء">الثلاثاء</option>
                        <option value="الأربعاء">الأربعاء</option>
                        <option value="الخميس">الخميس</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">من الساعة:</label>
                        <input type="time" name="time_start" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">إلى الساعة:</label>
                        <input type="time" name="time_end" class="form-control" required>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success w-50">➕ إضافة</button>
                </div>
            </form>
        </div>
        <div class="card-footer text-center">
            <a href="dashboard.php" class="btn btn-outline-secondary">🔙 العودة إلى لوحة التحكم</a>
        </div>
    </div>
</div>
</body>
</html>
