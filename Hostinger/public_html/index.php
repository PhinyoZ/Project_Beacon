<?php
session_start();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าแรก</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        .container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
            width: 70%;
            max-width: 900px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #000;
        }

        p {
            margin-bottom: 20px;
            color: #333;
        }

        .btn {
            display: inline-block;
            text-align: center;
            padding: 10px 30px;
            margin-top: 20px;
            border: none;
            border-radius: 4px;
            background-color: #1a6f5d; /* Viridian Green */
            color: white;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #145a4a; /* Darker shade */
        }

        .logo {
            width: 100px;
            height: auto;
            margin-bottom: 20px;
        }

        .background-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.7;
        }

        .shape-top-left {
            width: 400px;
            height: 400px;
            background-color: #D4AF37; /* Golden */
            top: -150px;
            left: -150px;
        }

        .shape-top-right {
            width: 400px;
            height: 400px;
            background-color: #1a6f5d; /* Viridian Green */
            top: -200px;
            right: -200px;
        }

        .shape-bottom-left {
            width: 400px;
            height: 400px;
            background-color: #1a6f5d; /* Viridian Green */
            bottom: -150px;
            left: -150px;
        }

        .shape-bottom-right {
            width: 400px;
            height: 400px;
            background-color: #D4AF37; /* Golden */
            bottom: -150px;
            right: -150px;
        }
    </style>
</head>
<body>
    <div class="background-shapes">
        <div class="shape shape-top-left"></div>
        <div class="shape shape-top-right"></div>
        <div class="shape shape-bottom-left"></div>
        <div class="shape shape-bottom-right"></div>
    </div>
    <div class="container">
        <img src="Silpakorn.png" alt="Silpakorn University Emblem" class="logo">
        <h1>ยินดีต้อนรับเข้าสู่ระบบสำหรับตรวจสอบสถานะนักศึกษา</h1>
        <p>มหาวิทยาลัยศิลปากร หอพักเพชรรัตน์ 5</p>
        <a href="login.php" class="btn">เข้าสู่ระบบ</a>
    </div>
</body>
</html>
