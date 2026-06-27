<?php
session_start();
session_destroy();
header("Location: /student_portal/index.php");
exit();
?>

