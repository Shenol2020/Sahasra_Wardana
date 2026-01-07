<?php
// This file is included from dashboard.php

$conn = getDBConnection();
$farmer_id = $_SESSION['user_id'];

// Handle product deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ? AND farmer_id = ?");
    $stmt->bind_param("ii", $product_id, $farmer_id);
    $stmt->execute();
    $stmt->close();
    header('Location: dashboard.php');
    exit();
}

// Get farmer's products
$products = $conn->query("SELECT * FROM products WHERE farmer_id = $farmer_id ORDER BY created_at DESC");

// Get message count
$msg_count = $conn->query("SELECT COUNT(*) as count FROM messages WHERE receiver_id = $farmer_id AND is_read = 0")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - FarmMarket</title>
    <style>
        :root {
            --green-700: #2e7d32;
            --green-600: #388e3c;
            --green-100: #e8f5e9;
            --brown-700: #6d4c41;
            --brown-600: #8d6e63;
            --brown-100: #efebe9;
            --earth-bg-1: #f1f8e9;
            --earth-bg-2: #f9f6ef;
            --text-900: #2b2b2b;
            --text-600: #5b5b5b;
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

        .logo {
            font-size: 24px; font-weight: 800; color: var(--green-700);
            display: flex; align-items: center;
        }
        .logo img {
            height: 28px;
            margin-right: 8px;
            vertical-align: middle;
        }

        .user-info { display: flex; align-items: center; gap: 12px; }
        .user-name { color: var(--text-900); font-weight: 700; }

        .nav-btn {
            background: var(--green-700); color: white; padding: 8px 16px; border-radius: 8px;
            text-decoration: none; font-size: 14px; transition: background 0.2s; position: relative; font-weight: 800;
        }
        .nav-btn:hover { background: var(--green-600); }

        .badge {
            position: absolute; top: -8px; right: -8px; background: #ff4444; color: #fff;
            border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 800;
        }

        .container { max-width: 1200px; margin: 0 auto; padding: 30px 20px; }

        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .header-left h1 { color: var(--text-900); margin-bottom: 6px; }
        .header-left p { color: var(--text-600); }

        .add-btn {
            background: var(--green-700); color: #fff; padding: 12px 24px; border-radius: 10px;
            text-decoration: none; font-weight: 800; transition: background 0.2s;
        }
        .add-btn:hover { background: var(--green-600); }

        .stats-bar {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px; margin-bottom: 24px;
        }
        .stat-card {
            background: #fff; padding: 20px; border-radius: 12px;
            box-shadow: 0 2px 10px rgba(45,63,45,0.05);
            border: 1px solid var(--brown-100);
        }
        .stat-label { color: var(--text-600); font-size: 14px; margin-bottom: 6px; }
        .stat-value { color: var(--green-700); font-size: 28px; font-weight: 800; }

        .products-table {
            background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(45,63,45,0.05);
            overflow: hidden; border: 1px solid var(--brown-100);
        }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f6faf5; }
        th {
            padding: 15px; text-align: left; color: var(--text-900);
            font-weight: 800; font-size: 14px; border-bottom: 1px solid #e3efe1;
        }
        td { padding: 15px; border-top: 1px solid #f0f0f0; color: var(--text-600); }

        .product-image-small {
            width: 60px; height: 60px; object-fit: cover; border-radius: 8px; background: #f0f0f0;
        }

        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 800; }
        .status-active { background: #e8f5e9; color: var(--green-700); }
        .status-inactive { background: #ffebee; color: #f44336; }

        .action-btns { display: flex; gap: 10px; }
        .btn-edit {
            padding: 8px 16px; background: var(--brown-700); color: #fff; text-decoration: none;
            border-radius: 8px; font-size: 13px; transition: background 0.2s; font-weight: 800;
        }
        .btn-edit:hover { background: var(--brown-600); }
        .btn-delete {
            padding: 8px 16px; background: #f44336; color: #fff; text-decoration: none;
            border-radius: 8px; font-size: 13px; transition: background 0.2s; font-weight: 800;
        }
        .btn-delete:hover { background: #d32f2f; }

        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state h2 { color: var(--text-900); margin-bottom: 8px; }
        .empty-state p { color: var(--text-600); margin-bottom: 16px; }

        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 12px; padding: 12px 16px; }
            .header { flex-direction: column; gap: 16px; text-align: center; }
            .products-table { overflow-x: auto; }
            table { min-width: 800px; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <img src="assets/images/head2.png" alt="Logo" style="height: 55px;px; vertical-align:middle; margin-right:8px;" />
        </div>
        <div class="user-info">
            <span class="user-name">👨‍🌾 <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <a href="price_stats.php" class="nav-btn" style="background: #4caf50;">📊 Price Stats</a>
            <a href="messages.php" class="nav-btn" style="background: #ff9800;">
                💬 Messages
                <?php if ($msg_count > 0): ?>
                    <span class="badge"><?php echo $msg_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="logout.php" class="nav-btn">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="header">
            <div class="header-left">
                <h1>My Products</h1>
                <p>Manage your product listings</p>
            </div>
            <a href="add_product.php" class="add-btn">+ Add New Product</a>
        </div>
        
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-label">Total Products</div>
                <div class="stat-value"><?php echo $products->num_rows; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Active Listings</div>
                <div class="stat-value"><?php 
                    echo $conn->query("SELECT COUNT(*) as count FROM products WHERE farmer_id = $farmer_id AND status='active'")->fetch_assoc()['count'];
                ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Unread Messages</div>
                <div class="stat-value"><?php echo $msg_count; ?></div>
            </div>
        </div>
        
        <div class="products-table">
            <?php if ($products->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Price/kg</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $products->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($product['image_path']): ?>
                                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" class="product-image-small" alt="Product">
                                    <?php else: ?>
                                        <div class="product-image-small" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center;">🌾</div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($product['product_name']); ?></strong></td>
                                <td><?php echo formatPrice($product['price_per_kg']); ?></td>
                                <td><?php echo $product['quantity_kg']; ?> kg</td>
                                <td>
                                    <span class="status-badge status-<?php echo $product['status']; ?>">
                                        <?php echo ucfirst($product['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($product['created_at']); ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="btn-edit">Edit</a>
                                        <a href="?delete=<?php echo $product['product_id']; ?>" class="btn-delete" onclick="return confirm('Delete this product?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h2>No Products Yet</h2>
                    <p>Start by adding your first product!</p>
                    <a href="add_product.php" class="add-btn">+ Add Product</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>