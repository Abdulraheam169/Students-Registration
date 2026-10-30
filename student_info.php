<?php

require_once __DIR__ . '/includes/auth.php';
require_login();

$page_title = 'Student Info';
$id = (int) ($_GET['id'] ?? 0);

$stmt = db()->prepare('SELECT * FROM students WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$student = $stmt->fetch();

if (!$student) {
    set_flash('danger', 'Student record not found.');
    redirect(BASE_URL . '/view_students.php');
    exit;
}

require __DIR__ . '/includes/header.php';
?>

<div class="text-center mb-4">
    <h1>Student Info</h1>
    <p class="page-sub mb-0">Detailed profile view for student record #<?= (int) $student['id'] ?>.</p>
</div>

<div class="cards d-flex justify-content-center align-items-center py-5 px-3">
    <div class="box d-flex flex-column justify-content-between p-3 my-3">
        <?php if (!empty($student['profile_picture'])): ?>
            <img src="<?= e(upload_url($student['profile_picture'])) ?>" alt="Profile" class="profile" />
        <?php else: ?>
            <img src="images/profile.png" alt="Profile" class="profile" />
        <?php endif; ?>

        <div>
            <div class="name mb-1"><?= e($student['name']) ?></div>
            <div class="id mb-2">ID: #<?= (int) $student['id'] ?></div>
            <div class="email mb-1">Email: <?= e($student['email']) ?></div>
            <div class="phone mb-1">Phone: <?= e($student['phone']) ?></div>
            <div class="address mb-3">Address: <?= e($student['address'] ?: 'No address specified') ?></div>
        </div>

        <div class="d-flex justify-content-center gap-2 mt-auto pt-2">
            <a href="update_student.php?id=<?= (int) $student['id'] ?>" class="btn btn-primary btn-sm">Update</a>
            <a href="view_students.php" class="btn btn-success btn-sm">Back</a>
            <form action="delete_student.php" method="POST" style="display:inline-flex; margin:0; vertical-align:middle;" onsubmit="return confirm('Are you sure you want to delete this student?');">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $student['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm" style="border:none;">Delete</button>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>