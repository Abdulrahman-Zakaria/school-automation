<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_name = $_SESSION['user']['name'];
$subject = $_SESSION['user']['subject']; // تأكد أن المعلم له حقل subject في جدول users
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الحضور عبر QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow text-center">
        <div class="card-header bg-primary text-white">
            <h4>📷 تسجيل حضور الطلاب</h4>
            <p>المعلم: <strong><?= htmlspecialchars($teacher_name) ?></strong></p>
        </div>
        <div class="card-body">
            <p>✅ سيتم تسجيل الحضور للمادة: <strong class="text-info"><?= htmlspecialchars($subject) ?></strong></p>

            <div id="reader" style="width: 300px; margin: auto;"></div>
            <div id="result" class="mt-3 text-success"></div>
        </div>
        <div class="card-footer">
            <a href="dashboard.php" class="btn btn-outline-secondary">🔙 العودة إلى لوحة التحكم</a>
        </div>
    </div>
</div>

<script>
    function onScanSuccess(decodedText, decodedResult) {
        html5QrcodeScanner.clear().then(() => {
            document.getElementById("result").innerHTML = "جارٍ التحقق من الرمز...";

            fetch("take_attendance_api.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    qr_code: decodedText,
                    subject: <?= json_encode($subject) ?>  // نرسل المادة تلقائياً من السيرفر
                })
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById("result").innerHTML = data.message;
            });
        }).catch(err => {
            console.error("فشل إيقاف القارئ:", err);
        });
    }

    const html5QrcodeScanner = new Html5QrcodeScanner(
        "reader", { fps: 10, qrbox: 250 });
    html5QrcodeScanner.render(onScanSuccess);
</script>

</body>
</html>
