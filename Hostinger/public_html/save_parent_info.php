<?php
session_start();
include 'db_connect.php'; // เชื่อมต่อฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $parent_id = $_POST['parent_id'];
    $parent_name = $_POST['parent_name'];
    $contact_number = $_POST['contact_number'];
    $student_id = $_POST['student_id'];

    // ตรวจสอบว่าได้รับข้อมูลจากฟอร์มหรือไม่
    if ($parent_id && $parent_name && $contact_number && $student_id) {
        $stmt = $conn->prepare("UPDATE parent_info SET parent_name = ?, contact_number = ?, student_id = ? WHERE id = ?");
        $stmt->bind_param("sssi", $parent_name, $contact_number, $student_id, $parent_id);

        if ($stmt->execute()) {
            $_SESSION['alert_message'] = "บันทึกข้อมูลสำเร็จ";
            $_SESSION['alert_type'] = "success";
        } else {
            $_SESSION['alert_message'] = "เกิดข้อผิดพลาด: " . $stmt->error;
            $_SESSION['alert_type'] = "error";
        }
    } else {
        $_SESSION['alert_message'] = "ข้อมูลไม่ครบถ้วน";
        $_SESSION['alert_type'] = "error";
    }

    header("Location: of_manage_parents.php");
    exit();
}
?>
