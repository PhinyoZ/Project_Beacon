<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'officer') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$user = $_SESSION['user'];
$display_name = $user['displayName'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยินดีต้อนรับ ผู้ดูแลหอพัก</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e3f2fd; /* พื้นหลังสีน้ำเงินอ่อน */
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .navbar {
            background-color: #2196f3; /* สีน้ำเงิน */
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
            color: #bbdefb; /* สีน้ำเงินอ่อนเมื่อ hover */
            text-decoration: underline;
        }

        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center; /* จัดตำแหน่งปุ่มให้อยู่ตรงกลาง */
            padding: 20px;
            text-align: center;
        }

        h1 {
            color: #2196f3;
            margin-bottom: 20px;
        }

        .btn-custom {
            margin-top: 15px;
            padding: 20px 40px; /* ขนาดปุ่มใหญ่ขึ้น */
            border-radius: 10px;
            font-size: 1.3rem; /* ขนาดตัวอักษรใหญ่ขึ้น */
            text-decoration: none;
            display: inline-block;
            transition: all 0.4s ease;
            width: 300px; /* ขนาดของปุ่มกว้างขึ้น */
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-custom:hover {
            transform: scale(1.05); /* ขยายเล็กน้อยเมื่อ hover */
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2); /* เพิ่มความชัดของเงา */
        }

        .btn-student, .btn-parent, .btn-officer, .btn-dorm-student {
            background-color: #42a5f5;
            color: white;
            background-image: linear-gradient(45deg, #42a5f5, #2196f3);
        }

        .btn-student:hover {
            background-image: linear-gradient(45deg, #1e88e5, #1565c0);
            color: #fff; /* เปลี่ยนสีตัวอักษรเมื่อ hover */
        }

        .btn-parent:hover {
            background-image: linear-gradient(45deg, #1976d2, #0d47a1);
            color: #fff;
        }

        .btn-officer:hover {
            background-image: linear-gradient(45deg, #1a237e, #0d47a1);
            color: #fff;
        }

        .btn-dorm-student:hover {
            background-image: linear-gradient(45deg, #0d47a1, #0d47a1);
            color: #fff;
        }

        @media (max-width: 768px) {
            .btn-custom {
                font-size: 1.2rem; /* ขนาดตัวอักษรใหญ่ขึ้น */
                padding: 15px 30px; /* ขนาดปุ่มใหญ่ขึ้น */
                width: 100%; /* ปรับให้ปุ่มกว้างเต็มหน้าจอ */
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand" href="#">ยินดีต้อนรับ, <?php echo htmlspecialchars($display_name); ?></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="welcome4.php">หน้าหลัก</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="of_manage_students.php">จัดการข้อมูลนักศึกษา</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="of_manage_parents.php">จัดการข้อมูลปกครอง</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="of_becon.php">ดูข้อมูลบีคอน</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">ออกจากระบบ</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1>เจ้าหน้าที่</h1>
        <p>ยินดีต้อนรับสู่หน้าหลักเจ้าหน้าที่! คุณสามารถจัดการข้อมูลต่าง ๆ ได้จากที่นี่:</p>
        <a href="of_manage_students.php" class="btn-custom btn-student">จัดการนักศึกษา</a>
        <a href="of_manage_parents.php" class="btn-custom btn-parent">จัดการผู้ปกครอง</a>
        <a href="of_becon.php" class="btn-custom btn-officer">ดูข้อมูลบีคอน</a>
        <a href="https://nvqdgs-3000.csb.app" class="btn-custom btn-dorm-student" target="_blank">แสดงนักศึกษาในหอพัก</a> <!-- ปุ่มใหม่ -->
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
