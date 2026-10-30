<?php

require_once __DIR__ . '/includes/auth.php';
require_login();

// Ensure the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/view_students.php');
    exit;
}

// Verify CSRF token
csrf_require();

$id = (int) ($_POST['id'] ?? 0);
if ($id > 0) {
    $stmt = db()->prepare('SELECT profile_picture FROM students WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $student = $stmt->fetch();

    if ($student && !empty($student['profile_picture'])) {
        delete_upload($student['profile_picture']);
    }

    $del = db()->prepare('DELETE FROM students WHERE id = :id');
    $del->execute(['id' => $id]);

    set_flash('success', 'Student record deleted successfully.');
}

redirect(BASE_URL . '/view_students.php');
exit;