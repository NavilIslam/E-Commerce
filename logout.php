<?php
require_once __DIR__ . '/config/config.php';

Auth::logout();
setFlash('success', 'You have been logged out securely.');
redirect(BASE_URL . '/login.php');
