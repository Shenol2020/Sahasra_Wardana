<?php
require_once 'config.php';

$error = '';
$success = '';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// CSRF setup
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- Social login config + helpers ---
$oauthProviders = [
    'google' => [
        'client_id' => getenv('GOOGLE_CLIENT_ID') ?: (defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : ''),
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: (defined('GOOGLE_CLIENT_SECRET') ? GOOGLE_CLIENT_SECRET : ''),
        'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url' => 'https://oauth2.googleapis.com/token',
        'userinfo_url' => 'https://www.googleapis.com/oauth2/v3/userinfo',
        'scope' => 'openid email profile',
    ],
    'facebook' => [
        'client_id' => getenv('FACEBOOK_CLIENT_ID') ?: (defined('FACEBOOK_CLIENT_ID') ? FACEBOOK_CLIENT_ID : ''),
        'client_secret' => getenv('FACEBOOK_CLIENT_SECRET') ?: (defined('FACEBOOK_CLIENT_SECRET') ? FACEBOOK_CLIENT_SECRET : ''),
        'auth_url' => 'https://www.facebook.com/v16.0/dialog/oauth',
        'token_url' => 'https://graph.facebook.com/v16.0/oauth/access_token',
        'userinfo_url' => 'https://graph.facebook.com/me?fields=id,name,email',
        'scope' => 'email,public_profile',
    ],
];
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$redirectBase = $baseUrl . '/login.php';

/** Simple POST request */
function http_post_json($url, $fields) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $fields,
        CURLOPT_HEADER => false,
    ]);
    $resp = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    return [$status, $resp, $err];
}

/** Simple GET with bearer */
function http_get_json($url, $bearer = '') {
    $ch = curl_init($url);
    $headers = [];
    if ($bearer) $headers[] = 'Authorization: Bearer ' . $bearer;
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    $resp = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    return [$status, $resp, $err];
}

// --- Start OAuth flow ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['provider'])) {
    $provider = $_GET['provider'];
    if (!isset($oauthProviders[$provider])) {
        $error = 'Unsupported provider.';
    } else {
        $cfg = $oauthProviders[$provider];
        if (!$cfg['client_id'] || !$cfg['client_secret']) {
            $error = 'Social login not configured.';
        } else {
            $_SESSION['oauth_state'] = bin2hex(random_bytes(16));
            $redirectUri = $redirectBase . '?provider_cb=' . urlencode($provider);
            if ($provider === 'google') {
                $params = [
                    'client_id' => $cfg['client_id'],
                    'redirect_uri' => $redirectUri,
                    'response_type' => 'code',
                    'scope' => $cfg['scope'],
                    'state' => $_SESSION['oauth_state'],
                    'include_granted_scopes' => 'true',
                    'prompt' => 'select_account',
                ];
            } else { // facebook
                $params = [
                    'client_id' => $cfg['client_id'],
                    'redirect_uri' => $redirectUri,
                    'response_type' => 'code',
                    'scope' => $cfg['scope'],
                    'state' => $_SESSION['oauth_state'],
                ];
            }
            header('Location: ' . $cfg['auth_url'] . '?' . http_build_query($params));
            exit();
        }
    }
}

// --- OAuth callback handler ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['provider_cb']) && isset($_GET['code'])) {
    $provider = $_GET['provider_cb'];
    $state = $_GET['state'] ?? '';
    if (!hash_equals($_SESSION['oauth_state'] ?? '', $state)) {
        $error = 'Invalid OAuth session. Please try again.';
    } elseif (!isset($oauthProviders[$provider])) {
        $error = 'Unsupported provider.';
    } else {
        unset($_SESSION['oauth_state']);
        $cfg = $oauthProviders[$provider];
        $redirectUri = $redirectBase . '?provider_cb=' . urlencode($provider);

        // Exchange code for token
        if ($provider === 'google') {
            $tokenFields = [
                'code' => $_GET['code'],
                'client_id' => $cfg['client_id'],
                'client_secret' => $cfg['client_secret'],
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ];
        } else { // facebook
            $tokenFields = [
                'code' => $_GET['code'],
                'client_id' => $cfg['client_id'],
                'client_secret' => $cfg['client_secret'],
                'redirect_uri' => $redirectUri,
            ];
        }
        [$status, $tokenResp, $tokenErr] = http_post_json($cfg['token_url'], $tokenFields);
        if ($status < 200 || $status >= 300 || !$tokenResp) {
            $error = 'Failed to obtain access token.';
        } else {
            $tokenData = json_decode($tokenResp, true);
            $accessToken = $tokenData['access_token'] ?? '';
            if (!$accessToken && isset($tokenData['id_token'])) {
                // Google may return id_token; still prefer userinfo via access token
                $accessToken = $tokenData['access_token'] ?? '';
            }
            if (!$accessToken) {
                $error = 'Invalid token response.';
            } else {
                // Fetch userinfo
                $userinfoUrl = $cfg['userinfo_url'];
                // Facebook token exchange was GET historically; handle if POST failed
                if ($provider === 'facebook' && empty($tokenData['access_token'])) {
                    // try GET
                    $query = http_build_query($tokenFields);
                    [$status, $tokenResp, $tokenErr] = http_get_json($cfg['token_url'] . '?' . $query);
                    $tokenData = json_decode($tokenResp, true);
                    $accessToken = $tokenData['access_token'] ?? '';
                }
                [$uStatus, $uResp, $uErr] = http_get_json($userinfoUrl . ($provider === 'facebook' ? ('&access_token=' . urlencode($accessToken)) : ''), $provider === 'google' ? $accessToken : '');
                if ($uStatus < 200 || $uStatus >= 300 || !$uResp) {
                    $error = 'Failed to fetch user profile.';
                } else {
                    $profile = json_decode($uResp, true);
                    $email = $profile['email'] ?? '';
                    $name = $profile['name'] ?? ($profile['given_name'] ?? '');
                    $providerId = ($provider === 'google' ? 'google-' : 'facebook-') . ($profile['sub'] ?? $profile['id'] ?? '');

                    // Try to find existing local account by email or provider id in username
                    $conn = getDBConnection();
                    $stmt = $conn->prepare("SELECT user_id, username, password_hash, user_type, full_name FROM users WHERE username = ? OR username = ?");
                    $lookupKey1 = $email ?: $providerId;
                    $lookupKey2 = $providerId;
                    $stmt->bind_param("ss", $lookupKey1, $lookupKey2);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result && $result->num_rows >= 1) {
                        $user = $result->fetch_assoc();
                        // Login successful without password
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['user_type'] = $user['user_type'];
                        $_SESSION['full_name'] = $user['full_name'] ?: $name;
                        $stmt->close();
                        $conn->close();
                        header('Location: dashboard.php');
                        exit();
                    } else {
                        $stmt && $stmt->close();
                        $conn && $conn->close();
                        $error = 'No account linked with this social login. Please sign up first.';
                    }
                }
            }
        }
    }
}

// Replace POST handler with CSRF validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $error = 'Invalid session. Please refresh and try again.';
    } else {
        $username = sanitize($_POST['username']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password';
        } else {
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT user_id, username, password_hash, user_type, full_name FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['password_hash'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['full_name'] = $user['full_name'];
                    
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error = 'Invalid username or password';
                }
            } else {
                $error = 'Invalid username or password';
            }
            
            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Farmer Marketplace</title>
    <style>
        /* Updated nature-inspired palette */
        :root {
            --primary: #2c7a3f;          /* deep green */
            --primary-dark: #1f5a2d;
            --bg: #f3f7f1;               /* soft field green */
            --text: #2b2b2b;
            --muted: #5e6b5e;
            --border: #dfe6da;
            --success-bg: #e9f6ea;
            --success: #2e7d32;
            --error-bg: #fff3f0;
            --error: #c33;
            --accent-brown: #8b6f47;     /* earth brown */
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            /* Gentle sunrise over fields */
            background: linear-gradient(160deg, #cfe9c8 0%, #f6efd8 50%, #fff8ec 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 24px;
        }
        .auth-wrapper {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            width: 100%;
            max-width: 1000px;
            /* Softer, welcoming card */
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 16px 48px rgba(0,0,0,0.22);
        }
        .brand-panel {
            position: relative;
            /* Rural landscape-inspired panel */
            background:
                radial-gradient(900px 500px at -10% -10%, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0) 60%),
                linear-gradient(135deg, #3c8b4a 0%, #2c7a3f 45%, #8b6f47 100%);
            color: #fff;
            padding: 48px;
            display: flex; align-items: center;
        }
        .brand-content { max-width: 480px; }
        .brand-content h1 {
            font-size: 32px; font-weight: 800; letter-spacing: 0.2px; margin-bottom: 8px;
            color: #fff;
        }
        .brand-content p { color: rgba(255,255,255,0.92); margin-bottom: 24px; }
        .brand-list { list-style: none; display: grid; gap: 10px; margin-top: 12px; }
        .brand-list li { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,0.95); }

        /* Simple illustration block */
        .brand-illustration {
            margin-top: 20px;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            overflow: hidden;
            background: rgba(255,255,255,0.08);
            height: 140px; /* consistent height for photo crop */
        }
        .brand-illustration img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;   /* fills area with natural crop */
            border-radius: 12px; /* matches container rounding */
        }

        .form-panel {
            padding: 40px;
            background: #fff;
        }
        .form-header h2 {
            font-size: 24px; color: var(--primary); margin-bottom: 6px;
        }
        .form-header .sub { color: var(--muted); font-size: 14px; margin-bottom: 20px; }

        .alert {
            padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; border: 1px solid transparent;
        }
        .alert-error { background: var(--error-bg); color: var(--error); border-color: #fcc; }
        .alert-success { background: var(--success-bg); color: var(--success); border-color: #cfc; }

        .social-login {
            display: grid;
            grid-template-columns: 1fr; /* stacked vertically */
            gap: 12px;
            margin-bottom: 18px;
        }
        .social-btn {
            border: 2px solid var(--border);
            background: #f9fbf7;
            color: #2b3a2b;
            width: 100%;
            padding: 12px;               /* slightly larger for comfort */
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: flex;               /* align logo + text */
            align-items: center;
            justify-content: center;
            gap: 10px;                   /* spacing between logo and text */
        }
        .social-btn .logo {
            width: 20px;
            height: 20px;
            display: block;
        }
        .social-btn.google { border-color: #dbe6d5; }
        .social-btn.facebook { border-color: #e3e0d8; }
        .social-btn:hover { background: #f2f6ef; }

        .divider {
            position: relative; text-align: center; color: #999; margin: 18px 0 24px;
        }
        .divider span {
            background: #fff; padding: 0 10px; position: relative; z-index: 1; font-size: 12px; letter-spacing: 0.3px;
        }
        .divider::before {
            content: ''; position: absolute; left: 0; top: 50%; width: 100%; height: 1px; background: #e7eee2;
        }

        form .field { margin-bottom: 14px; }
        label { display: block; margin-bottom: 8px; color: #333; font-weight: 600; font-size: 13px; }
        .input-with-icon {
            position: relative; display: flex; align-items: center;
        }
        .input-with-icon input {
            border: 2px solid #dfe6da;
            background: #fcfdfb;
            width: 100%; padding: 12px 40px 12px 40px; border-radius: 10px; font-size: 14px;
            transition: border-color 0.25s ease;
        }
        .input-with-icon input:focus { border-color: var(--primary); }
        .input-with-icon .icon {
            position: absolute; left: 12px; width: 18px; height: 18px; opacity: 0.7;
        }
        .input-with-icon .toggle {
            position: absolute; right: 8px; border: none; background: transparent;
            color: var(--primary); font-weight: 600; cursor: pointer; padding: 6px 8px; border-radius: 6px;
        }
        .input-with-icon .toggle:hover { background: rgba(102,126,234,0.08); }

        .form-row {
            display: flex; align-items: center; justify-content: space-between;
            margin: 6px 0 16px;
        }
        .checkbox { color: #444; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }
        .forgot { font-size: 13px; color: var(--primary); text-decoration: none; }
        .forgot:hover { text-decoration: underline; }

        .btn.primary {
            background: var(--primary);
            width: 100%; padding: 12px; color: #fff;
            border: none; border-radius: 10px; font-size: 15px; font-weight: 700; cursor: pointer;
            transition: background 0.2s ease, transform 0.02s ease;
        }
        .btn.primary:hover { background: var(--primary-dark); }
        .btn.primary:active { transform: translateY(1px); }

        .register {
            text-align: center; margin-top: 16px; color: #555; font-size: 13px;
        }
        .register a { color: var(--primary); font-weight: 700; text-decoration: none; }
        .register a:hover { text-decoration: underline; }

        @media (max-width: 900px) {
            .auth-wrapper { grid-template-columns: 1fr; }
            .brand-panel { display: none; }
            .form-panel { padding: 28px; }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="brand-panel">
            <div class="brand-content">
                <!-- Updated branding and farmer-friendly copy -->
                <h1 style="text-align:center;">
                    <img src="assets/images/head.png" alt="Logo" style="height: 70px; vertical-align:middle; margin-right:8px;" />
                </h1>
                <h1 style="text-align:center;">Sri Lanka</h1>
                <p>A familiar marketplace connecting local farmers and buyers.</p>
                <ul class="brand-list">
                    <li>
                        <span class="brand-dot" aria-hidden="true" style="font-size:18px; line-height:1; flex:0 0 auto;">•</span>
                        Rice , tea, and spices from our lands
                    </li>
                    <li>
                        <span class="brand-dot" aria-hidden="true" style="font-size:18px; line-height:1; flex:0 0 auto;">•</span>
                        Fair prices and direct sales
                    </li>
                    <li>
                        <span class="brand-dot" aria-hidden="true" style="font-size:18px; line-height:1; flex:0 0 auto;">•</span>
                        Simple, secure, and friendly
                    </li>
                </ul>

                <!-- Minimal rural landscape SVG (no external assets) -->
                <div class="brand-illustration" aria-hidden="true">
                
                    <img src="assets/images/sri_lanka_farm.jpg"
                         alt="Sri Lankan rural landscape with paddy fields, tea estates, and coconut trees"
                         width="1600" height="900" loading="lazy" />
                </div>
            </div>
        </div>

        <div class="form-panel">
            <div class="form-header">
                <!-- Friendlier header -->
                <b><h2 style="text-align:center;">Welcome</h2></b>
                <div class="sub">Sign in to continue</div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="social-login">
                <a class="social-btn google" aria-label="Sign in with Google" href="?provider=google">
                    <img class="logo" src="assets/images/google.png" alt="" aria-hidden="true" />
                    <span>Continue with Google</span>
                </a>
                <a class="social-btn facebook" aria-label="Sign in with Facebook" href="?provider=facebook">
                    <img class="logo" src="assets/images/facebook.png" alt="" aria-hidden="true" style="width: 29px; height: 22px;" />
                    <span>Continue with Facebook</span>
                </a>
            </div>

            <div class="divider"><span>Or use your details</span></div>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES); ?>">
                <div class="field">
                    <label for="username">Username</label>
                    <div class="input-with-icon">
                        <!-- ...existing code (icon)... -->
                        <input type="text" id="username" name="username" required autocomplete="username" placeholder="e.g : sunil_disanayaka">
                    </div>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <!-- ...existing code (icon)... -->
                        <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
                        <!-- ...existing code (toggle)... -->
                    </div>
                </div>

                <div class="form-row">
                    <label class="checkbox">
                        <input type="checkbox" id="rememberMe"> Remember me
                    </label>
                    <a href="forgot_password.php" class="forgot">Forgot password?</a>
                </div>

                <button type="submit" class="btn primary">Sign in</button>
            </form>

            <p class="register">Don't have an account? <a href="register_step1.php">Create Account</a></p>
        </div>
    </div>

    <script>
        // Password visibility toggle
        (function () {
            var btn = document.getElementById('togglePassword');
            var input = document.getElementById('password');
            if (btn && input) {
                btn.addEventListener('click', function () {
                    var type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    btn.textContent = type === 'password' ? 'Show' : 'Hide';
                });
            }
        })();
    </script>
</body>
</html>