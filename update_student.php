<?php

require_once __DIR__ . '/includes/auth.php';
require_login();

$page_title = 'Update Student Info';
$active = 'students';
$errors = [];
$id = (int) ($_GET['id'] ?? 0);

// Fetch current record
$stmt = db()->prepare('SELECT * FROM students WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$student = $stmt->fetch();

if (!$student) {
    set_flash('danger', 'Student not found.');
    redirect(BASE_URL . '/view_students.php');
    exit;
}

$name    = $student['name'];
$email   = $student['email'];
$phone   = $student['phone'];
$address = $student['address'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $name    = trim((string) ($_POST['name'] ?? ''));
    $email   = trim((string) ($_POST['email'] ?? ''));
    $phone   = trim((string) ($_POST['phone'] ?? ''));
    $address = trim((string) ($_POST['address'] ?? ''));

    if ($name === '' || $email === '' || $phone === '') {
        $errors[] = 'Name, email, and phone are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        // Check for duplicate email excluding current student
        $stmt = db()->prepare('SELECT id FROM students WHERE email = :e AND id <> :id LIMIT 1');
        $stmt->execute(['e' => $email, 'id' => $id]);
        if ($stmt->fetch()) {
            $errors[] = 'This email is already registered to another student.';
        } else {
            $profilePicture = $student['profile_picture'];

            // Handle image upload using the secure helper function
            if (isset($_FILES['profile_picture'])) {
                $newPicture = handle_upload($_FILES['profile_picture'], $errors);
                if ($newPicture) {
                    // Delete the old picture from the server
                    if (!empty($profilePicture)) {
                        delete_upload($profilePicture);
                    }
                    $profilePicture = $newPicture;
                }
            }

            // If there was an upload error, stop and show it
            if (isset($errors['profile_picture'])) {
                // Do not proceed with database update
            } else {
                // Save changes
                $stmt = db()->prepare(
                    'UPDATE students 
                     SET name = :name, email = :email, phone = :phone, address = :address, profile_picture = :pic 
                     WHERE id = :id'
                );
                $stmt->execute([
                    'name'    => $name,
                    'email'   => $email,
                    'phone'   => $phone,
                    'address' => $address,
                    'pic'     => $profilePicture,
                    'id'      => $id,
                ]);

                set_flash('success', 'Student record updated successfully!');
                redirect(BASE_URL . '/student_info.php?id=' . $id);
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

<h1 class="text-center mb-3">Update Student Info</h1>

<form action="update_student.php?id=<?= (int) $id ?>" method="POST"
      enctype="multipart/form-data"
      class="needs-validation-ajax styled-form p-4 mx-auto shadow-sm"
      id="studentForm"
      novalidate>
    <?= csrf_field() ?>

    <input type="hidden" name="id" value="<?= (int) $id ?>" />

    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input
            type="text"
            id="name"
            name="name"
            class="form-control"
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
            value="<?= e($address) ?>"
            data-validate="address"
        />
    </div>

    <div class="mb-3">
        <label for="profile_picture" class="form-label">Change Picture</label>
        <input
            type="file"
            id="profile_picture"
            name="profile_picture"
            class="form-control"
            accept="image/*"
            data-validate="profile_picture"
        />
    </div>

    <button type="submit" class="btn btn-submit w-100 mt-2">Save Changes</button>
</form>

<p class="text-center mt-3 mb-0 small">
    <a href="student_info.php?id=<?= (int) $id ?>" class="fw-semibold">&larr; Cancel and Back to Info</a>
</p>

<?php require __DIR__ . '/includes/footer.php'; ?>