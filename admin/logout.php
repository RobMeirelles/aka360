<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';

// Cerrar sesión usando la función de logout
logout();

// Redirigir al login
header('Location: login.php');
exit;
?> 