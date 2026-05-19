<?php
require_once __DIR__ . '/../includes/functions.php';
session_destroy();
session_start();
flash('Logged out successfully.');
redirect(baseUrl('auth/login.php'));
