<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'officer') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

// ดึงข้อมูลจากฐานข้อมูล
$query = "SELECT dm, beacon_name, beacon_place, beacon_status, created_at FROM beacon";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $beacons = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $beacons = [];
}

$alert_message = isset($_SESSION['alert_message']) ? $_SESSION['alert_message'] : '';
$alert_type = isset($_SESSION['alert_type']) ? $_SESSION['alert_type'] : '';

unset($_SESSION['alert_message']);
unset($_SESSION['alert_type']);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ดูข้อมูลบีคอน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e3f2fd;
            font-family: Arial, sans-serif;
        }
        .navbar {
            background-color: #2196f3;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .container {
            margin-top: 20px;
        }
        h1 {
            color: #000000;
            text-align: center;
        }
        .beacon-card {
            background-color: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .status-enabled {
            color: green;
            font-weight: bold;
        }
        .status-disabled {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">ดูข้อมูลบีคอน</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="welcome4.php">หน้าหลักเจ้าหน้าที่</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">ออกจากระบบ</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

    <!-- Container -->
    <div class="container">
        <h1>ดูข้อมูลบีคอน</h1>

        <!-- แสดงข้อมูลบีคอน -->
        <?php foreach ($beacons as $beacon): ?>
            <div class="beacon-card">
                <div class="row">
                    <div class="col-md-8">
                        <p><strong>DM:</strong> <?php echo htmlspecialchars($beacon['dm']); ?></p>
                        <p><strong>ชื่อบีคอน:</strong> <?php echo htmlspecialchars($beacon['beacon_name']); ?></p>
                        <p><strong>สถานที่ติดตั้ง:</strong> <?php echo htmlspecialchars($beacon['beacon_place']); ?></p>
                        <p><strong>สถานะ:</strong> 
                            <span class="<?php echo $beacon['beacon_status'] === 'enable' ? 'status-enabled' : 'status-disabled'; ?>">
                                <?php echo $beacon['beacon_status'] === 'enable' ? 'เปิดใช้งานอยู่' : 'ปิดใช้งานอยู่'; ?>
                            </span>
                        </p>
                        <p><strong>วันที่สร้าง:</strong> <?php echo htmlspecialchars($beacon['created_at']); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
