<?php
// logout.php - User logout
session_start();
session_destroy();
header('Location: index.php');
exit;
?>
