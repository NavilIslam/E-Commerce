<?php
require_once __DIR__ . '/../config/config.php';

Auth::logout();
setFlash('success', 'Logged out of admin session.');
redirect(ADMIN_URL . '/login.php');
