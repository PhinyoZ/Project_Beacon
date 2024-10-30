<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'parent') {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

include 'db_connect.php';
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$parent_id = $_SESSION['user']['userId'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_ids = $_POST['student_id']; // รับค่าหลาย student_id ในรูปแบบ array
    $parent_name = $_POST['parent_name'];
    $contact_number = $_POST['contact_number'];

    if (empty($student_ids) || empty($parent_name) || empty($contact_number)) {
        $error_message = "กรุณากรอกข้อมูลให้ครบถ้วน";
        header("Location: parent_form.php?error_message=" . urlencode($error_message));
        exit();
    }

    // ลบข้อมูลเก่าของผู้ปกครองจาก parent_info เพื่อเตรียมใส่ข้อมูลใหม่
    $delete_sql = "DELETE FROM parent_info WHERE parent_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("s", $parent_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    // เพิ่มข้อมูลนักเรียนใหม่สำหรับผู้ปกครองนี้
    $insert_sql = "INSERT INTO parent_info (parent_id, student_id, parent_name, contact_number) VALUES (?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);

    foreach ($student_ids as $student_id) {
        $insert_stmt->bind_param("siss", $parent_id, $student_id, $parent_name, $contact_number);
        if (!$insert_stmt->execute()) {
            $error_message = "เกิดข้อผิดพลาด: " . $insert_stmt->error;
            header("Location: parent_form.php?error_message=" . urlencode($error_message));
            exit();
        }
    }

    $insert_stmt->close();
    $conn->close();

    $success_message = "ข้อมูลถูกบันทึกเรียบร้อยแล้ว!";
    header("Location: parent_form.php?success_message=" . urlencode($success_message));
    exit();
} else {
    header("Location: parent_form.php");
    exit();
}
?>
