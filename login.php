<?php

require_once __DIR__ . '/includes/auth.php';

// Redirect if already authenticated
if (is_logged_in()) {
    redirect(BASE_URL . '/dashboard.php');
    exit;
}

$page_title = 'Login';
$active     = '';
$errors     = [];
$username   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $errors[] = 'Please fill in both fields.';
    } else {
        // Use the pre-built helper function from auth.php
        $user = attempt_login($username, $password);

        if ($user) {
            login_user($user);
            
            set_flash('success', 'Welcome back, ' . $user['username'] . '!');
            redirect(BASE_URL . '/dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid username or password.';
        }
    }
}
require __DIR__ . '/includes/header.php';
?>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger py-2 small">
    <?= e($errors[0]) ?>
  </div>
<?php endif; ?>

<h1 class="text-center mb-3">Login</h1>
<form action="login.php" method="POST"
      class="needs-validation-ajax styled-form p-4 mx-auto shadow-sm"
      id="loginForm"
      novalidate>
        <?= csrf_field() ?>

<div class="mb-3">
    <label for="username" class="form-label">Username or Email</label>
    <input
        type="text"
        id="username"
        name="username"
        class="form-control"
        placeholder="Enter username or Email"
        value="<?= e($username) ?>"
        data-validate="identifier"
        required
    />
</div>

<div class="mb-3">
    <label for="password" class="form-label">Password</label>
    <input
        type="password"
        id="password"
        name="password"
        class="form-control"
        placeholder="Enter password"
        data-validate="login_password"
        required
    />
</div>

<button type="submit" class="btn btn-submit w-100 mt-2">Login</button>      </form>

<p class="text-center mt-3 mb-0 small">
    Don't have an account?
    <a href="register.php" class="fw-semibold">Register now</a>
</p>

<?php require __DIR__ . '/includes/footer.php'; ?>