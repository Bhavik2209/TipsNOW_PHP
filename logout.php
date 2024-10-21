<?php
require_once 'auth.php';
session_start();
logout();
header('Location: index.php');
?>