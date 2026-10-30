<?php

require_once __DIR__ . '/functions.php';

define('SESSION_TIMEOUT', 1800);

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        logout_user();
        set_flash('warning', 'Your session expired after 30 minutes of inactivity. Please log in again.');
        redirect(BASE_URL . '/login.php');
    }
    $_SESSION['last_activity'] = time();
}

// State checks

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'       => (int) $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'email'    => $_SESSION['user_email'] ?? '',
    ];
}

// Actions

// Create the session for a user row coming from the database.

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']      = (int) $user['id'];
    $_SESSION['username']     = $user['username'];
    $_SESSION['user_email']   = $user['email'];
    $_SESSION['last_activity'] = time();
}

function attempt_login(string $identifier, string $password): ?array
{

    $stmt = db()->prepare(
        'SELECT id, username, email, password FROM users WHERE username = :username
         UNION
         SELECT id, username, email, password FROM users WHERE email = :email
         LIMIT 1'
    );
    $stmt->execute(['username' => $identifier, 'email' => $identifier]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $up = db()->prepare('UPDATE users SET password = :p WHERE id = :id');
            $up->execute(['p' => password_hash($password, PASSWORD_DEFAULT), 'id' => $user['id']]);
        }
        return $user;
    }

    return null;
}

//  Destroy the session completely.

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
    }
    session_destroy();
}

// Guards

// Redirect guests to the login page (with a "return to" hint)

function require_login(): void
{
    if (!is_logged_in()) {

        $target = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $prefix = BASE_URL . '/';
        if ($target !== '' && str_starts_with($target, $prefix)) {
            $_SESSION['redirect_after_login'] = $target;   // already project-relative
        }
        set_flash('warning', 'Please log in to access that page.');
        redirect(BASE_URL . '/login.php');
    }
}

// Keep logged-in users away from login/register page
function require_guest(): void
{
    if (is_logged_in()) {
        redirect(BASE_URL . '/dashboard.php');
    }
}
