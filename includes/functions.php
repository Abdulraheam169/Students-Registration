<?php

if (!function_exists('db')) {
    require_once __DIR__ . '/../config/config.php';
}

// Secure session start

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Output escaping

// Escape a value for safe HTML output (prevents XSS)

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}


function upload_url(?string $file): string
{
    if ($file === null || $file === '' || !is_file(UPLOAD_DIR . '/' . $file)) {
        return BASE_URL . '/assets/img/default-avatar.svg';
    }
    return UPLOAD_URL . '/' . rawurlencode($file);
}

// Flash messages (one-shot session messages)

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

// CSRF protection

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(?string $token): bool
{
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

//Abort the request when the CSRF token is invalid

function csrf_require(): void
{
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        http_response_code(419);
        exit('Invalid or expired form token. Please reload the page and try again.');
    }
}

// Redirects

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

// Validation helpers


function validate_student(array $data, array $opts = []): array
{
    $errors  = [];
    $name    = trim($data['name'] ?? '');
    $email   = trim($data['email'] ?? '');
    $phone   = trim($data['phone'] ?? '');
    $address = trim($data['address'] ?? '');

    /*  name  */
    if ($name === '') {
        $errors['name'] = 'Full name is required.';
    } elseif (mb_strlen($name) < 3) {
        $errors['name'] = 'Name must be at least 3 characters long.';
    } elseif (mb_strlen($name) > 100) {
        $errors['name'] = 'Name cannot exceed 100 characters.';
    } elseif (!preg_match("/^[\p{L}\p{M}\s.'\-]+$/u", $name)) {
        $errors['name'] = 'Name may only contain letters, spaces, apostrophes, dots and hyphens.';
    }

    /*  email  */
    if ($email === '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address (e.g. name@example.com).';
    } elseif (mb_strlen($email) > 100) {
        $errors['email'] = 'Email cannot exceed 100 characters.';
    } elseif (!empty($opts['check_unique_email'])) {
        $sql  = 'SELECT id FROM students WHERE email = :email';
        $args = ['email' => $email];
        if (!empty($opts['id'])) {
            $sql .= ' AND id <> :id';
            $args['id'] = (int) $opts['id'];
        }
        $stmt = db()->prepare($sql);
        $stmt->execute($args);
        if ($stmt->fetch()) {
            $errors['email'] = 'This email address is already registered to another student.';
        }
    }

    /*  phone  */
    if ($phone === '') {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!preg_match('/^[0-9+\-\s()]{7,15}$/', $phone)) {
        $errors['phone'] = 'Phone must be 7-15 characters (digits, +, -, spaces or brackets).';
    }

    /*  address  */
    if ($address !== '' && mb_strlen($address) > 1000) {
        $errors['address'] = 'Address is too long (maximum 1000 characters).';
    }

    return $errors;
}

//Validate the user registration payload

function validate_user(array $data): array
{
    $errors   = [];
    $username = trim($data['username'] ?? '');
    $email    = trim($data['email'] ?? '');
    $password = (string) ($data['password'] ?? '');
    $confirm  = (string) ($data['confirm_password'] ?? '');

    /*  username  */
    if ($username === '') {
        $errors['username'] = 'Username is required.';
    } elseif (!preg_match('/^[A-Za-z0-9_.]{3,100}$/', $username)) {
        $errors['username'] = 'Username must be 3-100 characters: letters, numbers, dot or underscore only.';
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE username = :u');
        $stmt->execute(['u' => $username]);
        if ($stmt->fetch()) {
            $errors['username'] = 'This username is already taken.';
        }
    }

    /*  email  */
    if ($email === '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = :e');
        $stmt->execute(['e' => $email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'An account with this email already exists.';
        }
    }

    /*  password  */
    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters long.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors['password'] = 'Password must contain at least one letter and one number.';
    }

    /*  confirmation  */
    if ($confirm === '') {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    return $errors;
}

// rofile picture upload

function handle_upload(array $file, array &$errors): ?string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;                       // optional field, nothing uploaded
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors['profile_picture'] = 'Upload failed (error code ' . (int) $file['error'] . ').';
        return null;
    }

    if ($file['size'] > MAX_UPLOAD_BYTES) {
        $errors['profile_picture'] = 'Image is larger than ' . (MAX_UPLOAD_BYTES / 1048576) . ' MB.';
        return null;
    }

    /* Never trust the client: verify the real image type with getimagesize(). */
    $info = @getimagesize($file['tmp_name']);
    if ($info === false || !in_array($info['mime'], ALLOWED_MIME, true)) {
        $errors['profile_picture'] = 'Only JPG, PNG, GIF or WEBP images are allowed.';
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXT, true)) {
        $ext = match ($info['mime']) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            default      => 'jpg',
        };
    }

    if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0775, true) && !is_dir(UPLOAD_DIR)) {
        $errors['profile_picture'] = 'Upload folder is missing and could not be created.';
        return null;
    }

    $filename = 'student_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target   = UPLOAD_DIR . '/' . $filename;


    $moved = move_uploaded_file($file['tmp_name'], $target);
    if (!$moved && !is_uploaded_file($file['tmp_name']) && is_file($file['tmp_name'])) {
        $moved = @rename($file['tmp_name'], $target);
    }

    if (!$moved) {
        $errors['profile_picture'] = 'Could not save the uploaded file. Check folder permissions.';
        return null;
    }

    @chmod($target, 0644);
    return $filename;
}

// Delete an uploaded picture from disk (used on update/delete)

function delete_upload(?string $filename): void
{
    if (!$filename) {
        return;
    }
    $path = UPLOAD_DIR . '/' . basename($filename);
    if (is_file($path)) {
        @unlink($path);
    }
}

// Small formatting helpers

// Build an initials avatar text (e.g. "Ahmad Al-Salem" -> "AA")

function initials(string $name): string
{
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    $out   = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $out .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $out !== '' ? $out : '?';
}

// Format a MySQL timestamp for display

function nice_date(?string $value): string
{
    if (!$value) {
        return '—';
    }
    $ts = strtotime($value);
    return $ts ? date('d M Y, H:i', $ts) : '—';
}
