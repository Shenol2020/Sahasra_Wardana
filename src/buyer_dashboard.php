<?php
// This file is included from dashboard.php, so session and config are already loaded

$conn = getDBConnection();

// Get all active products with farmer information
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$filter_category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

$sql = "SELECT p.*, u.full_name as farmer_name, u.location as farmer_location, u.contact as farmer_contact 
        FROM products p 
        JOIN users u ON p.farmer_id = u.user_id 
        WHERE p.status = 'active'";

if (!empty($search)) {
    $sql .= " AND (p.product_name LIKE '%$search%' OR p.description LIKE '%$search%')";
}

if (!empty($filter_category)) {
    $sql .= " AND p.category = '$filter_category'";
}

$sql .= " ORDER BY p.created_at DESC";

$products = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard - FarmMarket</title>
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
            box-shadow: 0 2px 10px rgba(45, 63, 45, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 4px solid var(--green-700);
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            color: var(--green-700);
        }

        .user-info { display: flex; align-items: center; gap: 12px; }
        .user-name { color: var(--text-900); font-weight: 600; }

        .logout-btn {
            background: var(--green-700);
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            transition: background 0.2s;
        }
        .logout-btn:hover { background: var(--green-600); }

        .container { max-width: 1200px; margin: 0 auto; padding: 30px 20px; }

        .header { margin-bottom: 20px; }
        .header h1 { color: var(--text-900); margin-bottom: 6px; }
        .header p { color: var(--text-600); }

        .stats-bar {
            display: flex; gap: 20px; margin-bottom: 24px;
        }
        .stat-card {
            flex: 1; background: #fff; padding: 20px; border-radius: 12px;
            box-shadow: 0 2px 10px rgba(45, 63, 45, 0.05);
            border: 1px solid var(--brown-100);
        }
        .stat-label { color: var(--text-600); font-size: 14px; margin-bottom: 6px; }
        .stat-value { color: var(--green-700); font-size: 28px; font-weight: 800; }

        .filters {
            background: #fff; padding: 20px; border-radius: 12px; margin-bottom: 24px;
            box-shadow: 0 2px 10px rgba(45, 63, 45, 0.05);
            border: 1px solid var(--brown-100);
        }
        .filter-row { display: flex; gap: 15px; flex-wrap: wrap; }
        .filter-group { flex: 1; min-width: 200px; }
        .filter-group label { display: block; margin-bottom: 6px; color: var(--text-900); font-weight: 700; font-size: 14px; }
        .filter-group input, .filter-group select {
            width: 100%; padding: 10px; border: 2px solid #dfe8df; border-radius: 8px; font-size: 14px; background: #fff;
        }
        .filter-group input:focus, .filter-group select:focus {
            outline: none; border-color: var(--green-600); box-shadow: 0 0 0 3px rgba(56,142,60,0.15);
        }
        .filter-group button {
            padding: 12px 24px; background: var(--green-700); color: #fff; border: none; border-radius: 10px;
            cursor: pointer; font-size: 14px; font-weight: 800;
        }
        .filter-group button:hover { background: var(--green-600); }

        .products-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;
        }

        .product-card {
            background: white; border-radius: 12px; overflow: hidden;
            box-shadow: 0 2px 10px rgba(45, 63, 45, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid var(--brown-100);
        }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 6px 20px rgba(45, 63, 45, 0.15); }

        .product-image { width: 100%; height: 200px; object-fit: cover; background: #f0f0f0; }

        .product-info { padding: 18px; }
        .product-name { font-size: 20px; font-weight: 700; color: var(--text-900); margin-bottom: 8px; }
        .product-description { color: var(--text-600); font-size: 14px; margin-bottom: 12px; line-height: 1.5; }

        .product-details {
            display: flex; justify-content: space-between; margin-bottom: 12px; padding: 10px;
            background: #f6faf5; border-radius: 8px; border: 1px solid #e3efe1;
        }
        .detail-item { text-align: center; }
        .detail-label { font-size: 12px; color: var(--text-600); margin-bottom: 4px; }
        .detail-value { font-size: 16px; font-weight: 800; color: var(--green-700); }

        .farmer-info {
            padding: 12px; background: #fbfbfb; border-radius: 8px; margin-bottom: 12px; border: 1px solid #f0f0f0;
        }
        .farmer-info h4 { color: var(--text-900); font-size: 14px; margin-bottom: 6px; }
        .farmer-detail { font-size: 13px; color: var(--text-600); margin-bottom: 4px; }

        .contact-btn {
            display: block; width: 100%; padding: 12px;
            background: var(--green-700); color: white; text-align: center; text-decoration: none;
            border-radius: 10px; font-weight: 800; transition: background 0.2s;
        }
        .contact-btn:hover { background: var(--green-600); }

        .empty-state {
            text-align: center; padding: 60px 20px; background: white; border-radius: 12px;
            border: 1px solid var(--brown-100);
        }
        .empty-state h2 { color: var(--text-900); margin-bottom: 8px; }
        .empty-state p { color: var(--text-600); }

        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 10px; padding: 12px 16px; }
            .products-grid { grid-template-columns: 1fr; }
            .filter-row { flex-direction: column; }
            .stats-bar { flex-direction: column; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <img src="assets/images/head2.png" alt="Logo" style="height: 55px;px; vertical-align:middle; margin-right:8px;" />
        </div>
        <div class="user-info">
            <span class="user-name">👤 <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <a href="price_stats.php" class="logout-btn" style="background: #4caf50;">📊 Price Stats</a>
            <a href="messages.php" class="logout-btn" style="background: #ff9800;">💬 Messages</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="header">
            <h1>Available Products</h1>
            <p>Browse fresh agricultural products directly from farmers</p>
        </div>
        
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-label">Total Products</div>
                <div class="stat-value"><?php echo $products->num_rows; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Active Farmers</div>
                <div class="stat-value"><?php 
                    $farmers_count = $conn->query("SELECT COUNT(DISTINCT farmer_id) as count FROM products WHERE status='active'")->fetch_assoc()['count'];
                    echo $farmers_count;
                ?></div>
            </div>
        </div>
        
        <div class="filters">
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Search Products</label>
                        <input type="text" name="search" placeholder="Enter product name..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="filter-group">
                        <label>Category</label>
                        <select name="category">
                            <option value="">All Categories</option>
                            <option value="vegetable" <?php echo $filter_category == 'vegetable' ? 'selected' : ''; ?>>Vegetables</option>
                            <option value="fruit" <?php echo $filter_category == 'fruit' ? 'selected' : ''; ?>>Fruits</option>
                            <option value="grain" <?php echo $filter_category == 'grain' ? 'selected' : ''; ?>>Grains</option>
                            <option value="other" <?php echo $filter_category == 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="filter-group" style="display: flex; align-items: flex-end;">
                        <button type="submit">Filter</button>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if ($products->num_rows > 0): ?>
            <div class="products-grid">
                <?php while ($product = $products->fetch_assoc()): ?>
                    <div class="product-card">
                        <?php if ($product['image_path']): ?>
                            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="Product" class="product-image">
                        <?php else: ?>
                            <div class="product-image" style="display: flex; align-items: center; justify-content: center; font-size: 48px;">
                                🌾
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                            
                            <div class="product-details">
                                <div class="detail-item">
                                    <div class="detail-label">Price/kg</div>
                                    <div class="detail-value"><?php echo formatPrice($product['price_per_kg']); ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Available</div>
                                    <div class="detail-value"><?php echo $product['quantity_kg']; ?> kg</div>
                                </div>
                            </div>
                            
                            <div class="farmer-info">
                                <h4>👨‍🌾 <?php echo htmlspecialchars($product['farmer_name']); ?></h4>
                                <div class="farmer-detail">📍 <?php echo htmlspecialchars($product['farmer_location']); ?></div>
                                <div class="farmer-detail">📞 <?php echo htmlspecialchars($product['farmer_contact']); ?></div>
                            </div>
                            
                            <a href="send_message.php?product_id=<?php echo $product['product_id']; ?>" class="contact-btn">
                                💬 Contact Farmer
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>No Products Found</h2>
                <p>Check back later for new listings!</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>