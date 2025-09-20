<?php
require_once '../includes/config.php';

// Destroy session and redirect to login
session_destroy();
redirectTo('login.php');
?>