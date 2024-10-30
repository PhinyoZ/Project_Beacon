<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php'; // เพิ่มการเชื่อมต่อฐานข้อมูล

$user = $_SESSION['user'];
$display_name = $user['displayName'];
$picture_url = $user['pictureUrl'];

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
    <title>ยินดีต้อนรับ (นักศึกษา)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .navbar-custom {
            background-color: #1a6f5d;
            padding: 10px 20px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 10;
        }

        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link {
            color: white;
        }

        /* ปรับแต่ง navbar hover ให้เป็นสีเขียว */
        .navbar-custom .nav-link:hover {
            background-image: linear-gradient(135deg, #1a6f5d 0%, #28a745 100%);
            color: white;
            border-radius: 8px;
            padding: 5px 10px;
            transition: background-color 0.4s ease, padding 0.4s ease;
        }

        .container {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative;
        }

        .profile img {
            border-radius: 50%;
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 5px solid #1a6f5d;
            margin-bottom: 20px;
        }

        h1 {
            margin: 10px 0;
            font-size: 1.5em;
            color: #333;
        }

        .btn-custom {
            display: block;
            text-align: center;
            padding: 12px 20px;
            margin: 15px auto;
            border: none;
            border-radius: 25px;
            background-color: #1a6f5d; /* ปรับเป็นสีเขียว */
            color: white;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            width: 85%;
        }

        /* ปรับแต่งปุ่ม hover ให้เป็นสีเขียว */
        .btn-custom:hover {
            background-image: linear-gradient(135deg, #1a6f5d 0%, #28a745 100%);
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.4s ease;
        }

        .background-decoration {
            position: absolute;
            border-radius: 50%;
            background-color: #1a6f5d;
            z-index: -1;
        }

        .background-decoration.left {
            width: 150px;
            height: 150px;
            top: -50px;
            left: -50px;
        }

        .background-decoration.right {
            width: 100px;
            height: 100px;
            bottom: -30px;
            right: -30px;
        }

        .message {
            font-size: 1.1em;
            color: #333;
            margin-bottom: 10px;
        }

        @media (max-width: 600px) {
            .container {
                width: 90%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">ยินดีต้อนรับ, <?php echo htmlspecialchars($display_name); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="welcome2.php">หน้าหลัก</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="form.php">กรอกข้อมูลเพิ่มเติม</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">ออกจากระบบ</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="profile">
            <img src="<?php echo htmlspecialchars($picture_url); ?>" alt="Profile Picture">
            <h1><?php echo htmlspecialchars($display_name); ?></h1>
        </div>

        <!-- ข้อความที่ต้องการ -->
        <p class="message">กรุณากรอกข้อมูลสำหรับนักศึกษา</p>
        <a href="form.php" class="btn btn-custom">กรอกข้อมูลเพิ่มเติม</a>

        <div class="background-decoration left"></div>
        <div class="background-decoration right"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
