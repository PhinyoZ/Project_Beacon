<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

// ฟิลเตอร์การค้นหา
$search_keyword = "";
if (isset($_GET['search'])) {
    $search_keyword = $_GET['search'];
}

// ดึงข้อมูลจากตาราง user โดยกรองตามคำค้นหา
$query = "SELECT id, user_id, name_line, role, status, created_at 
          FROM user 
          WHERE (user_id LIKE ? OR name_line LIKE ? OR role LIKE ? OR status LIKE ?)";
$stmt = $conn->prepare($query);
$search_term = "%" . $search_keyword . "%";
$stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $users = [];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['user_id']) && isset($_POST['new_role'])) {
        $user_id = $_POST['user_id'];
        $new_role = $_POST['new_role'];

        $stmt = $conn->prepare("UPDATE user SET role = ? WHERE user_id = ?");
        $stmt->bind_param("ss", $new_role, $user_id);
        $stmt->execute();

        $_SESSION['alert_message'] = "บทบาทของผู้ใช้ถูกแก้ไขเรียบร้อยแล้ว";
        $_SESSION['alert_type'] = "success";

        header("Location: manage_users.php");
        exit();
    }

    if (isset($_POST['user_id']) && isset($_POST['action'])) {
        $user_id = $_POST['user_id'];
        $action = $_POST['action'];

        if ($action == 'suspend') {
            $stmt = $conn->prepare("UPDATE user SET status = 'suspended' WHERE user_id = ?");
            $stmt->bind_param("s", $user_id);
            $stmt->execute();

            $_SESSION['alert_message'] = "บัญชีผู้ใช้ถูกระงับการใช้งานแล้ว";
            $_SESSION['alert_type'] = "warning";
        } elseif ($action == 'activate') {
            $stmt = $conn->prepare("UPDATE user SET status = 'active' WHERE user_id = ?");
            $stmt->bind_param("s", $user_id);
            $stmt->execute();

            $_SESSION['alert_message'] = "บัญชีผู้ใช้ถูกเปิดใช้งานแล้ว";
            $_SESSION['alert_type'] = "success";
        }

        header("Location: manage_users.php");
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
    <title>จัดการผู้ใช้</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3e5f5; /* พื้นหลังสีม่วงอ่อน */
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .navbar {
            background-color: #7b1fa2; /* สีม่วงเข้ม */
            padding: 10px 20px;
        }

        .navbar-brand {
            color: white !important;
            font-size: 1.2rem;
        }

        .navbar-nav .nav-link {
            color: white !important;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #d1c4e9; /* สีม่วงอ่อนเมื่อ hover */
            text-decoration: underline;
        }

        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 20px;
        }

        h1 {
            color: #7b1fa2;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            background-color: white;
            border-collapse: collapse;
            margin-bottom: 20px;
            overflow-x: auto;
            display: block;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            white-space: nowrap;
        }

        th {
            background-color: #f2f2f2;
        }

        .btn-action {
            margin: 5px;
            padding: 10px 20px;
            border-radius: 5px;
            color: white;
            text-align: center;
            display: inline-block;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-suspend {
            background-color: #ff7043;
        }

        .btn-activate {
            background-color: #66bb6a;
        }

        .btn-edit {
            background-color: #42a5f5;
        }

        .btn-suspend:hover {
            background-color: #e64a19;
        }

        .btn-activate:hover {
            background-color: #388e3c;
        }

        .btn-edit:hover {
            background-color: #1e88e5;
        }

        @media (max-width: 768px) {
            table, th, td {
                font-size: 12px;
            }

            .btn-action {
                font-size: 10px;
                padding: 8px 15px;
            }

            /* ปรับการแสดงผลให้เป็น card layout บนมือถือ */
            table {
                display: block;
                border: 0;
            }

            thead {
                display: none;
            }

            tr {
                display: block;
                margin-bottom: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
                padding: 10px;
                background-color: white;
            }

            td {
                display: block;
                text-align: right;
                font-size: 14px;
                border: none;
            }

            td:before {
                content: attr(data-label);
                float: left;
                font-weight: bold;
                color: #7b1fa2;
            }

            td:last-child {
                border-bottom: 0;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand" href="#">จัดการผู้ใช้</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="welcome3.php">หน้าหลักแอดมิน</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">ออกจากระบบ</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1>จัดการผู้ใช้</h1>

        <!-- ฟิลเตอร์การค้นหา -->
        <form method="GET" action="manage_users.php" class="form-inline mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="ค้นหา (ชื่อ, รหัส, บทบาท, สถานะ)" value="<?php echo htmlspecialchars($search_keyword); ?>">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">ค้นหา</button>
                </div>
            </div>
        </form>

        <?php if (!empty($alert_message)): ?>
            <script>
                Swal.fire({
                    icon: '<?php echo $alert_type; ?>',
                    title: '<?php echo ($alert_type == "success") ? "สำเร็จ" : "คำเตือน"; ?>',
                    text: '<?php echo $alert_message; ?>',
                });
            </script>
        <?php endif; ?>
        <?php if (count($users) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ชื่อผู้ใช้ (LINE ID)</th>
                        <th>ชื่อใน LINE</th>
                        <th>สถานะ</th>
                        <th>บทบาท</th>
                        <th>วันที่สร้าง</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td data-label="(LINE ID)">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td data-label="ชื่อไลน์"><?php echo htmlspecialchars($user['name_line']); ?></td>
                            <td data-label="สถานะ"><?php echo htmlspecialchars($user['status']); ?></td>
                            <td data-label="บทบาท"><?php echo htmlspecialchars($user['role']); ?></td>
                            <td data-label="วันที่สร้าง"><?php echo htmlspecialchars($user['created_at']); ?></td>
                            <td data-label="จัดการ">
                                <?php if ($user['status'] == 'active'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        <input type="hidden" name="action" value="suspend">
                                        <button type="submit" class="btn btn-suspend btn-action">ระงับการใช้งาน</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        <input type="hidden" name="action" value="activate">
                                        <button type="submit" class="btn btn-activate btn-action">เปิดใช้งาน</button>
                                    </form>
                                <?php endif; ?>
                                <button type="button" class="btn btn-edit btn-action" data-toggle="modal" data-target="#editRoleModal-<?php echo $user['id']; ?>">บทบาท</button>
                                
                                <!-- Modal สำหรับแก้ไขบทบาท -->
                                <div class="modal fade" id="editRoleModal-<?php echo $user['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editRoleModalLabel-<?php echo $user['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editRoleModalLabel-<?php echo $user['id']; ?>">แก้ไขบทบาท</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form method="POST" action="manage_users.php">
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="new_role">เลือกบทบาทใหม่</label>
                                                        <select class="form-control" id="new_role" name="new_role">
                                                            <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>ผู้ดูแลระบบ</option>
                                                            <option value="officer" <?php echo ($user['role'] == 'officer') ? 'selected' : ''; ?>>เจ้าหน้าที่</option>
                                                            <option value="parent" <?php echo ($user['role'] == 'parent') ? 'selected' : ''; ?>>ผู้ปกครอง</option>
                                                            <option value="student" <?php echo ($user['role'] == 'student') ? 'selected' : ''; ?>>นักศึกษา</option>
                                                        </select>
                                                    </div>
                                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                                                    <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- จบ Modal -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>ไม่มีข้อมูลผู้ใช้</p>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
