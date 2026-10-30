<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    logout_user();

    session_start();
    session_regenerate_id(true);
    set_flash('success', 'You have been logged out successfully.');
}

redirect(BASE_URL . '/login.php');
