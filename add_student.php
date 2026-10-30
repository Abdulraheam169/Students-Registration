<?php

require_once __DIR__ . '/includes/auth.php';
require_login();

$page_title = 'Add Student';
$active = 'add';
$errors = [];
$name    = '';
$email   = '';
$phone   = '';
$address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $name    = trim((string) ($_POST['name'] ?? ''));
    $email   = trim((string) ($_POST['email'] ?? ''));
    $phone   = trim((string) ($_POST['phone'] ?? ''));
    $address = trim((string) ($_POST['address'] ?? ''));

    if ($name === '' || $email === '' || $phone === '') {
        $errors[] = 'Please fill in name, email, and phone number.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        // Check for duplicate email
        $stmt = db()->prepare('SELECT id FROM students WHERE email = :e LIMIT 1');
        $stmt->execute(['e' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'A student with this email already exists.';
        } else {
            // Handle image upload using the secure helper function
            $profilePicture = null;
            if (isset($_FILES['profile_picture'])) {
                $profilePicture = handle_upload($_FILES['profile_picture'], $errors);
            }

            // If there was an upload error, show it and do not save
            if (isset($errors['profile_picture'])) {
                // The error is already in the array, so we just skip saving
            } else {
                // save student 
                $stmt = db()->prepare(
                    'INSERT INTO students (name, email, phone, address, profile_picture) 
                     VALUES (:name, :email, :phone, :address, :pic)'
                );
                $stmt->execute([
                    'name'    => $name,
                    'email'   => $email,
                    'phone'   => $phone,
                    'address' => $address,
                    'pic'     => $profilePicture,
                ]);

                set_flash('success', 'Student added successfully!');
                redirect(BASE_URL . '/view_students.php');
                exit;
            }
        }
    }
}
require __DIR__ . '/includes/header.php';
?>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger py-2 small">
    <?php foreach ($errors as $error): ?>
        <?= e($error) ?><br>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<h1 class="text-center mb-3">Add Student</h1>
<form action="add_student.php" method="POST"
      enctype="multipart/form-data"
      class="needs-validation-ajax styled-form p-4 mx-auto shadow-sm"
      id="studentForm"
      novalidate>
        <?= csrf_field() ?>

<div class="mb-3">
    <label for="name" class="form-label">Student Name</label>
    <input
        type="text"
        id="name"
        name="name"
        class="form-control"
        placeholder="Enter student name"
        value="<?= e($name) ?>"
        data-validate="name"
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
        placeholder="Enter student email"
        value="<?= e($email) ?>"
        data-validate="student_email"
        required
    />
</div>

<div class="mb-3">
    <label for="phone" class="form-label">Phone</label>
    <input
        type="text"
        id="phone"
        name="phone"
        class="form-control"
        placeholder="Enter phone number"
        value="<?= e($phone) ?>"
        data-validate="phone"
        required
    />
</div>

<div class="mb-3">
    <label for="address" class="form-label">Address</label>
    <input
        type="text"
        id="address"
        name="address"
        class="form-control"
        placeholder="Enter address"
        value="<?= e($address) ?>"
        data-validate="address"
    />
</div>

<div class="mb-3">
    <label for="profile_picture" class="form-label">Profile Picture</label>
    <input
        type="file"
        id="profile_picture"
        name="profile_picture"
        class="form-control"
        accept="image/*"
        data-validate="profile_picture"
    />
</div>

<button type="submit" class="btn btn-submit w-100 mt-2">Add Student</button>      </form>

<p class="text-center mt-3 mb-0 small">
    <a href="view_students.php" class="fw-semibold">&larr; Back to Students List</a>
</p>

<?php require __DIR__ . '/includes/footer.php'; ?>