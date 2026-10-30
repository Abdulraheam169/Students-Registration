<?php

require_once __DIR__ . '/includes/auth.php';
require_login();

$page_title = 'Students Page';
$search = trim((string) ($_GET['search'] ?? ''));
// Sorting settings
$allowedSorts = ['id_desc', 'name_asc', 'name_desc', 'email_asc'];
$sort = $_GET['sort'] ?? 'id_desc';
if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'id_desc'; // Default
}

// Map sort to SQL ORDER BY
$orderBy = match ($sort) {
    'name_asc' => 'name ASC',
    'name_desc' => 'name DESC',
    'email_asc' => 'email ASC',
    default => 'id DESC',
};

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $limit;

// Calculate total students and fetch paginated data
if ($search !== '') {
    $searchTerm = "%{$search}%";

    $countStmt = db()->prepare('SELECT COUNT(*) FROM students WHERE name LIKE :s1 OR email LIKE :s2 OR phone LIKE :s3');
    $countStmt->execute(['s1' => $searchTerm, 's2' => $searchTerm, 's3' => $searchTerm]);
    $totalStudents = (int) $countStmt->fetchColumn();

    $stmt = db()->prepare("SELECT * FROM students WHERE name LIKE :s1 OR email LIKE :s2 OR phone LIKE :s3 ORDER BY $orderBy LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':s1', $searchTerm);
    $stmt->bindValue(':s2', $searchTerm);
    $stmt->bindValue(':s3', $searchTerm);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $students = $stmt->fetchAll();
} else {
    $totalStudents = (int) db()->query('SELECT COUNT(*) FROM students')->fetchColumn();

    $stmt = db()->prepare("SELECT * FROM students ORDER BY $orderBy LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $students = $stmt->fetchAll();
}

$totalPages = (int) ceil($totalStudents / $limit);

require __DIR__ . '/includes/header.php';
?>

<h1 class="text-center mb-3">All Students</h1>

<!-- Search toolbar -->
<div class="toolbar mb-4">
    <form action="view_students.php" method="GET" class="search-form d-flex flex-wrap align-items-center gap-2 p-3 rounded shadow-sm">
        <input
            type="text"
            name="search"
            class="form-control flex-grow-1"
            placeholder="Search students by name, email, or phone..."
            value="<?= e($search) ?>"
        />
        <!-- Sorting Dropdown -->
        <select name="sort" class="form-select w-auto" onchange="this.form.submit()">
            <option value="id_desc" <?= $sort === 'id_desc' ? 'selected' : '' ?>>Newest First</option>
            <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
            <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
            <option value="email_asc" <?= $sort === 'email_asc' ? 'selected' : '' ?>>Email (A-Z)</option>
        </select>
        <button type="submit" class="btn search-btn px-4">Search</button>
        <?php if ($search !== ''): ?>
            <a href="view_students.php" class="btn btn-danger px-3">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Students grid -->
<div class="cards d-flex flex-wrap justify-content-evenly align-items-stretch gap-4 p-4">
    <?php if (empty($students)): ?>
        <div class="empty text-center py-5 w-100">No student records found.</div>
    <?php else: ?>
        <?php foreach ($students as $student): ?>
            <div class="box d-flex flex-column justify-content-between p-3 my-3">
                <?php if (!empty($student['profile_picture'])): ?>
                    <img src="<?= e(upload_url($student['profile_picture'])) ?>" alt="Profile" class="profile" />
                <?php else: ?>
                    <img src="images/profile.png" alt="Profile" class="profile" />
                <?php endif; ?>

                <div>
                    <div class="name mb-1"><?= e($student['name']) ?></div>
                    <div class="id mb-2">ID: #<?= (int) $student['id'] ?></div>
                    <div class="email mb-1"><?= e($student['email']) ?></div>
                    <div class="phone mb-1"><?= e($student['phone']) ?></div>
                    <div class="address mb-3"><?= e($student['address'] ?: 'No address specified') ?></div>
                </div>

                <div class="d-flex justify-content-center gap-2 mt-auto pt-2">
                    <a href="update_student.php?id=<?= (int) $student['id'] ?>" class="btn btn-primary btn-sm">Update</a>
                    <a href="student_info.php?id=<?= (int) $student['id'] ?>" class="btn btn-success btn-sm">Info</a>
                    <form action="delete_student.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this student?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $student['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" style="border:none;">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Pagination controls -->
<?php if ($totalPages > 1): ?>
    <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&sort=<?= urlencode($sort) ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>" class="page-btn">&laquo; Previous</a>        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&sort=<?= urlencode($sort) ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
               class="page-btn <?= $i === $page ? 'active' : '' ?>">
               <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&sort=<?= urlencode($sort) ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>" class="page-btn">Next &raquo;</a>        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>