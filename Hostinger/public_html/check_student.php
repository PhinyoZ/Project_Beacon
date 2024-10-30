<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ตั้งค่าเขตเวลาเป็นประเทศไทย
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'parent') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$user = $_SESSION['user'];
$userId = $user['userId'];
$display_name = $user['displayName'];

// ตรวจสอบว่าเป็นผู้ปกครองของนักศึกษาคนไหน
$stmt = $conn->prepare("
    SELECT student_info.student_id, student_info.first_name, student_info.last_name, student_info.room_number, beacon_info_student.pictureUrl 
    FROM parent_info 
    JOIN student_info ON parent_info.student_id = student_info.student_id 
    JOIN beacon_info_student ON beacon_info_student.userId = student_info.userId 
    WHERE parent_info.parent_id = ?
");
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();
$students_info = $result->fetch_all(MYSQLI_ASSOC);

$students_data = [];

foreach ($students_info as $student_info) {
    $student_id = $student_info['student_id'];

    // ดึงข้อมูลจากตาราง beacon_events โดยใช้ student_id ผ่านการ JOIN กับ student_info
    $stmt = $conn->prepare("
        SELECT beacon_events.dm, beacon_events.datetimeregis 
        FROM beacon_events 
        JOIN student_info ON beacon_events.user_id = student_info.userId 
        WHERE student_info.student_id = ? 
        ORDER BY beacon_events.datetimeregis DESC 
        LIMIT 1
    ");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $beacon_event = $result->fetch_assoc();

    $status = 'ไม่พบข้อมูล';
    $color = 'gray';
    $formatted_date = null;

    if ($beacon_event) {
        if ($beacon_event['dm'] === 'a0a3b32fadf2') {
            $status = 'ไม่อยู่หอ';
            $color = 'red';
        } elseif ($beacon_event['dm'] === 'a0a3b33145fe') {
            $status = 'อยู่หอ';
            $color = 'green';
        }

       // เพิ่มเวลา 7 ชั่วโมงให้กับเวลาที่บันทึก
$datetime = new DateTime($beacon_event['datetimeregis']);
$datetime->modify('+7 hours');
$formatted_date = $datetime->format('Y-m-d H:i:s');

    }

    $students_data[] = [
        'student_info' => $student_info,
        'status' => $status,
        'color' => $color,
        'datetimeregis' => $formatted_date
    ];
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสอบนักศึกษา</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #d4af37;
        }

        .navbar-brand {
            color: white !important;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            color: #fff3e0 !important;
            transform: translateY(-2px);
        }

        .navbar-nav .nav-link {
            color: white !important;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #fff3e0 !important;
            transform: translateY(-2px);
        }

        .status-box {
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 10px;
            margin-top: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #ccc;
        }

        .container-fluid {
            max-width: 100%;
            margin: auto;
            padding: 10px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .student-info {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #ccc;
            transition: all 0.3s ease;
        }

        /* เอฟเฟกต์ hover */
        .student-info:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            background-color: #f7f4ef;
        }

        .student-info img {
            border-radius: 50%;
            width: 100px;
            max-width: 150px;
            margin-bottom: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .student-info img:hover {
            transform: scale(1.05);
        }

        .student-info p {
            font-size: 14px;
            margin-top: 5px;
            transition: color 0.3s ease;
        }

        /* Media Queries สำหรับหน้าจอมือถือ */
        @media (max-width: 768px) {
            .student-info {
                padding: 10px;
                margin-bottom: 15px;
            }

            .status-box {
                margin-top: 10px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-light">
        <a class="navbar-brand" href="#">กรอกข้อมูลนักศึกษา</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="welcome1.php">หน้าหลัก</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="parent_form.php">ข้อมูลเพิ่มเติม</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">ออกจากระบบ</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid">
        <h1>สถานะนักศึกษา</h1>

        <?php if (empty($students_data)): ?>
            <div class="alert alert-warning text-center">
                ไม่พบข้อมูลนักศึกษา กรุณาลงทะเบียนก่อน
            </div>
        <?php else: ?>
            <?php foreach ($students_data as $data): ?>
                <div class="student-info">
                    <img src="<?php echo htmlspecialchars($data['student_info']['pictureUrl']); ?>" alt="Profile Picture">
                    <p><strong>รหัสนักศึกษา:</strong> <?php echo htmlspecialchars($data['student_info']['student_id']); ?></p>
                    <p><strong>ชื่อ:</strong> <?php echo htmlspecialchars($data['student_info']['first_name']); ?></p>
                    <p><strong>นามสกุล:</strong> <?php echo htmlspecialchars($data['student_info']['last_name']); ?></p>
                    <p><strong>หมายเลขห้อง:</strong> <?php echo htmlspecialchars($data['student_info']['room_number']); ?></p>
                    
                    <div class="status-box" style="background-color: <?php echo $data['color']; ?>;">
                        <p>สถานะ: <?php echo $data['status']; ?></p>
                        <?php if ($data['datetimeregis']): ?>
                            <p>เวลาที่บันทึก: <?php echo htmlspecialchars($data['datetimeregis']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- JavaScript Libraries สำหรับการทำงานของ Bootstrap Navbar -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>



<?php
$conn->close();
?>
