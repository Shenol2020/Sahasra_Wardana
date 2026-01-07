<?php
require_once 'config.php';

if (!isset($_SESSION['reg_full_name']) || !isset($_SESSION['reg_location']) || !isset($_SESSION['reg_user_type'])) {
    header('Location: register_step1.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif (strlen($username) < 4) {
        $error = 'Username must be at least 4 characters';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $conn = getDBConnection();
        
        // Check if username already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username already exists. Please choose another.';
        } else {
            // Insert new user
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, location, contact, user_type, username, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", 
                $_SESSION['reg_full_name'],
                $_SESSION['reg_location'],
                $_SESSION['reg_contact'],
                $_SESSION['reg_user_type'],
                $username,
                $password_hash
            );
            
            if ($stmt->execute()) {
                // Clear registration session data
                unset($_SESSION['reg_full_name']);
                unset($_SESSION['reg_location']);
                unset($_SESSION['reg_contact']);
                unset($_SESSION['reg_user_type']);
                
                // Redirect to login
                header('Location: login.php?registered=1');
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Step 4</title>
    <style>
        :root {
            --green-700: #2e7d32;
            --green-600: #388e3c;
            --green-100: #e8f5e9;
            --brown-700: #6d4c41;
            --brown-100: #efebe9;
            --earth-bg-1: #f1f8e9;
            --earth-bg-2: #f9f6ef;
            --text-900: #2b2b2b;
            --text-600: #5b5b5b;
            --danger-700: #b71c1c;
            --danger-100: #ffebee;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, "Noto Sans", "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(165deg, var(--earth-bg-1) 0%, var(--earth-bg-2) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            color: var(--text-900);
        }

        .container {
            width: 100%;
            max-width: 560px;
            background: #fff;
            border-radius: 12px;
            border: 1px solid var(--brown-100);
            box-shadow: 0 8px 32px rgba(45, 63, 45, 0.12);
            overflow: hidden;
        }

        .top-bar { height: 8px; background: linear-gradient(90deg, var(--green-700), var(--brown-700)); }
        .inner { padding: 28px 24px; }

        .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
        .leaf {
            width: 40px; height: 40px; border-radius: 50%;
            background: var(--green-100);
            display: grid; place-items: center;
            border: 1px solid #dfe8df;
        }
        .brand h2 { font-size: 22px; line-height: 1.2; color: var(--green-700); font-weight: 700; }

        .subtitle { color: var(--text-600); margin: 6px 0 18px; font-size: 14px; }

        .progress-bar {
            display: flex; justify-content: space-between; align-items: center;
            margin: 10px 0 22px; gap: 8px;
        }
        .progress-step {
            flex: 1; text-align: center; background: #f6faf5; color: #5d6b5b;
            padding: 10px 8px; border-radius: 20px; border: 1px solid #e3efe1;
            font-size: 13px; font-weight: 600;
        }
        .progress-step.active { background: var(--green-700); color: #fff; border-color: var(--green-700); }
        .progress-step.completed { background: var(--green-600); color: #fff; border-color: var(--green-600); }

        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 8px; color: var(--text-900); font-weight: 700; font-size: 14px; }

        input[type="text"], input[type="password"] {
            width: 100%; padding: 14px 12px; border: 2px solid #dfe8df; border-radius: 8px;
            font-size: 16px; transition: border-color 0.2s, box-shadow 0.2s; background: #fff;
        }
        input[type="text"]::placeholder, input[type="password"]::placeholder { color: #9aa69a; }
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none; border-color: var(--green-600); box-shadow: 0 0 0 3px rgba(56,142,60,0.15);
        }

        .password-hint { font-size: 12px; color: var(--text-600); margin-top: 6px; }

        .btn {
            width: 100%; padding: 14px; background: var(--green-700); color: white;
            border: none; border-radius: 10px; font-size: 16px; font-weight: 800;
            cursor: pointer; transition: background 0.2s, transform 0.02s;
        }
        .btn:hover { background: var(--green-600); }
        .btn:active { transform: translateY(1px); }

        .alert-error {
            background: var(--danger-100); color: var(--danger-700);
            border: 1px solid #ffcdd2; padding: 12px; border-radius: 8px;
            margin-bottom: 16px; font-size: 14px;
        }

        .footer { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; gap: 10px; }
        .back-link a { color: var(--brown-700); text-decoration: none; font-size: 14px; font-weight: 700; }
        .back-link a:hover { text-decoration: underline; }

        .show-password {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--text-600);
            margin-top: 8px;
            user-select: none;
            cursor: pointer;
        }

        @media (max-width: 480px) {
            .inner { padding: 22px 16px; }
            .brand h2 { font-size: 20px; }
            .progress-step { font-size: 12px; padding: 8px 6px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-bar"></div>
        <div class="inner">
            <div class="brand">
                <div class="leaf" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M3 21c7 0 12-5 12-12V4c0-.6-.4-1-1-1h-5C4 3 3 7 3 10v11z" fill="#2e7d32"/>
                        <path d="M9 8c0 6 6 8 10 8" stroke="#6d4c41" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <h2>Welcome</h2>
            </div>

            <p class="subtitle">Create your login credentials</p>

            <div class="progress-bar">
                <div class="progress-step completed">1. Name</div>
                <div class="progress-step completed">2. Contact</div>
                <div class="progress-step completed">3. Type</div>
                <div class="progress-step active">4. Account</div>
            </div>

            <?php if ($error): ?>
                <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="" novalidate>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Choose a unique username"
                        required
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                        aria-label="Username"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create a secure password"
                        required
                        aria-label="Password"
                    >
                    <p class="password-hint">At least 6 characters</p>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Re-enter your password"
                        required
                        aria-label="Confirm password"
                    >
                    <label class="show-password">
                        <input type="checkbox" id="showPass"> Show password
                    </label>
                </div>

                <button type="submit" class="btn">Complete Registration</button>
            </form>

            <div class="footer">
                <div class="back-link"><a href="register_step3.php">← Back</a></div>
            </div>
        </div>
    </div>

    <script>
        // Toggle visibility for both password fields
        (function () {
            const show = document.getElementById('showPass');
            const pwd = document.getElementById('password');
            const cpwd = document.getElementById('confirm_password');
            if (!show || !pwd || !cpwd) return;
            show.addEventListener('change', function () {
                const t = this.checked ? 'text' : 'password';
                pwd.type = t;
                cpwd.type = t;
            });
        })();
    </script>
</body>
</html>