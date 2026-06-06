<?php
// FreshBasket AWS Assignment App
// PHP / Elastic Beanstalk / Amazon RDS MySQL

$DB_HOST = getenv('DB_HOST') ?: 'YOUR_RDS_ENDPOINT_OR_PRIVATE_IP';
$DB_USER = getenv('DB_USER') ?: 'admin';
$DB_PASS = getenv('DB_PASS') ?: 'YOUR_RDS_PASSWORD';
$DB_NAME = getenv('DB_NAME') ?: 'freshbasket';

$appName = 'FreshBasket Cloud Marketplace';

$instanceId = trim(@file_get_contents('http://169.254.169.254/latest/meta-data/instance-id')) ?: 'Elastic Beanstalk';
$az = trim(@file_get_contents('http://169.254.169.254/latest/meta-data/placement/availability-zone')) ?: 'AWS Availability Zone';
$serverTime = date('Y-m-d H:i:s');

$dbOk = false;
$dbMessage = '';
$totalOrders = 0;
$recentOrders = [];

mysqli_report(MYSQLI_REPORT_OFF);

$conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    $dbMessage = 'RDS connection failed: ' . $conn->connect_error;
} else {
    $conn->query("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(80) NOT NULL,
        product_name VARCHAR(120) NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $customer = trim($_POST['customer_name'] ?? '');
        $product = trim($_POST['product_name'] ?? '');
        $quantity = max(1, min(99, (int)($_POST['quantity'] ?? 1)));

        if ($customer !== '' && $product !== '') {
            $stmt = $conn->prepare('INSERT INTO orders (customer_name, product_name, quantity) VALUES (?, ?, ?)');
            $stmt->bind_param('ssi', $customer, $product, $quantity);
            $stmt->execute();
            $stmt->close();
        }

        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }

    $countResult = $conn->query('SELECT COUNT(*) AS total FROM orders');
    $totalOrders = $countResult ? (int)$countResult->fetch_assoc()['total'] : 0;

    if ($totalOrders === 0) {
        $seed = $conn->prepare('INSERT INTO orders (customer_name, product_name, quantity) VALUES (?, ?, ?)');
        $data = [
            ['Mia', 'Organic Tomato Box', 2],
            ['Noah', 'Fresh Herb Bundle', 1],
            ['Ava', 'Local Fruit Basket', 3]
        ];

        foreach ($data as $item) {
            $seed->bind_param('ssi', $item[0], $item[1], $item[2]);
            $seed->execute();
        }

        $seed->close();
    }

    $countResult = $conn->query('SELECT COUNT(*) AS total FROM orders');
    $totalOrders = $countResult ? (int)$countResult->fetch_assoc()['total'] : 0;

    $recentResult = $conn->query('SELECT id, customer_name, product_name, quantity, created_at FROM orders ORDER BY id DESC LIMIT 5');

    if ($recentResult) {
        while ($row = $recentResult->fetch_assoc()) {
            $recentOrders[] = $row;
        }
    }

    $dbOk = true;
    $dbMessage = 'Connected. Successful write/read operation against Amazon RDS MySQL.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>FreshBasket AWS Assignment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #f5f8ef, #eaf6f2);
            color: #123d25;
        }

        .hero {
            padding: 45px 7%;
            background: linear-gradient(135deg, #dff3d6, #e6f8ff);
        }

        h1 {
            margin: 0;
            font-size: 42px;
            color: #0f3b25;
        }

        .subtitle {
            margin-top: 10px;
            color: #4d6d5b;
            font-size: 18px;
        }

        .container {
            width: 90%;
            margin: -25px auto 50px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 28px;
        }

        .card, .panel {
            background: rgba(255,255,255,0.92);
            border: 1px solid #dbeadb;
            border-radius: 22px;
            padding: 26px;
            box-shadow: 0 15px 40px rgba(23, 71, 38, 0.10);
        }

        .card h3 {
            margin: 0 0 14px;
            color: #6a846f;
            letter-spacing: 3px;
            font-size: 13px;
            text-transform: uppercase;
        }

        .card strong {
            font-size: 30px;
            color: #123d25;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1.15fr;
            gap: 26px;
        }

        .status {
            padding: 18px;
            border-radius: 16px;
            font-weight: bold;
            background: #e6f7e9;
            color: #176b2b;
            margin: 15px 0;
        }

        .status.error {
            background: #ffeaea;
            color: #a32620;
        }

        label {
            display: block;
            margin-top: 18px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 15px;
            margin-top: 8px;
            border-radius: 14px;
            border: 1px solid #cfe0cf;
            font-size: 16px;
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 16px;
            border: none;
            border-radius: 16px;
            background: #0d4a2a;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            overflow: hidden;
            border-radius: 18px;
        }

        th, td {
            padding: 15px;
            border-bottom: 1px solid #e6efe6;
            text-align: left;
        }

        th {
            color: #6b806e;
            font-size: 13px;
            letter-spacing: 3px;
            text-transform: uppercase;
            background: #f6fbf5;
        }

        .flow {
            margin-top: 28px;
            padding: 24px;
            background: #ffffff;
            border-radius: 22px;
            border: 1px solid #dbeadb;
        }

        .flow-box {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .flow-box span {
            padding: 12px 16px;
            border-radius: 999px;
            background: #edf7ee;
            font-weight: bold;
        }

        @media (max-width: 900px) {
            .cards, .grid {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>

<section class="hero">
    <h1><?php echo htmlspecialchars($appName); ?></h1>
    <p class="subtitle">
        A fresh cloud marketplace running on Elastic Beanstalk with live Amazon RDS MySQL read/write testing.
    </p>
</section>

<main class="container">

    <section class="cards">
        <div class="card">
            <h3>Application</h3>
            <strong>Online</strong>
            <p>Served through AWS Elastic Beanstalk.</p>
        </div>

        <div class="card">
            <h3>Database</h3>
            <strong><?php echo $dbOk ? 'Connected' : 'Error'; ?></strong>
            <p>Amazon RDS MySQL read/write status.</p>
        </div>

        <div class="card">
            <h3>Total Orders</h3>
            <strong><?php echo (int)$totalOrders; ?></strong>
            <p>Live value read from RDS.</p>
        </div>

        <div class="card">
            <h3>EC2 Instance</h3>
            <strong><?php echo htmlspecialchars(substr($instanceId, 0, 16)); ?></strong>
            <p><?php echo htmlspecialchars($az); ?></p>
        </div>
    </section>

    <section class="grid">
        <div class="panel">
            <h2>RDS Connectivity Test</h2>

            <div class="status <?php echo $dbOk ? '' : 'error'; ?>">
                <?php echo htmlspecialchars($dbMessage); ?>
            </div>

            <p>
                Submitting an order writes to RDS. The table on the right reads the newest records back from RDS.
            </p>

            <form method="post">
                <label>Customer name</label>
                <input name="customer_name" value="Bhautik" required>

                <label>Product</label>
                <input name="product_name" value="Organic Veggie Box" required>

                <label>Quantity</label>
                <input name="quantity" type="number" min="1" max="99" value="1" required>

                <button type="submit">Submit Order to RDS</button>
            </form>
        </div>

        <div class="panel">
            <h2>Recent FreshBasket Orders</h2>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentOrders): ?>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><?php echo (int)$order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                <td><?php echo (int)$order['quantity']; ?></td>
                                <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No records to display.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <p>Server time: <?php echo htmlspecialchars($serverTime); ?></p>
        </div>
    </section>

    <section class="flow">
        <h2>AWS Architecture Flow</h2>
        <div class="flow-box">
            <span>User Browser</span>
            <span>Load Balancer</span>
            <span>EC2 Auto Scaling</span>
            <span>Custom AMI</span>
            <span>Multi-AZ RDS MySQL</span>
            <span>SNS Notifications</span>
        </div>
    </section>

</main>

</body>
</html>