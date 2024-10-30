<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
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
    <title>ยินดีต้อนรับ ผู้ดูแลระบบ</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3e5f5; /* พื้นหลังสีม่วงอ่อน */
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .navbar {
            background-color: #8e24aa; /* สีม่วงเข้ม */
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
            align-items: center; /* จัดตำแหน่งปุ่มให้อยู่ตรงกลาง */
            padding: 20px;
            text-align: center;
        }

        h1 {
            color: #8e24aa;
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

        .btn-student, .btn-parent, .btn-officer, .btn-user, .btn-dorm-student {
            background-color: #ab47bc;
            color: white;
            background-image: linear-gradient(45deg, #ab47bc, #8e24aa);
        }

        .btn-student:hover {
            background-image: linear-gradient(45deg, #8e24aa, #7b1fa2);
            color: #fff; /* เปลี่ยนสีตัวอักษรเมื่อ hover */
        }

        .btn-parent:hover {
            background-image: linear-gradient(45deg, #9c27b0, #6a1b9a);
            color: #fff;
        }

        .btn-officer:hover {
            background-image: linear-gradient(45deg, #aa00ff, #6200ea);
            color: #fff;
        }

        .btn-user:hover {
            background-image: linear-gradient(45deg, #9575cd, #512da8);
            color: #fff;
        }

        .btn-dorm-student:hover {
            background-image: linear-gradient(45deg, #6a1b9a, #38006b);
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
                    <a class="nav-link" href="welcome3.php">หน้าหลัก</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_students.php">จัดการข้อมูลนักศึกษา</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_parents.php">จัดการผู้ข้อมูลปกครอง</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_users.php">จัดการผู้ใช้</a></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="becon.php">จัดข้อมูลบีคอน</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">ออกจากระบบ</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1>ผู้ดูแลระบบ</h1>
        <p>ยินดีต้อนรับสู่หน้าผู้ดูแลระบบ! คุณสามารถจัดการข้อมูลต่าง ๆ ได้จากที่นี่:</p>
        <a href="manage_students.php" class="btn-custom btn-student">จัดการข้อมูลนักศึกษา</a>
        <a href="manage_parents.php" class="btn-custom btn-parent">จัดการผู้ปกครอง</a>
        <a href="manage_users.php" class="btn-custom btn-user">จัดการผู้ใช้</a>
        <a href="becon.php" class="btn-custom btn-officer">จัดการข้อมูลบีคอน</a>
         <a href="https://nvqdgs-3000.csb.app" class="btn-custom btn-dorm-student" target="_blank">แสดงนักศึกษาในหอพัก</a> <!-- ปุ่มใหม่ -->
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
