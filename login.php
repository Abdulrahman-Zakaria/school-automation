<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli("localhost", "root", "", "school_db");

    if ($conn->connect_error) {
        die("فشل الاتصال: " . $conn->connect_error);
    }

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "❌ كلمة المرور غير صحيحة";
        }
    } else {
        $error = "❌ البريد الإلكتروني غير موجود";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">
<div class="container">
    <div class="card shadow text-center mx-auto" style="max-width: 450px;">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">🔐 تسجيل الدخول</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="mb-3 text-start">
                    <label class="form-label">📧 البريد الإلكتروني:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3 text-start">
                    <label class="form-label">🔒 كلمة المرور:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success w-100">تسجيل الدخول</button>
            </form>
        </div>
        <div class="card-footer text-center">
            <a href="index.php" class="btn btn-outline-secondary">العودة إلى الصفحة الرئيسية</a>
        </div>
    </div>
</div>
</body>
</html>
