<?php
session_start();

// Clear all session variables
session_unset();
session_destroy();

// Redirect to the main login page
header("Location: ../login.php"); // path to your main login.php
exit();
?>
