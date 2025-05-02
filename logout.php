<?php
session_start(); // Start session to destroy it
session_destroy();
session_unset();

// Redirect to login page with success message
header("Location: login.php?success=You have been logged out successfully.");
exit;
?>