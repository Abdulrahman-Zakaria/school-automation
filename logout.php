<?php
// logout.php - تسجيل الخروج من النظام
session_start();
session_unset();
session_destroy();
header("Location: index.php");
exit();
