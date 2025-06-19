<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    echo json_encode(["message" => "❌ صلاحية غير مسموح بها."]);
    exit();
}

$teacher_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['qr_code']) || !isset($data['subject'])) {
        echo json_encode(["message" => "❌ البيانات غير مكتملة (QR أو المادة مفقودة)."]);
        exit();
    }

    $qr_code = base64_decode($data['qr_code']);
    $subject = $data['subject'];
    $date = date('Y-m-d');

    $conn = new mysqli("localhost", "root", "", "school_db");
    if ($conn->connect_error) {
        echo json_encode(["message" => "❌ فشل الاتصال بقاعدة البيانات."]);
        exit();
    }

    // جلب الطالب حسب QR
    $stmt = $conn->prepare("SELECT id FROM students WHERE qr_code = ?");
    $stmt->bind_param("s", $qr_code);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $student = $res->fetch_assoc();
        $student_id = $student['id'];

        // تحقق من تسجيل الحضور لنفس المادة في نفس اليوم
        $check = $conn->prepare("SELECT * FROM attendance WHERE student_id = ? AND date = ? AND subject = ?");
        $check->bind_param("iss", $student_id, $date, $subject);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            // تسجيل الحضور
            $insert = $conn->prepare("INSERT INTO attendance (student_id, date, status, subject, teacher_id) VALUES (?, ?, 'present', ?, ?)");
            $insert->bind_param("issi", $student_id, $date, $subject, $teacher_id);
            $insert->execute();
            echo json_encode(["message" => "✅ تم تسجيل حضور الطالب في مادة $subject."]);
        } else {
            echo json_encode(["message" => "⚠️ تم تسجيل الحضور مسبقًا لهذه المادة اليوم."]);
        }
    } else {
        echo json_encode(["message" => "❌ رمز QR غير صالح."]);
    }

    $conn->close();
}
?>
