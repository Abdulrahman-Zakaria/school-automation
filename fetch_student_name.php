<?php
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn = new mysqli("localhost", "root", "", "school_db");
    if ($conn->connect_error) {
        die("Connection failed");
    }

    $stmt = $conn->prepare("SELECT users.name FROM students JOIN users ON students.user_id = users.id WHERE students.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($name);
    if ($stmt->fetch()) {
        echo $name;
    }
    $stmt->close();
    $conn->close();
}
?>
