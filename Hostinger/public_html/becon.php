<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
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

// ตรวจสอบการเพิ่มหรือแก้ไขข้อมูล
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $dm = $_POST['dm'];
        $beacon_name = $_POST['beacon_name'] ?? null;
        $beacon_place = $_POST['beacon_place'] ?? null;

        if ($action === 'add') {
            // เพิ่มข้อมูลบีคอนใหม่
            $stmt = $conn->prepare("INSERT INTO beacon (dm, beacon_name, beacon_place, beacon_status, created_at) VALUES (?, ?, ?, 'enable', NOW())");
            $stmt->bind_param("sss", $dm, $beacon_name, $beacon_place);
            $stmt->execute();
            $_SESSION['alert_message'] = "เพิ่มบีคอนใหม่สำเร็จ!";
            $_SESSION['alert_type'] = "success";

        } elseif ($action === 'edit') {
            // แก้ไขข้อมูลบีคอน
            $stmt = $conn->prepare("UPDATE beacon SET beacon_name = ?, beacon_place = ? WHERE dm = ?");
            $stmt->bind_param("sss", $beacon_name, $beacon_place, $dm);
            $stmt->execute();
            $_SESSION['alert_message'] = "แก้ไขบีคอนสำเร็จ!";
            $_SESSION['alert_type'] = "success";

        } elseif ($action === 'enable' || $action === 'disable') {
            // เปลี่ยนสถานะบีคอน (เปิด/ปิดการใช้งาน)
            $stmt = $conn->prepare("UPDATE beacon SET beacon_status = ? WHERE dm = ?");
            $stmt->bind_param("ss", $action, $dm);
            $stmt->execute();
            if ($action === 'enable') {
                $_SESSION['alert_message'] = "เปิดใช้งานบีคอนสำเร็จ!";
                $_SESSION['alert_type'] = "success";
            } else {
                $_SESSION['alert_message'] = "ปิดใช้งานบีคอนสำเร็จ!";
                $_SESSION['alert_type'] = "warning";
            }
        }

        header("Location: becon.php");
        exit();
    }
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
    <title>จัดการข้อมูลบีคอน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f3e5f5;
            font-family: Arial, sans-serif;
        }
        .navbar {
            background-color: #8e24aa;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .container {
            margin-top: 20px;
        }
        h1 {
            color: #8e24aa;
            text-align: center;
        }
        .beacon-card {
            background-color: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-action {
            width: 100px;
            margin: 5px 0;
        }
        .btn-add {
            background-color: #ab47bc;
            color: white;
            margin-bottom: 20px;
        }
        .btn-add:hover {
            background-color: #8e24aa;
        }
        @media (max-width: 768px) {
            .btn-action {
                width: 100%;
                margin: 10px 0;
            }
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
        <a class="navbar-brand" href="#">จัดการข้อมูลบีคอน</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="welcome3.php">หน้าหลักแอดมิน</a>
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
        <h1>จัดการข้อมูลบีคอน</h1>

        <!-- แจ้งเตือนการทำงาน -->
        <?php if ($alert_message): ?>
            <script>
                Swal.fire({
                    icon: '<?php echo $alert_type; ?>',
                    title: '<?php echo $alert_message; ?>',
                    showConfirmButton: false,
                    timer: 1500
                });
            </script>
        <?php endif; ?>

        <!-- ปุ่มเพิ่มข้อมูล -->
        <div class="text-end">
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addModal">เพิ่มข้อมูล</button>
        </div>

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
                    <div class="col-md-4 text-end">
                        <form method="POST">
                            <input type="hidden" name="dm" value="<?php echo $beacon['dm']; ?>">
                            <button type="button" class="btn btn-warning btn-action" data-bs-toggle="modal" data-bs-target="#editModal-<?php echo $beacon['dm']; ?>">แก้ไข</button>
                            <?php if ($beacon['beacon_status'] === 'enable'): ?>
                                <button type="submit" name="action" value="disable" class="btn btn-danger btn-action">ปิดใช้งาน</button>
                            <?php else: ?>
                                <button type="submit" name="action" value="enable" class="btn btn-success btn-action">เปิดใช้งาน</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal แก้ไขข้อมูล -->
            <div class="modal fade" id="editModal-<?php echo $beacon['dm']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">แก้ไขข้อมูลบีคอน</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="dm" class="form-label">DM</label>
                                    <input type="text" class="form-control" name="dm" value="<?php echo $beacon['dm']; ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="beacon_name" class="form-label">ชื่อบีคอน</label>
                                    <input type="text" class="form-control" name="beacon_name" value="<?php echo $beacon['beacon_name']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="beacon_place" class="form-label">สถานที่ติดตั้ง</label>
                                    <input type="text" class="form-control" name="beacon_place" value="<?php echo $beacon['beacon_place']; ?>" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                <button type="submit" name="action" value="edit" class="btn btn-primary">บันทึก</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Modal เพิ่มข้อมูล -->
        <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">เพิ่มข้อมูลบีคอน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="dm" class="form-label">DM</label>
                                <input type="text" class="form-control" name="dm" required>
                            </div>
                            <div class="mb-3">
                                <label for="beacon_name" class="form-label">ชื่อบีคอน</label>
                                <input type="text" class="form-control" name="beacon_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="beacon_place" class="form-label">สถานที่ติดตั้ง</label>
                                <input type="text" class="form-control" name="beacon_place" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                            <button type="submit" name="action" value="add" class="btn btn-primary">บันทึก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
