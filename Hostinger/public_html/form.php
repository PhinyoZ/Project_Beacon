<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php'; // เพิ่มการเชื่อมต่อฐานข้อมูล

$user = $_SESSION['user'];
$userId = $user['userId'];
$display_name = $user['displayName'];

$stmt = $conn->prepare("SELECT * FROM student_info WHERE userId = ?");
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();
$student_info = $result->fetch_assoc();

$alert_message = isset($_SESSION['alert_message']) ? $_SESSION['alert_message'] : '';
$alert_type = isset($_SESSION['alert_type']) ? $_SESSION['alert_type'] : '';

// ลบค่าเซสชันหลังจากใช้งานเพื่อไม่ให้แสดงซ้ำ
unset($_SESSION['alert_message']);
unset($_SESSION['alert_type']);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กรอกข้อมูลเพิ่มเติม</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding-top: 56px; /* Adjust according to your navbar height */
        }

        /* Navbar */
        .navbar-custom {
            background-color: #1a6f5d;
        }
        .navbar-custom .navbar-brand,
        .navbar-custom .navbar-nav .nav-link {
            color: white;
        }

        /* Navbar hover */
        .navbar-custom .nav-link:hover {
            background-image: linear-gradient(135deg, #1a6f5d 0%, #28a745 100%);
            color: white;
            border-radius: 8px;
            padding: 5px 10px;
            transition: background-color 0.4s ease, padding 0.4s ease;
        }

        .container {
            max-width: 600px;
            margin: 100px auto 0;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        /* Button */
        .btn-custom {
            display: block;
            width: 100%;
            text-align: center;
            padding: 10px;
            margin-top: 20px;
            border: none;
            border-radius: 4px;
            background-color: #1a6f5d;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.4s ease;
        }

        /* Button hover */
        .btn-custom:hover {
            background-image: linear-gradient(135deg, #1a6f5d 0%, #28a745 100%);
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .container {
                margin: 18px auto;
                padding: 15px;
            }
            h1 {
                font-size: 22px;
            }
            .btn-custom {
                padding: 11px;
                font-size: 14px;
            }
            .navbar-custom .navbar-brand,
            .navbar-custom .navbar-nav .nav-link {
                font-size: 15px;
            }
            .form-control {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
        <a class="navbar-brand" href="#">ยินดีต้อนรับ, <?php echo htmlspecialchars($display_name); ?></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="welcome2.php">หน้าหลัก</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">ออกจากระบบ</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <h1>กรอกข้อมูลเพิ่มเติม</h1>
        <?php if ($student_info): ?>
            <div id="infoDisplay">
                <p><strong>รหัสนักศึกษา:</strong> <?php echo htmlspecialchars($student_info['student_id']); ?></p>
                <p><strong>ชื่อ:</strong> <?php echo htmlspecialchars($student_info['first_name']); ?></p>
                <p><strong>นามสกุล:</strong> <?php echo htmlspecialchars($student_info['last_name']); ?></p>
                <p><strong>เลขห้อง:</strong> <?php echo htmlspecialchars($student_info['room_number']); ?></p>
                <button class="btn btn-custom" id="editBtn">แก้ไขข้อมูล</button>
            </div>
        <?php endif; ?>

        <form method="post" action="save_student_info.php" autocomplete="on" id="infoForm" style="<?php echo $student_info ? 'display:none;' : ''; ?>">
            <div class="form-group">
                <label for="display_name">ชื่อไลน์:</label>
                <select name="display_name" id="display_name" class="form-control" required autocomplete="name">
                    <option value="<?php echo htmlspecialchars($display_name); ?>"><?php echo htmlspecialchars($display_name); ?></option>
                </select>
            </div>
            <div class="form-group">
                <label for="student_id">รหัสนักศึกษา:</label>
                <input type="text" name="student_id" id="student_id" class="form-control" required autocomplete="student-id" value="<?php echo htmlspecialchars($student_info['student_id'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="first_name">ชื่อ:</label>
                <input type="text" name="first_name" id="first_name" class="form-control" required autocomplete="given-name" value="<?php echo htmlspecialchars($student_info['first_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="last_name">นามสกุล:</label>
                <input type="text" name="last_name" id="last_name" class="form-control" required autocomplete="family-name" value="<?php echo htmlspecialchars($student_info['last_name'] ?? ''); ?>">
            </div>
            <button type="submit" class="btn btn-custom">บันทึกข้อมูล</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($alert_message): ?>
                Swal.fire({
                    icon: '<?php echo $alert_type; ?>',
                    title: '<?php echo $alert_message; ?>'
                });
            <?php endif; ?>

            $('#editBtn').click(function() {
                $('#infoDisplay').hide();
                $('#infoForm').show();
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
