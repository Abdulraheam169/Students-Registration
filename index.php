<?php

require_once __DIR__ . '/includes/auth.php';

$page_title = 'Home';

if (is_logged_in()) {
    redirect(BASE_URL . '/dashboard.php');
    exit;
}

$stats = ['students' => 0, 'users' => 0];
try {
    $stats['students'] = (int) db()->query('SELECT COUNT(*) FROM students')->fetchColumn();
    $stats['users']    = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
} catch (Throwable $e) {
    // Fallback to zero counts if tables are not ready
}

require __DIR__ . '/includes/header.php';
?>

<div class="text-center my-4">
    <h1 class="mb-3">Welcome To The Best System For Students Records</h1>

    <div class="d-flex justify-content-center gap-3 my-4">
        <a href="register.php" class="btn btn-primary px-4 fw-semibold">Registration</a>
        <a href="login.php" class="btn btn-outline-dark px-4 fw-semibold">Login</a>
    </div>

    <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
        <div class="stat-item p-3 text-center shadow-sm">
            <span><?= (int) $stats['students'] ?></span>
            <label>Registered Students</label>
        </div>
        <div class="stat-item p-3 text-center shadow-sm">
            <span><?= (int) $stats['users'] ?></span>
            <label>System Accounts</label>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>