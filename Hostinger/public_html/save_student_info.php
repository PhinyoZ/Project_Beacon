<?php
session_start();
include 'db_connect.php'; // เพิ่มการเชื่อมต่อฐานข้อมูล

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$alert_message = '';
$alert_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $display_name = $_POST['display_name']; // ลบ room_number ออก

    // ดึง userId จาก beacon_info_student โดยใช้ displayName
    $stmt = $conn->prepare("SELECT userId FROM beacon_info_student WHERE displayName = ?");
    if ($stmt) {
        $stmt->bind_param("s", $display_name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $userId = $row['userId'];

            // ตรวจสอบว่ามี userId ในตาราง user หรือไม่
            $stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                // ถ้าไม่พบ userId ในตาราง user ให้บันทึกข้อมูลใหม่
                $stmt = $conn->prepare("INSERT INTO user (user_id, name_line, role) VALUES (?, ?, 'student')");
                $stmt->bind_param("ss", $userId, $display_name);
                if (!$stmt->execute()) {
                    $alert_message = "เกิดข้อผิดพลาดในการบันทึกข้อมูลในตาราง user: " . $stmt->error;
                    $alert_type = "error";
                }
            }

            if ($alert_type != "error") {
                // ตรวจสอบว่ามี userId ในตาราง student_info หรือไม่
                $stmt = $conn->prepare("SELECT * FROM student_info WHERE userId = ?");
                $stmt->bind_param("s", $userId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    // ถ้าพบ userId ให้ทำการอัปเดตข้อมูล (ลบ room_number)
                    $stmt = $conn->prepare("UPDATE student_info SET student_id = ?, first_name = ?, last_name = ? WHERE userId = ?");
                    $stmt->bind_param("ssss", $student_id, $first_name, $last_name, $userId);
                    $alert_message = "อัปเดตข้อมูลสำเร็จ";
                    $alert_type = "success";
                } else {
                    // ถ้าไม่พบ userId ให้บันทึกข้อมูลใหม่ (ลบ room_number)
                    $stmt = $conn->prepare("INSERT INTO student_info (userId, student_id, first_name, last_name) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $userId, $student_id, $first_name, $last_name);
                    $alert_message = "บันทึกข้อมูลสำเร็จ";
                    $alert_type = "success";
                }

                if (!$stmt->execute()) {
                    $alert_message = "เกิดข้อผิดพลาดในการบันทึกข้อมูลในตาราง student_info: " . $stmt->error;
                    $alert_type = "error";
                }
            }

            $stmt->close();
        } else {
            $alert_message = "ไม่พบชื่อที่เลือกในฐานข้อมูลนักเรียน";
            $alert_type = "error";
        }
    } else {
        $alert_message = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง: " . $conn->error;
        $alert_type = "error";
    }

    // ส่งข้อความแจ้งเตือนกลับไปที่ form.php
    $_SESSION['alert_message'] = $alert_message;
    $_SESSION['alert_type'] = $alert_type;
    header("Location: form.php"); // เปลี่ยนเส้นทางกลับไปที่หน้า form.php
    exit();
}

$conn->close();
?>
