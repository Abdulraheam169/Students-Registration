document.addEventListener('DOMContentLoaded', function () {
    const baseUrl = document.body.getAttribute('data-base-url') || '';

    // Auto-dismiss flash alerts after 4 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.click();
            }
        }, 4000);
    });

    // Render or clear Bootstrap validation feedback message
    function setFieldStatus(input, isValid, message) {
        let feedback = input.parentElement.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            input.parentElement.appendChild(feedback);
        }

        if (isValid) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            feedback.textContent = '';
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            feedback.textContent = message;
        }
    }

    // Synchronous client-side rules matching backend validation
    function validateField(input) {
        const rule = input.getAttribute('data-validate');
        const val = input.value.trim();

        if (!rule) return true;

        if (rule === 'name') {
            if (val === '') {
                setFieldStatus(input, false, 'Full name is required.');
                return false;
            }
            if (val.length < 3 || val.length > 100) {
                setFieldStatus(input, false, 'Name must be between 3 and 100 characters.');
                return false;
            }
        }

        if (rule === 'username') {
            const userRegex = /^[A-Za-z0-9_.]{3,100}$/;
            if (val === '') {
                setFieldStatus(input, false, 'Username is required.');
                return false;
            }
            if (!userRegex.test(val)) {
                setFieldStatus(input, false, 'Use 3-100 letters, numbers, dots, or underscores.');
                return false;
            }
        }

        if (rule === 'email' || rule === 'student_email') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (val === '') {
                setFieldStatus(input, false, 'Email address is required.');
                return false;
            }
            if (!emailRegex.test(val)) {
                setFieldStatus(input, false, 'Please enter a valid email address.');
                return false;
            }
        }

        if (rule === 'phone') {
            const phoneRegex = /^[0-9+\-\s()]{7,15}$/;
            if (val === '') {
                setFieldStatus(input, false, 'Phone number is required.');
                return false;
            }
            if (!phoneRegex.test(val)) {
                setFieldStatus(input, false, 'Phone must be 7-15 digits.');
                return false;
            }
        }

        if (rule === 'address') {
            if (val.length > 1000) {
                setFieldStatus(input, false, 'Address cannot exceed 1000 characters.');
                return false;
            }
            if (val === '') {
                input.classList.remove('is-invalid', 'is-valid');
                return true;
            }
        }

        if (rule === 'password') {
            if (val.length < 6) {
                setFieldStatus(input, false, 'Password must be at least 6 characters.');
                return false;
            }
            if (!/[A-Za-z]/.test(val) || !/\d/.test(val)) {
                setFieldStatus(input, false, 'Password must include a letter and a number.');
                return false;
            }
        }

        if (rule === 'confirm_password') {
            const targetSelector = input.getAttribute('data-match');
            const targetInput = targetSelector ? document.querySelector(targetSelector) : null;
            if (val === '') {
                setFieldStatus(input, false, 'Please confirm your password.');
                return false;
            }
            if (targetInput && val !== targetInput.value) {
                setFieldStatus(input, false, 'Passwords do not match.');
                return false;
            }
        }

        if (rule === 'identifier' || rule === 'login_password') {
            if (val === '') {
                setFieldStatus(input, false, 'This field is required.');
                return false;
            }
        }

        if (rule === 'profile_picture') {
            if (!input.files || input.files.length === 0) {
                input.classList.remove('is-invalid', 'is-valid');
                return true;
            }
            const file = input.files[0];
            const allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            const ext = file.name.split('.').pop().toLowerCase();

            if (!allowedExt.includes(ext)) {
                setFieldStatus(input, false, 'Only JPG, PNG, GIF, or WEBP files are allowed.');
                return false;
            }
            if (file.size > 2 * 1024 * 1024) {
                setFieldStatus(input, false, 'Image size must be less than 2 MB.');
                return false;
            }
        }

        setFieldStatus(input, true, '');
        return true;
    }
// Remote check for duplicate username or email
    function checkAvailability(input) {
        const type = input.getAttribute('data-validate');
        const value = input.value.trim();

        if (!['username', 'email', 'student_email'].includes(type) || value === '') {
            return;
        }

        const form = input.closest('form');
        const idInput = form ? form.querySelector('input[name="id"]') : null;
        const ignoreId = idInput ? idInput.value : 0;

        const url = `${baseUrl}/check-availability.php?type=${encodeURIComponent(type)}&value=${encodeURIComponent(value)}&ignore_id=${encodeURIComponent(ignoreId)}`;

        fetch(url)
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.exists) {
                    input.dataset.taken = 'true';
                    setFieldStatus(input, false, data.message || 'This value is already taken.');
                } else {
                    delete input.dataset.taken;
                    setFieldStatus(input, true, '');
                }
            })
            .catch(function () {
                // Fallback to server-side check if request fails
                delete input.dataset.taken;
            });
    }
    // Attach events to forms with AJAX validation class
    const forms = document.querySelectorAll('.needs-validation-ajax');
    forms.forEach(function (form) {
        const inputs = form.querySelectorAll('[data-validate]');

        inputs.forEach(function (input) {
            input.addEventListener('input', function () {
                delete input.dataset.taken;
                validateField(input);
            });

            input.addEventListener('blur', function () {
                if (validateField(input)) {
                    checkAvailability(input);
                }
            });
        });

        form.addEventListener('submit', function (e) {
            let formValid = true;

            inputs.forEach(function (input) {
                const valid = validateField(input);
                if (!valid || input.dataset.taken === 'true') {
                    formValid = false;
                }
            });

            if (!formValid) {
                e.preventDefault();
            }
        });
    });
});