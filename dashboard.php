<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$role = $user['role'];
$name = $user['name'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <script>
        function fetchStudentName() {
            const studentId = document.getElementById("student_id_input").value;
            const resultDiv = document.getElementById("student_name_result");

            if (studentId === '') {
                resultDiv.innerHTML = '';
                return;
            }

            fetch("fetch_student_name.php?id=" + studentId)
                .then(response => response.text())
                .then(data => {
                    resultDiv.innerHTML = data
                        ? '👦 اسم الطالب: <strong>' + data + '</strong>'
                        : '⚠️ لم يتم العثور على الطالب';
                });
        }
    </script>
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h3>🎓 مرحباً، <?php echo htmlspecialchars($name); ?> (<?php echo $role; ?>)</h3>
        </div>
        <div class="card-body">

            <?php if ($role === 'admin'): ?>
                <h5>خيارات المدير:</h5>
                <ul class="list-group mb-3">
                    <li class="list-group-item"><a href="add_student.php">➕ إضافة طالب</a></li>
                    <li class="list-group-item"><a href="add_teacher.php">➕ إضافة معلم</a></li>
                    <li class="list-group-item"><a href="add_parent.php">➕ إضافة ولي امر</a></li>
                    <li class="list-group-item"><a href="add_schedule.php">📅 إضافة جدول عام</a></li>
                    <li class="list-group-item"><a href="add_teacher_schedule.php">📘 إضافة جدول لمعلم</a></li>
                    <li class="list-group-item"><a href="view_student_admin.php">👨‍🎓 عرض بيانات الطالب</a></li>
                    <li class="list-group-item"><a href="view_teacher_info.php">👨‍🏫 عرض بيانات المعلم</a></li>
                    <li class="list-group-item"><a href="edit_admin.php">🛠️ تعديل بيانات المدير</a></li>
                </ul>

            <?php elseif ($role === 'teacher'): ?>
                <h5>خيارات المعلم:</h5>
                <ul class="list-group">
                    <li class="list-group-item"><a href="add_grade.php">📝 إضافة درجات</a></li>
                    <li class="list-group-item"><a href="view_teacher_schedule.php">📖 عرض جدولي الدراسي</a></li>
                    <li class="list-group-item"><a href="take_attendance.php">✅ تسجيل الحضور</a></li>
                    <li class="list-group-item"><a href="view_attendance_by_teacher.php">📋 عرض الحضور للطلاب</a></li>
                    <li class="list-group-item"><a href="teacher_profile.php">👤 عرض بياناتي وتعديل كلمة المرور</a></li>
                    <li class="list-group-item"><a href="search_student_info.php">🔎 البحث عن طالب (درجات + حضور)</a></li>
                </ul>

            <?php elseif ($role === 'parent'): ?>
                <h5>استعلام عن بيانات الطالب:</h5>
                <form method="get" action="view_student_info.php">
                    <div class="mb-3">
                        <label class="form-label">أدخل رقم الطالب (ID):</label>
                        <input type="number" name="student_id" id="student_id_input" class="form-control" oninput="fetchStudentName()" required>
                        <div id="student_name_result" class="form-text text-success mt-2"></div>
                    </div>
                    <button type="submit" class="btn btn-outline-primary">عرض البيانات</button>
                </form>

            <?php elseif ($role === 'student'): ?>
                <h5>خيارات الطالب:</h5>
                <ul class="list-group">
                    <li class="list-group-item"><a href="view_schedule.php">📅 عرض الجدول الدراسي</a></li>
                    <li class="list-group-item"><a href="view_grades.php">📝 عرض الدرجات</a></li>
                    <li class="list-group-item"><a href="view_attendance.php">📋 عرض الحضور</a></li>
                    <li class="list-group-item"><a href="student_card.php">🎫 عرض بطاقة الطالب</a></li>
                </ul>

            <?php else: ?>
                <div class="alert alert-warning">⚠️ نوع المستخدم غير معروف.</div>
            <?php endif; ?>
        </div>
        <div class="card-footer text-center">
            <a href="logout.php" class="btn btn-danger">🚪 تسجيل الخروج</a>
        </div>
    </div>
</div>
</body>
</html>
