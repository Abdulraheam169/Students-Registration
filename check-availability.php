<?php
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

$type  = trim((string) ($_GET['type'] ?? ''));
$value = trim((string) ($_GET['value'] ?? ''));

if ($value === '') {
    echo json_encode(['exists' => false]);
    exit;
}

$exists  = false;
$message = '';

try {
    if ($type === 'username') {
        $stmt = db()->prepare('SELECT id FROM users WHERE username = :val LIMIT 1');
        $stmt->execute(['val' => $value]);
        if ($stmt->fetch()) {
            $exists  = true;
            $message = 'This username is already taken.';
        }
    } elseif ($type === 'email') {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = :val LIMIT 1');
        $stmt->execute(['val' => $value]);
        if ($stmt->fetch()) {
            $exists  = true;
            $message = 'This email is already registered.';
        }
    } elseif ($type === 'student_email') {
        // Exclude current student ID when updating an existing record
        $ignoreId = (int) ($_GET['ignore_id'] ?? 0);
        $stmt = db()->prepare('SELECT id FROM students WHERE email = :val AND id <> :id LIMIT 1');
        $stmt->execute(['val' => $value, 'id' => $ignoreId]);
        if ($stmt->fetch()) {
            $exists  = true;
            $message = 'A student with this email already exists.';
        }
    }
} catch (Throwable $e) {
    // Ignore DB errors here and defer to backend form submission
}

echo json_encode([
    'exists'  => $exists,
    'message' => $message,
]);