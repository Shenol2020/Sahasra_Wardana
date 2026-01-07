<?php
require_once 'config.php';
requireLogin();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

if (isUserType('farmer')) {
    // Farmer: Show messages received from buyers
    $messages = $conn->query("SELECT m.*, u.full_name as sender_name, u.contact as sender_contact, p.product_name 
                              FROM messages m 
                              JOIN users u ON m.sender_id = u.user_id 
                              JOIN products p ON m.product_id = p.product_id 
                              WHERE m.receiver_id = $user_id 
                              ORDER BY m.sent_at DESC");
    
    // Mark all messages as read
    $conn->query("UPDATE messages SET is_read = 1 WHERE receiver_id = $user_id");
} else {
    // Buyer: Show messages sent to farmers
    $messages = $conn->query("SELECT m.*, u.full_name as receiver_name, u.contact as receiver_contact, p.product_name 
                              FROM messages m 
                              JOIN users u ON m.receiver_id = u.user_id 
                              JOIN products p ON m.product_id = p.product_id 
                              WHERE m.sender_id = $user_id 
                              ORDER BY m.sent_at DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - FarmMarket</title>
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
            --success-700: #1b5e20;
            --success-100: #e8f5e9;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, "Noto Sans", "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(165deg, var(--earth-bg-1) 0%, var(--earth-bg-2) 100%);
            color: var(--text-900);
        }

        .navbar {
            background: #fff; padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(45,63,45,0.08);
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 100; border-bottom: 4px solid var(--green-700);
        }

        .logo { font-size: 24px; font-weight: 800; color: var(--green-700); }
        .nav-link { color: var(--green-700); text-decoration: none; font-weight: 800; }
        .nav-link:hover { text-decoration: underline; }

        .container { max-width: 1000px; margin: 0 auto; padding: 30px 20px; }

        .header { margin-bottom: 20px; }
        .header h1 { color: var(--text-900); margin-bottom: 6px; }
        .header p { color: var(--text-600); }

        .alert-success {
            background: var(--success-100); color: var(--success-700);
            border: 1px solid #c8e6c9; padding: 12px; border-radius: 10px; margin-bottom: 16px;
        }

        .messages-list { display: flex; flex-direction: column; gap: 18px; }

        .message-card {
            background: #fff; border-radius: 12px; padding: 20px;
            box-shadow: 0 2px 10px rgba(45,63,45,0.08);
            border: 1px solid var(--brown-100);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .message-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(45,63,45,0.12); }

        .message-header {
            display: flex; justify-content: space-between; align-items: flex-start;
            margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e3efe1;
        }

        .product-name { font-size: 18px; font-weight: 800; color: var(--green-700); margin-bottom: 6px; }

        .contact-info { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .contact-item { display: flex; align-items: center; gap: 6px; font-size: 14px; color: var(--text-600); }

        .message-time { font-size: 13px; color: var(--text-600); white-space: nowrap; }

        .message-content {
            background: #f6faf5; padding: 12px; border-radius: 10px; margin-bottom: 12px; border: 1px solid #e3efe1;
        }
        .message-text { color: var(--text-900); line-height: 1.6; font-size: 15px; }

        .message-actions { display: flex; gap: 10px; }
        .btn-contact {
            padding: 10px 16px; background: var(--green-700); color: #fff; text-decoration: none;
            border-radius: 10px; font-size: 14px; font-weight: 800; transition: background 0.2s;
        }
        .btn-contact:hover { background: var(--green-600); }

        .empty-state {
            background: #fff; padding: 50px 20px; border-radius: 12px; text-align: center;
            border: 1px solid var(--brown-100);
        }
        .empty-state-icon { font-size: 64px; margin-bottom: 14px; }
        .empty-state h2 { color: var(--text-900); margin-bottom: 6px; }
        .empty-state p { color: var(--text-600); margin-bottom: 16px; }

        .btn-primary {
            display: inline-block; padding: 12px 24px; background: var(--green-700); color: #fff;
            text-decoration: none; border-radius: 10px; font-weight: 800; transition: background 0.2s;
        }
        .btn-primary:hover { background: var(--green-600); }

        .unread-badge {
            background: #ff4444; color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 800;
        }

        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 12px; }
            .message-header { flex-direction: column; gap: 10px; }
            .contact-info { flex-direction: column; align-items: flex-start; gap: 6px; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <img src="assets/images/head2.png" alt="Logo" style="height: 55px;px; vertical-align:middle; margin-right:8px;" />
        </div>
        <a href="dashboard.php" class="nav-link">← Back to Dashboard</a>
    </nav>
    
    <div class="container">
        <div class="header">
            <h1>💬 Messages</h1>
            <p><?php echo isUserType('farmer') ? 'Inquiries from buyers about your products' : 'Your messages to farmers'; ?></p>
        </div>
        
        <?php if (isset($_GET['sent'])): ?>
            <div class="alert-success">✓ Message sent successfully! The farmer will see your inquiry.</div>
        <?php endif; ?>
        
        <?php if ($messages->num_rows > 0): ?>
            <div class="messages-list">
                <?php while ($msg = $messages->fetch_assoc()): ?>
                    <div class="message-card">
                        <div class="message-header">
                            <div class="message-title">
                                <div class="product-name">📦 <?php echo htmlspecialchars($msg['product_name']); ?></div>
                                <div class="contact-info">
                                    <?php if (isUserType('farmer')): ?>
                                        <span class="contact-item">👤 From: <?php echo htmlspecialchars($msg['sender_name']); ?></span>
                                        <span class="contact-item">📞 <?php echo htmlspecialchars($msg['sender_contact']); ?></span>
                                    <?php else: ?>
                                        <span class="contact-item">👨‍🌾 To: <?php echo htmlspecialchars($msg['receiver_name']); ?></span>
                                        <span class="contact-item">📞 <?php echo htmlspecialchars($msg['receiver_contact']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="message-time">
                                <?php 
                                    $time_ago = time() - strtotime($msg['sent_at']);
                                    if ($time_ago < 3600) {
                                        echo floor($time_ago / 60) . ' minutes ago';
                                    } elseif ($time_ago < 86400) {
                                        echo floor($time_ago / 3600) . ' hours ago';
                                    } else {
                                        echo formatDate($msg['sent_at']);
                                    }
                                ?>
                            </div>
                        </div>
                        
                        <div class="message-content">
                            <div class="message-text"><?php echo nl2br(htmlspecialchars($msg['message_text'])); ?></div>
                        </div>
                        
                        <?php if (isUserType('farmer')): ?>
                            <div class="message-actions">
                                <a href="tel:<?php echo htmlspecialchars($msg['sender_contact']); ?>" class="btn-contact">📞 Call Buyer</a>
                            </div>
                        <?php else: ?>
                            <div class="message-actions">
                                <a href="tel:<?php echo htmlspecialchars($msg['receiver_contact']); ?>" class="btn-contact">📞 Call Farmer</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">💬</div>
                <h2>No Messages Yet</h2>
                <p><?php echo isUserType('farmer') ? 'You haven\'t received any inquiries yet. Make sure your products are listed!' : 'You haven\'t contacted any farmers yet. Browse products and reach out!'; ?></p>
                <a href="dashboard.php" class="btn-primary">Browse Products</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>