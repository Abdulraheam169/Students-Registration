<?php

require_once __DIR__ . '/includes/auth.php';

// Redirect if already authenticated
if (is_logged_in()) {
    redirect(BASE_URL . '/dashboard.php');
    exit;
}

$page_title = 'Register';
$active = '';
$errors = [];
$username = '';
$email    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $username = trim((string) ($_POST['username'] ?? ''));
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['confirm_password'] ?? '');


    // Form validations
    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        $errors[] = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    } else {
        // Check for duplicate username or email
        $stmt = db()->prepare('SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1');
        $stmt->execute(['u' => $username, 'e' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Username or email already taken.';
        } else {
            // create new user account
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare('INSERT INTO users (username, email, password) VALUES (:u, :e, :p)');
            $stmt->execute([
                'u' => $username,
                'e' => $email,
                'p' => $hash,
            ]);

            set_flash('success', 'Registration successful! You may now log in.');
            redirect(BASE_URL . '/login.php');
            exit;
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

   <h1 class="text-center mb-3">Register</h1>
<form action="register.php" method="POST"
      class="needs-validation-ajax styled-form p-4 mx-auto shadow-sm"
      id="registerForm"
      novalidate>
        <?= csrf_field() ?>

<div class="mb-3">
    <label for="username" class="form-label">Username</label>
    <input
        type="text"
        id="username"
        name="username"
        class="form-control"
        placeholder="Enter username"
        value="<?= e($username) ?>"
        data-validate="username"
        required
    />
</div>

<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input
        type="email"
        id="email"
        name="email"
        class="form-control"
        placeholder="Enter email"
        value="<?= e($email) ?>"
        data-validate="email"
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
        data-validate="password"
        required
    />
</div>

<div class="mb-3">
    <label for="confirm_password" class="form-label">Confirm Password</label>
    <input
        type="password"
        id="confirm_password"
        name="confirm_password"
        class="form-control"
        placeholder="Confirm password"
        data-validate="confirm_password"
        data-match="#password"
        required
    />
</div>

<button type="submit" class="btn btn-submit w-100 mt-2">Sign Up</button>      </form>

<p class="text-center mt-3 mb-0 small">
    Already have an account?
    <a href="login.php" class="fw-semibold">Log in</a>
</p>

<?php require __DIR__ . '/includes/footer.php'; ?>