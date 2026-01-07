<?php
require_once 'config.php';

if (!isset($_SESSION['reg_full_name']) || !isset($_SESSION['reg_location'])) {
    header('Location: register_step1.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['user_type'])) {
        $error = 'Please select your account type';
    } else {
        $user_type = $_POST['user_type'];
        if ($user_type === 'farmer' || $user_type === 'buyer') {
            $_SESSION['reg_user_type'] = $user_type;
            header('Location: register_step4.php');
            exit();
        } else {
            $error = 'Invalid selection';
        }
    }
}

// after handling POST/redirects, add a helper to preselect current type
$currentType = '';
if (isset($_POST['user_type'])) {
    $currentType = $_POST['user_type'];
} elseif (isset($_SESSION['reg_user_type'])) {
    $currentType = $_SESSION['reg_user_type'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Step 3</title>
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

        .brand {
            display: flex; align-items: center; gap: 12px; margin-bottom: 16px;
        }

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

        .user-type-selection { display: flex; gap: 20px; margin-bottom: 24px; }

        .user-type-card {
            flex: 1; border: 3px solid #dfe8df; border-radius: 12px;
            padding: 26px 18px; text-align: center; cursor: pointer;
            transition: all 0.2s ease;
            background: #fff;
            outline: none;
        }

        .user-type-card:hover { border-color: var(--green-600); transform: translateY(-3px); box-shadow: 0 5px 18px rgba(46, 125, 50, 0.15); }
        .user-type-card:focus { box-shadow: 0 0 0 3px rgba(56,142,60,0.18); border-color: var(--green-600); }

        .user-type-card.selected {
            border-color: var(--green-700);
            background: #f0f9f1;
        }
        .user-type-card.selected h3 { color: var(--green-700); }

        .user-type-icon { font-size: 44px; margin-bottom: 12px; }
        .user-type-card h3 { color: #333; margin-bottom: 8px; font-size: 18px; }
        .user-type-card p { color: #666; font-size: 14px; line-height: 1.5; }

        .user-type-card input[type="radio"] { display: none; }

        .btn {
            width: 100%; padding: 14px; background: var(--green-700); color: #fff;
            border: none; border-radius: 10px; font-size: 16px; font-weight: 800;
            cursor: pointer; transition: background 0.2s, transform 0.02s;
        }
        .btn:hover { background: var(--green-600); }
        .btn:active { transform: translateY(1px); }
        .btn:disabled { background: #c7d5c7; cursor: not-allowed; }

        .alert-error {
            background: var(--danger-100); color: var(--danger-700);
            border: 1px solid #ffcdd2; padding: 12px; border-radius: 8px;
            margin-bottom: 16px; font-size: 14px;
        }

        .back-link { text-align: center; margin-top: 16px; }
        .back-link a { color: var(--brown-700); text-decoration: none; font-size: 14px; font-weight: 700; }
        .back-link a:hover { text-decoration: underline; }

        @media (max-width: 480px) {
            .inner { padding: 22px 16px; }
            .brand h2 { font-size: 20px; }
            .progress-step { font-size: 12px; padding: 8px 6px; }
            .user-type-selection { flex-direction: column; gap: 14px; }
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

            <p class="subtitle">Choose your account type.</p>

            <div class="progress-bar">
                <div class="progress-step completed">1. Name</div>
                <div class="progress-step completed">2. Contact</div>
                <div class="progress-step active">3. Type</div>
                <div class="progress-step">4. Account</div>
            </div>

            <?php if ($error): ?>
                <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="" id="userTypeForm" novalidate>
                <div class="user-type-selection">
                    <div
                        class="user-type-card <?php echo $currentType === 'farmer' ? 'selected' : ''; ?>"
                        onclick="selectType(event, 'farmer')"
                        role="radio"
                        aria-checked="<?php echo $currentType === 'farmer' ? 'true' : 'false'; ?>"
                        tabindex="0"
                        onkeydown="if(event.key==='Enter'||event.key===' '){selectType(event,'farmer');}"
                    >
                        <div class="user-type-icon">🌾</div>
                        <h3>Farmer</h3>
                        <p>I want to sell my agricultural products directly to buyers</p>
                        <input type="radio" name="user_type" value="farmer" id="farmer" <?php echo $currentType === 'farmer' ? 'checked' : ''; ?>>
                    </div>

                    <div
                        class="user-type-card <?php echo $currentType === 'buyer' ? 'selected' : ''; ?>"
                        onclick="selectType(event, 'buyer')"
                        role="radio"
                        aria-checked="<?php echo $currentType === 'buyer' ? 'true' : 'false'; ?>"
                        tabindex="0"
                        onkeydown="if(event.key==='Enter'||event.key===' '){selectType(event,'buyer');}"
                    >
                        <div class="user-type-icon">🛒</div>
                        <h3>Buyer</h3>
                        <p>I want to purchase fresh products at wholesale prices</p>
                        <input type="radio" name="user_type" value="buyer" id="buyer" <?php echo $currentType === 'buyer' ? 'checked' : ''; ?>>
                    </div>
                </div>

                <button type="submit" class="btn" id="continueBtn" <?php echo $currentType ? '' : 'disabled'; ?>>Continue</button>
            </form>

            <div class="back-link">
                <a href="register_step2.php">← Back</a>
            </div>
        </div>
    </div>

    <script>
        function selectType(e, type) {
            document.querySelectorAll('.user-type-card').forEach(card => {
                card.classList.remove('selected');
                card.setAttribute('aria-checked', 'false');
            });

            const selectedCard = e.currentTarget;
            selectedCard.classList.add('selected');
            selectedCard.setAttribute('aria-checked', 'true');

            document.getElementById(type).checked = true;
            document.getElementById('continueBtn').disabled = false;
        }
    </script>
</body>
</html>