// Authentication JavaScript

/**
 * Handle login form submission
 */
function handleLogin(event) {
    event.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const errorMsg = document.getElementById('error-message');
    const successMsg = document.getElementById('success-message');

    if (!email || !password) {
        showError(errorMsg, 'Please fill in all fields');
        return;
    }

    // Clear messages
    errorMsg.style.display = 'none';
    successMsg.style.display = 'none';

    fetch('../api/auth.php?action=login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            email: email,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(successMsg, 'Login successful! Redirecting...');
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1500);
        } else {
            showError(errorMsg, data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError(errorMsg, 'An error occurred. Please try again.');
    });
}

/**
 * Handle registration form submission
 */
function handleRegister(event) {
    event.preventDefault();

    const studentId = document.getElementById('student_id').value;
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const department = document.getElementById('department').value;
    const phone = document.getElementById('phone').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const errorMsg = document.getElementById('error-message');
    const successMsg = document.getElementById('success-message');

    // Clear messages
    errorMsg.style.display = 'none';
    successMsg.style.display = 'none';

    // Validation
    if (!studentId || !name || !email || !password || !confirmPassword) {
        showError(errorMsg, 'Please fill in all required fields');
        return;
    }

    if (password !== confirmPassword) {
        showError(errorMsg, 'Passwords do not match');
        return;
    }

    if (password.length < 6) {
        showError(errorMsg, 'Password must be at least 6 characters');
        return;
    }

    fetch('../api/auth.php?action=register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            student_id: studentId,
            name: name,
            email: email,
            department: department,
            phone: phone,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(successMsg, 'Registration successful! Redirecting...');
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1500);
        } else {
            showError(errorMsg, data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError(errorMsg, 'An error occurred. Please try again.');
    });
}

/**
 * Show error message
 */
function showError(element, message) {
    if (element) {
        element.textContent = message;
        element.style.display = 'block';
    }
}

/**
 * Show success message
 */
function showSuccess(element, message) {
    if (element) {
        element.textContent = message;
        element.style.display = 'block';
    }
}
