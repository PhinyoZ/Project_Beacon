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

// ดึงข้อมูลผู้ปกครองจากตาราง parent_info โดยใช้คำค้นหา
$query = "SELECT parent_info.id, parent_info.parent_name, parent_info.contact_number, student_info.student_id, student_info.first_name, student_info.last_name 
          FROM parent_info 
          JOIN student_info ON parent_info.student_id = student_info.student_id
          WHERE parent_info.parent_name LIKE ? OR student_info.student_id LIKE ? OR student_info.first_name LIKE ? OR student_info.last_name LIKE ?";
$stmt = $conn->prepare($query);
$search_term = "%" . $search_keyword . "%";
$stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $parents = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $parents = [];
}

// ดึงข้อมูลนักเรียนจากตาราง student_info สำหรับใช้ใน Dropdown
$students_query = "SELECT student_id, first_name, last_name FROM student_info";
$students_result = $conn->query($students_query);

if ($students_result->num_rows > 0) {
    $students = $students_result->fetch_all(MYSQLI_ASSOC);
} else {
    $students = [];
}

// การลบและการแก้ไขข้อมูล
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        // ลบข้อมูล
        if ($_POST['action'] === 'delete') {
            if (isset($_POST['parent_id'])) {
                $parent_id = $_POST['parent_id'];

                // ลบข้อมูลใน parent_info
                $stmt = $conn->prepare("DELETE FROM parent_info WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("i", $parent_id);
                    $stmt->execute();

                    $_SESSION['alert_message'] = "ข้อมูลผู้ปกครองถูกลบเรียบร้อยแล้ว";
                    $_SESSION['alert_type'] = "success";

                    header("Location: manage_parents.php");
                    exit();
                } else {
                    $_SESSION['alert_message'] = "เกิดข้อผิดพลาดในการลบข้อมูล: " . $conn->error;
                    $_SESSION['alert_type'] = "error";
                }
            }
        }
        // แก้ไขข้อมูล
        elseif ($_POST['action'] === 'edit') {
            if (isset($_POST['parent_id']) && isset($_POST['parent_name']) && isset($_POST['contact_number']) && isset($_POST['student_id'])) {
                $parent_id = $_POST['parent_id'];
                $parent_name = $_POST['parent_name'];
                $contact_number = $_POST['contact_number'];
                $student_id = $_POST['student_id'];

                // อัปเดตข้อมูลใน parent_info
                $stmt = $conn->prepare("UPDATE parent_info SET parent_name = ?, contact_number = ?, student_id = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("sssi", $parent_name, $contact_number, $student_id, $parent_id);
                    $stmt->execute();

                    $_SESSION['alert_message'] = "ข้อมูลผู้ปกครองถูกแก้ไขเรียบร้อยแล้ว";
                    $_SESSION['alert_type'] = "success";

                    header("Location: manage_parents.php");
                    exit();
                } else {
                    $_SESSION['alert_message'] = "เกิดข้อผิดพลาดในการแก้ไขข้อมูล: " . $conn->error;
                    $_SESSION['alert_type'] = "error";
                }
            }
        }
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
    <title>จัดการข้อมูลผู้ปกครอง</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3e5f5;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #7b1fa2;
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
            color: #d1c4e9;
            text-decoration: underline;
        }

        .container {
            margin-top: 80px;
            text-align: center;
        }

        /* ฟิลเตอร์การค้นหา */
        .search-container {
            margin-bottom: 20px;
        }

        table {
            margin-top: 20px;
            width: 100%;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table, th, td {
            border: 1px solid #ddd;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
        }

        th {
            background-color: #b39ddb;
            color: white;
            text-align: left;
        }

        td {
            text-align: right;
        }

        .btn-action {
            margin: 5px;
        }

        .btn-edit {
            background-color: #ffc107;
            color: white;
        }

        .btn-edit:hover {
            background-color: #e0a800;
        }

        .btn-delete {
            background-color: #d32f2f;
            color: white;
        }

        .btn-delete:hover {
            background-color: #b71c1c;
        }

        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }

            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            tr {
                margin: 0 0 1rem 0;
                border: 1px solid #ddd;
            }

            td {
                border: none;
                border-bottom: 1px solid #ddd;
                position: relative;
                padding-left: 50%;
                text-align: right;
            }

            td:before {
                position: absolute;
                top: 50%;
                left: 10px;
                transform: translateY(-50%);
                white-space: nowrap;
                font-weight: bold;
                text-align: left;
            }

            td:nth-of-type(1):before { content: "รหัสนักศึกษา"; }
            td:nth-of-type(2):before { content: "ชื่อนักศึกษา"; }
            td:nth-of-type(3):before { content: "ชื่อผู้ปกครอง"; }
            td:nth-of-type(4):before { content: "เบอร์ติดต่อ"; }
            td:nth-of-type(5):before { content: "จัดการ"; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand" href="#">จัดการข้อมูลผู้ปกครอง</a>
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
        <h1>จัดการข้อมูลผู้ปกครอง</h1>

        <!-- ฟิลเตอร์การค้นหา -->
        <form method="GET" action="manage_parents.php" class="form-inline d-flex align-items-center mb-3">
            <input type="text" name="search" class="form-control mr-2" placeholder="ค้นหา (ชื่อผู้ปกครอง, รหัสนักศึกษา)" value="<?php echo htmlspecialchars($search_keyword); ?>" style="flex: 1;">
            <button type="submit" class="btn btn-primary">ค้นหา</button>
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

        <?php if (count($parents) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>รหัสนักศึกษา</th>
                        <th>ชื่อนักศึกษา</th>
                        <th>ชื่อผู้ปกครอง</th>
                        <th>เบอร์ติดต่อ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($parents as $parent): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($parent['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($parent['parent_name']); ?></td>
                            <td><?php echo htmlspecialchars($parent['contact_number']); ?></td>
                            <td>
                                <button type="button" class="btn btn-edit btn-action" data-toggle="modal" data-target="#editParentModal-<?php echo $parent['id']; ?>">แก้ไข</button>
                                
                                <!-- ปุ่มลบข้อมูล -->
                                <form method="POST" action="manage_parents.php" style="display:inline;">
                                    <input type="hidden" name="parent_id" value="<?php echo $parent['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-delete btn-action" onclick="return confirm('คุณแน่ใจว่าต้องการลบข้อมูลนี้?');">ลบ</button>
                                </form>

                                <!-- Modal สำหรับแก้ไขข้อมูล -->
                                <div class="modal fade" id="editParentModal-<?php echo $parent['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editParentModalLabel-<?php echo $parent['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editParentModalLabel-<?php echo $parent['id']; ?>">แก้ไขข้อมูลผู้ปกครอง</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form method="POST" action="manage_parents.php">
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="parent_name">ชื่อผู้ปกครอง</label>
                                                        <input type="text" class="form-control" id="parent_name" name="parent_name" value="<?php echo htmlspecialchars($parent['parent_name']); ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="contact_number">เบอร์ติดต่อ</label>
                                                        <input type="text" class="form-control" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($parent['contact_number']); ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="student_id">รหัสนักศึกษา</label>
                                                        <select class="form-control" id="student_id" name="student_id" required>
                                                            <?php foreach ($students as $student): ?>
                                                                <option value="<?php echo htmlspecialchars($student['student_id']); ?>" <?php echo ($student['student_id'] == $parent['student_id']) ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($student['student_id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <input type="hidden" name="parent_id" value="<?php echo $parent['id']; ?>">
                                                    <input type="hidden" name="action" value="edit">
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
            <p>ไม่มีข้อมูลผู้ปกครอง</p>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
