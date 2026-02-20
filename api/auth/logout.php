<?php
// File: api/auth/logout.php
session_start();
session_destroy();
header("Location: ../../login.html");
exit();
?>