<?php
require_once 'config.php';

if (!isset($_SESSION['reg_full_name'])) {
    header('Location: register_step1.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = sanitize($_POST['location']);
    $contact = sanitize($_POST['contact']);
    
    if (empty($location) || empty($contact)) {
        $error = 'Please fill in all fields';
    } elseif (strlen($contact) < 10) {
        $error = 'Please enter a valid contact number';
    } else {
        $_SESSION['reg_location'] = $location;
        $_SESSION['reg_contact'] = $contact;
        header('Location: register_step3.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Step 2</title>
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

        .top-bar {
            height: 8px;
            background: linear-gradient(90deg, var(--green-700), var(--brown-700));
        }

        .inner { padding: 28px 24px; }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .leaf {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: var(--green-100);
            display: grid; place-items: center;
            border: 1px solid #dfe8df;
        }

        .brand h2 {
            font-size: 22px;
            line-height: 1.2;
            color: var(--green-700);
            font-weight: 700;
        }

        .subtitle {
            color: var(--text-600);
            margin: 6px 0 18px;
            font-size: 14px;
        }

        .progress-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0 22px;
            gap: 8px;
        }

        .progress-step {
            flex: 1;
            text-align: center;
            background: #f6faf5;
            color: #5d6b5b;
            padding: 10px 8px;
            border-radius: 20px;
            border: 1px solid #e3efe1;
            font-size: 13px;
            font-weight: 600;
        }

        .progress-step.active {
            background: var(--green-700);
            color: #fff;
            border-color: var(--green-700);
        }

        .progress-step.completed {
            background: var(--green-600);
            color: #fff;
            border-color: var(--green-600);
        }

        .form-group { margin-bottom: 16px; }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-900);
            font-weight: 700;
            font-size: 14px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 14px 12px;
            border: 2px solid #dfe8df;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fff;
            font-family: inherit;
        }

        input[type="text"]::placeholder,
        textarea::placeholder { color: #9aa69a; }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: var(--green-600);
            box-shadow: 0 0 0 3px rgba(56, 142, 60, 0.15);
        }

        textarea { resize: vertical; min-height: 96px; }

        .hint {
            color: var(--text-600);
            font-size: 12px;
            margin-top: 6px;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: var(--green-700);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: background 0.2s, transform 0.02s;
        }

        .btn:hover { background: var(--green-600); }
        .btn:active { transform: translateY(1px); }

        .alert-error {
            background: var(--danger-100);
            color: var(--danger-700);
            border: 1px solid #ffcdd2;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 14px;
            gap: 10px;
        }

        .back-link a {
            color: var(--brown-700);
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
        }

        .back-link a:hover { text-decoration: underline; }

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

            <div class="progress-bar">
                <div class="progress-step completed">1. Name</div>
                <div class="progress-step active">2. Contact</div>
                <div class="progress-step">3. Type</div>
                <div class="progress-step">4. Account</div>
            </div>

            <?php if ($error): ?>
                <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="" novalidate>
                <div class="form-group">
                    <label for="location">Location</label>
                    <textarea
                        id="location"
                        name="location"
                        placeholder="e.g : Meegoda, Homagama, Colombo District"
                        required
                        aria-label="Location"><?php
                            echo isset($_POST['location'])
                                ? htmlspecialchars($_POST['location'])
                                : (isset($_SESSION['reg_location']) ? htmlspecialchars($_SESSION['reg_location']) : '');
                        ?></textarea>
                    <div class="hint">Include village, town, and district for accurate delivery.</div>
                </div>

                <div class="form-group">
                    <label for="contact">Contact Number</label>
                    <input
                        type="text"
                        id="contact"
                        name="contact"
                        placeholder="e.g : +94 71 234 5678"
                        required
                        aria-label="Contact number"
                        value="<?php
                            echo isset($_POST['contact'])
                                ? htmlspecialchars($_POST['contact'])
                                : (isset($_SESSION['reg_contact']) ? htmlspecialchars($_SESSION['reg_contact']) : '');
                        ?>"
                    >
                    <div class="hint">Use your mobile number with country code.</div>
                </div>

                <button type="submit" class="btn">Continue</button>
            </form>

            <div class="footer">
                <div class="back-link"><a href="register_step1.php">← Back</a></div>
            </div>
        </div>
    </div>
</body>
</html>