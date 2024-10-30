<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'parent') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php'; 

if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

$user = $_SESSION['user'];
$display_name = $user['displayName'];
$user_id = $user['userId'];

// ดึงรายชื่อนักเรียนจากตาราง student_info
$query = "SELECT student_id, first_name, last_name FROM student_info";
$result = $conn->query($query);

if ($result === false) {
    die("ข้อผิดพลาดในการดึงข้อมูลนักศึกษา: " . $conn->error);
}

if ($result->num_rows > 0) {
    $students = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $students = [];
}

// ตรวจสอบว่ามีข้อมูลของผู้ปกครองอยู่ในฐานข้อมูลหรือไม่
$parent_info_query = "SELECT * FROM parent_info WHERE parent_id = ?";
$stmt = $conn->prepare($parent_info_query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$parent_info_result = $stmt->get_result();

if ($parent_info_result === false) {
    die("ข้อผิดพลาดในการดึงข้อมูลผู้ปกครอง: " . $stmt->error);
}

if ($parent_info_result->num_rows > 0) {
    $parent_info = $parent_info_result->fetch_all(MYSQLI_ASSOC); 
    $student_ids = array_column($parent_info, 'student_id');
    $parent_name = $parent_info[0]['parent_name'];
    $contact_number = $parent_info[0]['contact_number'];
    $is_update = true;
} else {
    $is_update = false;
    $student_ids = [];
    $parent_name = '';
    $contact_number = '';
}

// ข้อความแจ้งเตือนหรือข้อผิดพลาด
$alert_message = isset($_SESSION['alert_message']) ? $_SESSION['alert_message'] : '';
$alert_type = isset($_SESSION['alert_type']) ? $_SESSION['alert_type'] : '';

unset($_SESSION['alert_message']);
unset($_SESSION['alert_type']);

// ตรวจสอบข้อผิดพลาดของฟอร์ม
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['parent_name'])) {
        $errors[] = "กรุณากรอกชื่อ-นามสกุลของผู้ปกครอง";
    }
    if (empty($_POST['contact_number'])) {
        $errors[] = "กรุณากรอกเบอร์ติดต่อ";
    }
    if (empty($_POST['student_id']) || !is_array($_POST['student_id'])) {
        $errors[] = "กรุณาเลือกนักศึกษาย่างน้อยหนึ่งคน";
    }

    // ตรวจสอบข้อมูลซ้ำในฐานข้อมูล
    foreach ($_POST['student_id'] as $student_id) {
        $check_query = "SELECT * FROM parent_info WHERE parent_id = ? AND student_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ss", $user_id, $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // กรณีที่นักศึกษาถูกเลือกแล้ว
            $_SESSION['alert_message'] = "นักศึกษานี้ถูกเพิ่มไปแล้ว";
            $_SESSION['alert_type'] = "error";
            header("Location: parent_form.php");
            exit();
        } else {
            // ทำการบันทึกนักศึกษาใหม่
            // เพิ่มโค้ดการบันทึกที่นี่
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กรอกข้อมูลเพิ่มเติม (ผู้ปกครอง)</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
            font-size: 1.2rem;
        }

        .navbar-nav .nav-link {
            color: white !important;
        }

        .navbar-nav .nav-link:hover {
            background-image: linear-gradient(135deg, #d4af37 0%, #f39c12 100%);
            color: white !important;
            padding: 5px 10px;
            border-radius: 8px;
            transition: background-color 0.4s ease, padding 0.4s ease;
        }

        .container {
            max-width: 500px;
            margin: 70px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.4rem;
            color: #333;
        }

        .form-group label {
            font-size: 0.9rem;
        }

        .btn-custom {
            background-color: #d4af37;
            color: white;
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            background-image: linear-gradient(135deg, #d4af37 0%, #f39c12 100%);
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-remove-student {
            background-color: #ff4d4d;
            color: white;
            border: none;
            font-size: 0.9rem;
            border-radius: 5px;
            padding: 6px 12px;
            transition: all 0.3s ease;
        }

        .btn-remove-student:hover {
            background-color: #e60000;
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        }

        .input-group .student-select {
            flex: 1;
        }

        .input-group-append {
            margin-left: 10px;
        }

        @media (max-width: 768px) {
            .container {
                margin: 50px auto;
                padding: 20px;
            }

            h1 {
                font-size: 1.2rem;
                margin-bottom: 15px;
            }

            .btn-custom {
                font-size: 0.9rem;
                padding: 10px;
            }

            .btn-remove-student {
                font-size: 0.85rem;
                padding: 5px 10px;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light">
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

    <div class="container">
        <h1><?php echo $is_update ? 'ข้อมูลผู้ปกครอง' : 'กรุณากรอกข้อมูลเพิ่มเติม'; ?></h1>

        <form method="POST" action="process_parent_form.php">
            <div class="form-group">
                <label for="parent_name">ชื่อ-นามสกุลของผู้ปกครอง(กรุณากรอกเป็นภาษาไทย)</label>
                <input type="text" class="form-control" id="parent_name" name="parent_name" value="<?php echo htmlspecialchars($parent_name); ?>" <?php echo $is_update ? 'readonly' : ''; ?> required>
            </div>
            <div class="form-group">
                <label for="contact_number">เบอร์ติดต่อ</label>
                <input type="text" class="form-control" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($contact_number); ?>" <?php echo $is_update ? 'readonly' : ''; ?> required>
            </div>
            <div class="form-group">
                <label>นักศึกษาที่เลือก:</label>
                <?php if ($is_update): ?>
                    <ul>
                        <?php 
                        foreach ($student_ids as $id) {
                            foreach ($students as $student) {
                                if (trim($student['student_id']) == trim($id)) {
                                    echo '<li>' . htmlspecialchars($student['first_name']) . ' ' . htmlspecialchars($student['last_name']) . '</li>';
                                }
                            }
                        }
                        ?>
                    </ul>
                <?php else: ?>
                    <div id="student-selection">
                        <div class="form-group">
                            <label for="search-student">ค้นหานักศึกษา</label>
                            <input type="text" id="search-student" class="form-control" placeholder="พิมพ์ชื่อหรือนามสกุลนักศึกษา">
                        </div>
                        <div class="form-group">
                            <label for="student_id[]">เลือกชื่อนักศึกษา</label>
                            <div class="input-group">
                                <select class="form-control student-select" id="student_id[]" name="student_id[]" required>
                                    <option value="">กรุณาเลือกนักศึกษา</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?php echo $student['student_id']; ?>">
                                            <?php echo htmlspecialchars($student['first_name']) . ' ' . htmlspecialchars($student['last_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-remove-student"><i class="fas fa-trash-alt"></i> ลบ</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mb-3" id="add-student">เพิ่มนักศึกษา</button>
                <?php endif; ?>
            </div>
            
            <?php if (!$is_update): ?>
                <button type="submit" class="btn btn-custom">ยืนยัน</button>
            <?php endif; ?>
        </form>
        
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            let selectedStudents = [];

            // เพิ่มนักศึกษาใหม่
            $('#add-student').click(function() {
                let newStudentSelect = `
                <div class="form-group student-entry">
                    <div class="input-group">
                        <select class="form-control student-select" name="student_id[]" required>
                            <option value="">กรุณาเลือกนักศึกษา</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['student_id']; ?>"><?php echo htmlspecialchars($student['first_name']) . ' ' . htmlspecialchars($student['last_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-remove-student"><i class="fas fa-trash-alt"></i> ลบ</button>
                        </div>
                    </div>
                </div>`;
                $('#student-selection').append(newStudentSelect);
            });

            // ลบนักศึกษา
            $(document).on('click', '.btn-remove-student', function() {
                let studentId = $(this).closest('.student-entry').find('.student-select').val();
                selectedStudents = selectedStudents.filter(id => id !== studentId);
                $(this).closest('.student-entry').remove();
            });

            // ตรวจสอบข้อมูลซ้ำ
            $(document).on('change', '.student-select', function() {
                let studentId = $(this).val();
                if (selectedStudents.includes(studentId)) {
                    alert('นักศึกษานี้ถูกเลือกแล้ว');
                    $(this).val('');
                } else {
                    selectedStudents.push(studentId);
                }
            });

            $('#search-student').on('keyup', function() {
                let searchTerm = $(this).val().toLowerCase();
                $('.student-select option').each(function() {
                    let studentName = $(this).text().toLowerCase();
                    if (studentName.indexOf(searchTerm) > -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        });

        <?php if (isset($_GET['success_message'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ',
                text: '<?php echo htmlspecialchars($_GET['success_message']); ?>',
                confirmButtonText: 'ตกลง'
            });
        <?php endif; ?>

        <?php if (isset($_GET['error_message'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'ข้อผิดพลาด',
                text: '<?php echo htmlspecialchars($_GET['error_message']); ?>',
                confirmButtonText: 'ตกลง'
            });
        <?php endif; ?>
    </script>
    
</body>
</html>

