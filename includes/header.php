<?php

require_once __DIR__ . '/auth.php';

$page_title = $page_title ?? SITE_NAME;
$active     = $active ?? '';
$user       = current_user();
$self       = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Student Registration System.">
    <title><?= e($page_title) ?> &middot; <?= e(SITE_NAME) ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <link rel="stylesheet" href="master.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎓</text></svg>">
</head>

<body class="d-flex flex-column min-vh-100" data-base-url="<?= e(BASE_URL) ?>">

<header class="container mt-3">
    <nav class="custom-nav d-flex flex-wrap justify-content-around align-items-center py-2 px-3 shadow-sm">
        <ul class="list-unstyled d-flex flex-wrap justify-content-around align-items-center w-100 m-0 gap-2">
            <?php if (is_logged_in()): ?>
                <li>
                    <a href="<?= e(BASE_URL) ?>/dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                </li>
                <li>
                    <a href="<?= e(BASE_URL) ?>/view_students.php"><i class="bi bi-people me-1"></i>Students</a>
                </li>
                <li>
                    <a href="<?= e(BASE_URL) ?>/add_student.php"><i class="bi bi-person-plus me-1"></i>Add Student</a>
                </li>
                <li>
                    <a href="<?= e(BASE_URL) ?>/logout.php" class="logout-link" onclick="return confirm('Do you really want to log out?');">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                </li>
            <?php else: ?>
                <li>
                    <a href="<?= e(BASE_URL) ?>/index.php"><i class="bi bi-house me-1"></i>Home</a>
                </li>
                <li>
                    <a href="<?= e(BASE_URL) ?>/login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
                </li>
                <li>
                    <a href="<?= e(BASE_URL) ?>/register.php"><i class="bi bi-person-check me-1"></i>Register</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<main class="flex-grow-1 py-4">
    <div class="container">
        <?php foreach (get_flashes() as $flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle me-1"></i><?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>