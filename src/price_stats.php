<?php
require_once 'config.php';
requireLogin();

$conn = getDBConnection();

// Get all unique products from the products table
$products_query = $conn->query("SELECT DISTINCT product_name FROM products WHERE status='active' ORDER BY product_name");

// Get selected product for chart
$selected_product = isset($_GET['product']) ? sanitize($_GET['product']) : '';

// Get current products data for comparison
$current_prices = [];
if (!empty($selected_product)) {
    $stmt = $conn->prepare("SELECT p.price_per_kg, p.created_at, u.full_name, u.location 
                            FROM products p 
                            JOIN users u ON p.farmer_id = u.user_id 
                            WHERE p.product_name = ? AND p.status = 'active'
                            ORDER BY p.created_at DESC
                            LIMIT 10");
    $stmt->bind_param("s", $selected_product);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $current_prices[] = $row;
    }
}

// Get historical price data from price_stats table (last 30 days)
$price_data = [];
$historical_prices = [];
if (!empty($selected_product)) {
    $stmt = $conn->prepare("SELECT recorded_date, AVG(price_per_kg) as avg_price, 
                            MIN(price_per_kg) as min_price, MAX(price_per_kg) as max_price,
                            COUNT(*) as entry_count
                            FROM price_stats 
                            WHERE product_name = ? 
                            AND recorded_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                            GROUP BY recorded_date 
                            ORDER BY recorded_date");
    $stmt->bind_param("s", $selected_product);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $price_data[] = $row;
    }
    
    // Get all historical entries for detailed view
    $stmt = $conn->prepare("SELECT ps.*, u.full_name 
                            FROM price_stats ps
                            JOIN users u ON ps.farmer_id = u.user_id
                            WHERE ps.product_name = ? 
                            AND ps.recorded_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                            ORDER BY ps.recorded_date DESC");
    $stmt->bind_param("s", $selected_product);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $historical_prices[] = $row;
    }
}

// Calculate statistics
$stats = [];
if (!empty($selected_product)) {
    // From historical data
    if (!empty($price_data)) {
        $all_prices = array_column($price_data, 'avg_price');
        $stats['avg_price'] = array_sum($all_prices) / count($all_prices);
        $stats['min_price'] = min(array_column($price_data, 'min_price'));
        $stats['max_price'] = max(array_column($price_data, 'max_price'));
        $stats['total_entries'] = array_sum(array_column($price_data, 'entry_count'));
        
        // Calculate price trend
        if (count($price_data) >= 2) {
            $first_price = $price_data[0]['avg_price'];
            $last_price = $price_data[count($price_data) - 1]['avg_price'];
            $stats['price_change'] = (($last_price - $first_price) / $first_price) * 100;
        } else {
            $stats['price_change'] = 0;
        }
    }
    
    // From current products
    if (!empty($current_prices)) {
        $current_avg = array_sum(array_column($current_prices, 'price_per_kg')) / count($current_prices);
        $stats['current_avg'] = $current_avg;
        $stats['current_listings'] = count($current_prices);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Price Statistics - FarmMarket</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, "Noto Sans", "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(165deg, var(--earth-bg-1) 0%, var(--earth-bg-2) 100%);
            color: var(--text-900);
        }
        
        .navbar {
            background: #fff;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(45,63,45,0.08);
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
        
        .nav-link {
            color: var(--green-700);
            text-decoration: none;
            font-weight: 800;
        }
        
        .nav-link:hover {
            text-decoration: underline;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .header {
            margin-bottom: 24px;
        }
        
        .header h1 {
            color: var(--text-900);
            margin-bottom: 6px;
        }
        
        .header p {
            color: var(--text-600);
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 25px;
            margin-bottom: 24px;
        }
        
        .card {
            background: #fff;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(45,63,45,0.08);
            border: 1px solid var(--brown-100);
        }
        
        .card h2 {
            color: var(--green-700);
            margin-bottom: 16px;
            font-size: 20px;
        }
        
        .alert {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        
        .alert-success {
            background: var(--success-100);
            color: var(--success-700);
            border: 1px solid #c8e6c9;
        }
        
        .alert-error {
            background: var(--danger-100);
            color: var(--danger-700);
            border: 1px solid #ffcdd2;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-900);
            font-weight: 700;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="number"],
        input[type="date"],
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #dfe8df;
            border-radius: 10px;
            font-size: 14px;
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        input:focus,
        select:focus {
            outline: none;
            border-color: var(--green-600);
            box-shadow: 0 0 0 3px rgba(56,142,60,0.15);
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: var(--green-700);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn:hover {
            background: var(--green-600);
        }
        
        .chart-container {
            position: relative;
            height: 400px;
            margin-top: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(45,63,45,0.08);
            border: 1px solid var(--brown-100);
            text-align: center;
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--text-600);
            margin-bottom: 8px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--green-700);
        }
        
        .stat-value.positive {
            color: var(--green-700);
        }
        
        .stat-value.negative {
            color: #d32f2f;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--text-600);
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 12px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-900);
            margin: 24px 0 12px;
        }
        
        .price-table {
            margin-top: 10px;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f6faf5;
            padding: 12px;
            text-align: left;
            color: var(--text-900);
            font-weight: 800;
            font-size: 14px;
            border-bottom: 1px solid #e3efe1;
        }
        
        td {
            padding: 12px;
            border-top: 1px solid #f0f0f0;
            color: var(--text-600);
        }
        
        tr:hover {
            background: #f6faf5;
        }
        
        @media (max-width: 968px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
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
            <h1>📊 Price Statistics & Market Trends</h1>
            <p>Analyze product prices and market trends over time</p>
        </div>
        
        <?php if (isUserType('farmer')): ?>
            <div class="card">
                <h2>View Market Trends</h2>
                <form method="GET" action="">
                    <div class="form-group">
                        <label>Select Product</label>
                        <select name="product" onchange="this.form.submit()">
                            <option value="">Choose a product...</option>
                            <?php 
                            $products_query->data_seek(0);
                            while ($product = $products_query->fetch_assoc()): 
                            ?>
                                <option value="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                        <?php echo $selected_product === $product['product_name'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($product['product_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </form>
                
                <?php if (!empty($selected_product) && !empty($price_data)): ?>
                    <div class="chart-container">
                        <canvas id="priceChart"></canvas>
                    </div>
                <?php elseif (!empty($selected_product)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📊</div>
                        <p>No price data available for this product in the last 30 days.</p>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📊</div>
                        <p>Select a product to view its price trends.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="card">
                <h2>View Market Trends</h2>
                <form method="GET" action="">
                    <div class="form-group">
                        <label>Select Product</label>
                        <select name="product" onchange="this.form.submit()">
                            <option value="">Choose a product...</option>
                            <?php while ($product = $products_query->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                        <?php echo $selected_product === $product['product_name'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($product['product_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </form>
                
                <?php if (!empty($selected_product) && !empty($price_data)): ?>
                    <div class="chart-container">
                        <canvas id="priceChart"></canvas>
                    </div>
                <?php elseif (!empty($selected_product)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📊</div>
                        <p>No price data available for this product in the last 30 days.</p>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📊</div>
                        <p>Select a product to view its price trends.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($selected_product) && !empty($stats)): ?>
            <div class="stats-grid">
                <?php if (isset($stats['current_avg'])): ?>
                <div class="stat-card">
                    <div class="stat-label">Current Avg Price</div>
                    <div class="stat-value"><?php echo formatPrice($stats['current_avg']); ?></div>
                    <div class="stat-label" style="margin-top: 5px;"><?php echo $stats['current_listings']; ?> active listings</div>
                </div>
                <?php endif; ?>
                
                <?php if (isset($stats['avg_price'])): ?>
                <div class="stat-card">
                    <div class="stat-label">30-Day Average</div>
                    <div class="stat-value"><?php echo formatPrice($stats['avg_price']); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Lowest Price</div>
                    <div class="stat-value"><?php echo formatPrice($stats['min_price']); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Highest Price</div>
                    <div class="stat-value"><?php echo formatPrice($stats['max_price']); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Price Trend</div>
                    <div class="stat-value <?php echo $stats['price_change'] >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $stats['price_change'] >= 0 ? '↑' : '↓'; ?>
                        <?php echo abs(round($stats['price_change'], 1)); ?>%
                    </div>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($current_prices)): ?>
            <h3 class="section-title">Current Market Listings for <?php echo htmlspecialchars($selected_product); ?></h3>
            <div class="card">
                <div class="price-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Farmer</th>
                                <th>Location</th>
                                <th>Price/Kg</th>
                                <th>Listed On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($current_prices as $price): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($price['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($price['location']); ?></td>
                                    <td><strong><?php echo formatPrice($price['price_per_kg']); ?></strong></td>
                                    <td><?php echo formatDate($price['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($historical_prices)): ?>
            <h3 class="section-title">Historical Price Records (Last 30 Days)</h3>
            <div class="card">
                <div class="price-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Farmer</th>
                                <th>Location</th>
                                <th>Price/Kg</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historical_prices as $price): ?>
                                <tr>
                                    <td><?php echo formatDate($price['recorded_date']); ?></td>
                                    <td><?php echo htmlspecialchars($price['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($price['location']); ?></td>
                                    <td><strong><?php echo formatPrice($price['price_per_kg']); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($price_data)): ?>
    <script>
        const ctx = document.getElementById('priceChart').getContext('2d');
        
        const chartData = {
            labels: [<?php 
                foreach ($price_data as $data) {
                    echo "'" . date('M d', strtotime($data['recorded_date'])) . "',";
                }
            ?>],
            datasets: [
                {
                    label: 'Average Price (Rs/kg)',
                    data: [<?php 
                        foreach ($price_data as $data) {
                            echo $data['avg_price'] . ',';
                        }
                    ?>],
                    borderColor: '#2e7d32',
                    backgroundColor: 'rgba(46,125,50,0.12)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#2e7d32',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                },
                {
                    label: 'Max Price (Rs/kg)',
                    data: [<?php 
                        foreach ($price_data as $data) {
                            echo $data['max_price'] . ',';
                        }
                    ?>],
                    borderColor: '#d32f2f',
                    backgroundColor: 'rgba(211,47,47,0.08)',
                    tension: 0.4,
                    fill: false,
                    pointRadius: 3,
                    borderDash: [5, 5]
                },
                {
                    label: 'Min Price (Rs/kg)',
                    data: [<?php 
                        foreach ($price_data as $data) {
                            echo $data['min_price'] . ',';
                        }
                    ?>],
                    borderColor: '#6d4c41',
                    backgroundColor: 'rgba(109,76,65,0.08)',
                    tension: 0.4,
                    fill: false,
                    pointRadius: 3,
                    borderDash: [5, 5]
                }
            ]
        };
        
        const config = {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: '<?php echo htmlspecialchars($selected_product); ?> - Price Trend (Last 30 Days)',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rs ' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) {
                                return 'Rs ' + Number(value).toFixed(2);
                            }
                        },
                        title: {
                            display: true,
                            text: 'Price (Rs/kg)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                }
            }
        };
        
        new Chart(ctx, config);
    </script>
    <?php endif; ?>
</body>
</html>
<?php $conn->close(); ?>