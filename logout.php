<?php
require_once __DIR__ . '/php/auth.php';
cdaLogout();
header('Location: login.php');
exit;
