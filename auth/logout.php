<?php
require_once '../config/database.php';

// Destroy session and redirect to login
session_destroy();
redirect('../index.php');
?>