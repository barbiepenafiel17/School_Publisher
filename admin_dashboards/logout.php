<?php

session_start(); // Start the session
include($_SERVER['DOCUMENT_ROOT'] . '/login.php');
// Destroy the session
session_unset();
session_destroy();

// Redirect to login page
header("login.php");
exit();
?>
