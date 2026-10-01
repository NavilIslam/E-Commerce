<?php
require_once __DIR__ . '/../../config/config.php';

Auth::logout();

if (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
    jsonSuccess('Logged out successfully', ['redirect' => BASE_URL . '/login.php']);
}

redirect(BASE_URL . '/login.php');
