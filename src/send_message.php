<?php
require_once 'config.php';
requireLogin();

if (!isset($_GET['product_id'])) {
    header('Location: dashboard.php');
    exit();
}

$product_id = (int)$_GET['product_id'];
$conn = getDBConnection();

// Get product and farmer details
$stmt = $conn->prepare("SELECT p.*, u.full_name, u.location FROM products p JOIN users u ON p.farmer_id = u.user_id WHERE p.product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: dashboard.php');
    exit();
}

$product = $result->fetch_assoc();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = sanitize($_POST['message']);
    
    if (empty($message)) {
        $error = 'Please enter a message';
    } elseif (strlen($message) < 10) {
        $error = 'Message must be at least 10 characters';
    } else {
        $stmt = $conn->prepare("INSERT INTO messages (product_id, sender_id, receiver_id, message_text) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $product_id, $_SESSION['user_id'], $product['farmer_id'], $message);
        
        if ($stmt->execute()) {
            $success = 'Message sent successfully!';
            $_POST['message'] = ''; // Clear the form
        } else {
            $error = 'Failed to send message. Please try again.';
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Farmer - FarmMarket</title>
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
            background: #fff;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(45,63,45,0.08);
            margin-bottom: 30px;
            border-bottom: 4px solid var(--green-700);
        }

        .navbar a {
            color: var(--green-700);
            text-decoration: none;
            font-weight: 800;
        }
        .navbar a:hover { text-decoration: underline; }

        .container { max-width: 900px; margin: 0 auto; padding: 0 20px 40px; }

        .content-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 25px;
        }

        .product-card, .message-card {
            background: #fff; padding: 24px; border-radius: 12px;
            box-shadow: 0 2px 10px rgba(45,63,45,0.08);
            border: 1px solid var(--brown-100);
        }

        .product-card h2, .message-card h2 { color: var(--green-700); margin-bottom: 16px; }

        .product-image {
            width: 100%; height: 220px; object-fit: cover; border-radius: 10px; margin-bottom: 16px; background: #f0f0f0;
        }

        .product-detail { margin-bottom: 12px; }
        .detail-label { font-size: 12px; color: var(--text-600); margin-bottom: 4px; }
        .detail-value { font-size: 16px; color: var(--text-900); font-weight: 600; }

        .price-highlight { color: var(--green-700); font-size: 20px; font-weight: 800; }

        .farmer-section {
            margin-top: 14px; padding-top: 14px; border-top: 1px solid #e3efe1;
        }
        .farmer-section h3 { color: var(--text-900); margin-bottom: 12px; font-size: 16px; }

        .alert { padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
        .alert-error { background: var(--danger-100); color: var(--danger-700); border: 1px solid #ffcdd2; }
        .alert-success { background: var(--success-100); color: var(--success-700); border: 1px solid #c8e6c9; }

        .message-info {
            background: #f6faf5; border: 1px solid #e3efe1; color: var(--text-600);
            padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 14px;
        }

        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 8px; color: var(--text-900); font-weight: 700; font-size: 14px; }

        textarea {
            width: 100%; padding: 14px 12px; border: 2px solid #dfe8df; border-radius: 10px;
            font-size: 16px; font-family: inherit; resize: vertical; min-height: 150px;
            transition: border-color 0.2s, box-shadow 0.2s; background: #fff;
        }
        textarea:focus { outline: none; border-color: var(--green-600); box-shadow: 0 0 0 3px rgba(56,142,60,0.15); }

        .btn {
            width: 100%; padding: 14px; background: var(--green-700); color: #fff; border: none;
            border-radius: 10px; font-size: 16px; font-weight: 800; cursor: pointer; transition: background 0.2s;
        }
        .btn:hover { background: var(--green-600); }

        @media (max-width: 768px) {
            .content-grid { grid-template-columns: 1fr; }
            .product-card, .message-card { padding: 20px; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php">← Back to Products</a>
    </nav>
    
    <div class="container">
        <div class="content-grid">
            <div class="product-card">
                <h2>Product Details</h2>
                
                <?php if (!empty($product['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="Product" class="product-image">
                <?php else: ?>
                    <div class="product-image" style="display: flex; align-items: center; justify-content: center; font-size: 48px;">🌾</div>
                <?php endif; ?>
                
                <div class="product-detail">
                    <div class="detail-label">Product Name</div>
                    <div class="detail-value"><?php echo htmlspecialchars($product['product_name']); ?></div>
                </div>
                
                <div class="product-detail">
                    <div class="detail-label">Description</div>
                    <div class="detail-value"><?php echo htmlspecialchars($product['description']); ?></div>
                </div>
                
                <div class="product-detail">
                    <div class="detail-label">Price per Kilogram</div>
                    <div class="price-highlight"><?php echo formatPrice($product['price_per_kg']); ?></div>
                </div>
                
                <div class="product-detail">
                    <div class="detail-label">Available Quantity</div>
                    <div class="detail-value"><?php echo $product['quantity_kg']; ?> kg</div>
                </div>
                
                <div class="farmer-section">
                    <h3>👨‍🌾 Farmer Information</h3>
                    <div class="product-detail">
                        <div class="detail-label">Name</div>
                        <div class="detail-value"><?php echo htmlspecialchars($product['full_name']); ?></div>
                    </div>
                    <div class="product-detail">
                        <div class="detail-label">Location</div>
                        <div class="detail-value"><?php echo htmlspecialchars($product['location']); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="message-card">
                <h2>Send Message</h2>
                
                <div class="message-info">
                    💬 Send a direct message to the farmer about this product. They will receive your inquiry and can respond through the messaging system.
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <br><br>
                        <a href="messages.php" style="color: #2196f3; font-weight: 600;">View your messages →</a>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="message">Your Message</label>
                        <textarea id="message" name="message" placeholder="Hello, I'm interested in your <?php echo htmlspecialchars($product['product_name']); ?>. Can you tell me more about..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>