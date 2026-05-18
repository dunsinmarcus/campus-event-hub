<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Campus Event Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>Campus Event Hub</h1>
            </div>
        </div>
    </nav>

    <div class="auth-container">
        <div class="auth-form">
            <h2>Student Login</h2>
            <form id="login-form" onsubmit="handleLogin(event)">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn-primary">Login</button>

                <div class="divider">or</div>

                <p class="auth-switch">Don't have an account? <a href="register.php">Register here</a></p>
            </form>

            <div id="error-message" class="error-message" style="display: none;"></div>
            <div id="success-message" class="success-message" style="display: none;"></div>
        </div>
    </div>

    <script src="js/auth.js"></script>
</body>
</html>
