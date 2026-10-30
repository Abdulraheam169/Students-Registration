<?php

require_once __DIR__ . '/includes/auth.php';
require_login();

$page_title = 'Dashboard';
$active     = 'dashboard';
$user       = current_user();

$totalStudents = (int) db()->query('SELECT COUNT(*) FROM students')->fetchColumn();

$stmt = db()->prepare('SELECT COUNT(*) FROM students WHERE created_at >= (NOW() - INTERVAL 30 DAY)');
$stmt->execute();
$newThisMonth = (int) $stmt->fetchColumn();

$withPicture = (int) db()->query(
    "SELECT COUNT(*) FROM students WHERE profile_picture IS NOT NULL AND profile_picture <> ''"
)->fetchColumn();

$latest = db()->query(
    'SELECT id, name, email, phone, address, profile_picture, created_at
       FROM students ORDER BY id DESC LIMIT 4'
)->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="text-center mb-4">
    <h1>Dashboard</h1>
    <p class="page-sub mb-0">Welcome back, <strong><?= e($user['username']) ?></strong>!</p>
</div>

<!-- Summary metrics -->
<div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
    <div class="stat-badge p-3 text-center shadow-sm">
        <strong><?= $totalStudents ?></strong>
        <span>Total Students</span>
    </div>
    <div class="stat-badge p-3 text-center shadow-sm">
        <strong><?= $newThisMonth ?></strong>
        <span>New This Month</span>
    </div>
    <div class="stat-badge p-3 text-center shadow-sm">
        <strong><?= $withPicture ?></strong>
        <span>With Pictures</span>
    </div>
</div>

<h1 class="text-center mb-3">Recently Added Students</h1>

<div class="cards d-flex flex-wrap justify-content-evenly align-items-stretch gap-4 p-4">
    <?php if (empty($latest)): ?>
        <div class="empty text-center py-5 w-100">No student records found yet.</div>
    <?php else: ?>
        <?php foreach ($latest as $s): ?>
            <div class="box d-flex flex-column justify-content-between p-3 my-3">
                <?php if (!empty($s['profile_picture'])): ?>
                    <img src="<?= e(upload_url($s['profile_picture'])) ?>" alt="Profile" class="profile" />
                <?php else: ?>
                    <img src="images/profile.png" alt="Profile" class="profile" />
                <?php endif; ?>

                <div>
                    <div class="name mb-1"><?= e($s['name']) ?></div>
                    <div class="id mb-2">ID: #<?= (int) $s['id'] ?></div>
                    <div class="email mb-1"><?= e($s['email']) ?></div>
                    <div class="phone mb-1"><?= e($s['phone']) ?></div>
                    <div class="address mb-3"><?= e($s['address'] ?: 'No address specified') ?></div>
                </div>

                <div class="d-flex justify-content-center gap-2 mt-auto pt-2">
                    <a href="update_student.php?id=<?= (int) $s['id'] ?>" class="btn btn-primary btn-sm">Update</a>
                    <a href="student_info.php?id=<?= (int) $s['id'] ?>" class="btn btn-success btn-sm">Info</a>
                    <form action="delete_student.php" method="POST" style="display:inline-flex; margin:0; vertical-align:middle;" onsubmit="return confirm('Delete this student?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" style="border:none;">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>