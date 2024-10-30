<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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

// ดึงข้อมูลนักเรียนจากตาราง student_info โดยกรองตามคำค้นหา (ชื่อ, นามสกุล, รหัสนักเรียน, หมายเลขห้อง)
$query = "SELECT id, student_id, first_name, last_name, room_number FROM student_info 
          WHERE student_id LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR room_number LIKE ? 
          ORDER BY room_number";
$stmt = $conn->prepare($query);
$search_term = "%" . $search_keyword . "%";
$stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $students = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $students = [];
}

// การจัดการ POST request (แก้ไข, ลบข้อมูลนักเรียน)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        // ส่วนแก้ไขข้อมูล
        if (isset($_POST['student_id']) && isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['id'])) {
            $id = $_POST['id'];
            $student_id = $_POST['student_id'];
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];

            $stmt = $conn->prepare("UPDATE student_info SET student_id = ?, first_name = ?, last_name = ? WHERE id = ?");
            $stmt->bind_param("sssi", $student_id, $first_name, $last_name, $id);
            $stmt->execute();

            $_SESSION['alert_message'] = "ข้อมูลนักศึกษาถูกแก้ไขเรียบร้อยแล้ว";
            $_SESSION['alert_type'] = "success";

            header("Location: manage_students.php");
            exit();
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (isset($_POST['student_id'])) {
            $student_id = $_POST['student_id'];

            // ลบข้อมูลใน parent_info ก่อน
            $stmt = $conn->prepare("DELETE FROM parent_info WHERE student_id = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();

            // ลบข้อมูลใน student_info
            $stmt = $conn->prepare("DELETE FROM student_info WHERE student_id = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();

            $_SESSION['alert_message'] = "ข้อมูลนักศึกษาถูกลบเรียบร้อยแล้ว";
            $_SESSION['alert_type'] = "success";

            header("Location: manage_students.php");
            exit();
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update_room') {
        if (isset($_POST['room_number']) && isset($_POST['id'])) {
            $id = $_POST['id'];
            $room_number = $_POST['room_number'];

            $stmt = $conn->prepare("UPDATE student_info SET room_number = ? WHERE id = ?");
            $stmt->bind_param("si", $room_number, $id);
            $stmt->execute();

            $_SESSION['alert_message'] = "หมายเลขห้องถูกอัปเดตเรียบร้อยแล้ว";
            $_SESSION['alert_type'] = "success";

            header("Location: manage_students.php");
            exit();
        }
    }
}

// ข้อความแจ้งเตือน
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
    <title>จัดการข้อมูลนักศึกษา</title>
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
        }

        .container {
            margin-top: 80px;
            text-align: center;
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

        .btn-room {
            background-color: #0288d1;
            color: white;
        }

        .btn-room:hover {
            background-color: #0277bd;
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
            td:nth-of-type(2):before { content: "ชื่อ"; }
            td:nth-of-type(3):before { content: "นามสกุล"; }
            td:nth-of-type(4):before { content: "หมายเลขห้อง"; }
            td:nth-of-type(5):before { content: "จัดการ"; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand" href="#">จัดการข้อมูลนักศึกษา</a>
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

    <div class="container mt-4">
        <h1>จัดการข้อมูลนักศึกษา</h1>

        <!-- ฟิลเตอร์การค้นหา -->
        <form method="GET" action="manage_students.php" class="form-inline mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="ค้นหา (ชื่อ, รหัส, ห้อง)" value="<?php echo htmlspecialchars($search_keyword); ?>">
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

        <?php if (count($students) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>รหัสนักศึกษา</th>
                        <th>ชื่อ</th>
                        <th>นามสกุล</th>
                        <th>หมายเลขห้อง</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['room_number']); ?></td>
                            <td>
                                <!-- ปุ่มแก้ไขข้อมูล -->
                                <button type="button" class="btn btn-warning btn-action" data-toggle="modal" data-target="#editStudentModal-<?php echo $student['id']; ?>">แก้ไข</button>
                                
                                <!-- ปุ่มจัดการห้องพัก -->
                                <button type="button" class="btn btn-info btn-action" data-toggle="modal" data-target="#roomManageModal-<?php echo $student['id']; ?>">จัดการห้องพัก</button>
                                
                                <!-- ปุ่มลบ -->
                                <form method="POST" action="manage_students.php" style="display:inline;">
                                    <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-danger btn-action">ลบ</button>
                                </form>

                                <!-- Modal แก้ไข -->
                                <div class="modal fade" id="editStudentModal-<?php echo $student['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editStudentModalLabel-<?php echo $student['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">แก้ไขข้อมูลนักศึกษา</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form method="POST" action="manage_students.php">
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="student_id">รหัสนักศึกษา</label>
                                                        <input type="text" class="form-control" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>" required readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="first_name">ชื่อ</label>
                                                        <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="last_name">นามสกุล</label>
                                                        <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
                                                    </div>
                                                    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
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

                                <!-- Modal จัดการห้องพัก -->
                                <div class="modal fade" id="roomManageModal-<?php echo $student['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="roomManageModalLabel-<?php echo $student['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">จัดการห้องพัก</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form method="POST" action="manage_students.php">
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="room_number">หมายเลขห้อง</label>
                                                        <input type="text" class="form-control" name="room_number" value="<?php echo htmlspecialchars($student['room_number']); ?>" required>
                                                    </div>
                                                    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                                    <input type="hidden" name="action" value="update_room">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                                                    <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>ไม่มีข้อมูลนักศึกษา</p>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
