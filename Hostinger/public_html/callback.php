<?php
session_start();
include 'db_connect.php'; // เพิ่มการเชื่อมต่อฐานข้อมูล

$CLIENT_ID = '2005980091';
$CLIENT_SECRET = '3a965c442042c42ce4e04325ced812be';
$REDIRECT_URL = 'https://mediumaquamarine-porcupine-998187.hostingersite.com/callback.php';
$TOKEN_URL = 'https://api.line.me/oauth2/v2.1/token';
$PROFILE_URL = 'https://api.line.me/v2/profile';

if ($_GET['state'] !== $_SESSION['state']) {
    die('State mismatch');
}

$code = $_GET['code'];

$data = [
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $REDIRECT_URL,
    'client_id' => $CLIENT_ID,
    'client_secret' => $CLIENT_SECRET,
];

$headers = ['Content-Type: application/x-www-form-urlencoded'];

$ch = curl_init($TOKEN_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($response, true);
$access_token = $token_data['access_token'];

$headers = [
    'Authorization: Bearer ' . $access_token,
];

$ch = curl_init($PROFILE_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$profile_response = curl_exec($ch);
curl_close($ch);

$profile_data = json_decode($profile_response, true);

$user_id = $profile_data['userId'];
$display_name = $profile_data['displayName'];

$_SESSION['user'] = $profile_data;

// ตรวจสอบว่ามี displayName อยู่ในตาราง beacon_info_student หรือไม่
$stmt = $conn->prepare("SELECT displayName FROM beacon_info_student WHERE displayName = ?");
$stmt->bind_param("s", $display_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // ถ้าพบ displayName ในตาราง beacon_info_student ให้ตั้ง role เป็น student และพาไปหน้า welcome2.php
    $role = 'student';
    $redirect_page = 'welcome2.php';
} else {
    // ตรวจสอบว่ามี user_id ในตาราง users หรือไม่ และตรวจสอบสถานะ
    $stmt = $conn->prepare("SELECT role, status FROM user WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // ตรวจสอบสถานะของผู้ใช้
        if ($row['status'] === 'suspended') {
            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'บัญชีถูกระงับ',
                    text: 'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ',
                    confirmButtonText: 'ตกลง',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'login.php'; // พากลับไปยังหน้าล็อกอิน
                    }
                });
            });
            </script>";
            exit(); // หยุดการทำงานของสคริปต์
        }

        // ตรวจสอบ role และกำหนด redirect page
        if ($row['role'] === 'admin') {
            $role = 'admin';
            $redirect_page = 'welcome3.php';
        } elseif ($row['role'] === 'officer') {
            $role = 'officer';
            $redirect_page = 'welcome4.php';
        } else {
            $role = 'parent';
            $redirect_page = 'welcome1.php';
        }
    } else {
        // ถ้าไม่มี user_id ให้ตั้ง role เป็น parent และพาไปหน้า welcome1.php
        $role = 'parent';
        $redirect_page = 'welcome1.php';
    }
}

// สร้างตาราง user ถ้ายังไม่มี
$sql_create_table = "CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL UNIQUE,
    name_line VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    role ENUM('admin', 'officer', 'parent', 'student') DEFAULT 'parent',
    status ENUM('active', 'suspended') DEFAULT 'active'
)";

if ($conn->query($sql_create_table) === TRUE) {
    // ตรวจสอบว่ามี user_id ในตาราง user หรือไม่
    $stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // ถ้ามี user_id อยู่แล้ว อัปเดตข้อมูล
        $stmt = $conn->prepare("UPDATE user SET name_line = ?, role = ?, created_at = CURRENT_TIMESTAMP WHERE user_id = ?");
        $stmt->bind_param("sss", $display_name, $role, $user_id);
    } else {
        // ถ้าไม่มี user_id ให้บันทึกข้อมูลใหม่
        $stmt = $conn->prepare("INSERT INTO user (user_id, name_line, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $user_id, $display_name, $role);
    }

    if ($stmt->execute()) {
        // ตั้งค่า role ใน $_SESSION
        $_SESSION['user']['role'] = $role;
        // พาไปหน้าที่กำหนดตาม role
        header("Location: " . $redirect_page);
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
exit();
?>
