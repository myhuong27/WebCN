<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login_update.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Lấy thông tin người dùng
try {
    $stmt = $conn->prepare("SELECT * FROM nguoi_dung WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch(PDOException $e) {
    $error = 'Không thể lấy thông tin người dùng';
    error_log($e->getMessage());
}

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ho_ten = trim($_POST['ho_ten'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($ho_ten) || empty($email)) {
        $error = 'Vui lòng điền đầy đủ họ tên và email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } else {
        try {
            // Kiểm tra email đã tồn tại chưa (trừ email hiện tại của user)
            $stmt = $conn->prepare("SELECT id FROM nguoi_dung WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                $error = 'Email đã được sử dụng bởi tài khoản khác';
            } else {
                // Nếu người dùng muốn đổi mật khẩu
                if (!empty($current_password)) {
                    if (empty($new_password) || empty($confirm_password)) {
                        $error = 'Vui lòng nhập đầy đủ mật khẩu mới và xác nhận mật khẩu';
                    } elseif ($new_password !== $confirm_password) {
                        $error = 'Mật khẩu mới và xác nhận mật khẩu không khớp';
                    } elseif (strlen($new_password) < 6) {
                        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự';
                    } elseif (!password_verify($current_password, $user['mat_khau'])) {
                        $error = 'Mật khẩu hiện tại không đúng';
                    } else {
                        // Cập nhật thông tin và mật khẩu
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE nguoi_dung SET ho_ten = ?, email = ?, mat_khau = ? WHERE id = ?");
                        $stmt->execute([$ho_ten, $email, $hashed_password, $user_id]);
                        $success = 'Cập nhật thông tin và mật khẩu thành công';
                    }
                } else {
                    // Chỉ cập nhật thông tin cơ bản
                    $stmt = $conn->prepare("UPDATE nguoi_dung SET ho_ten = ?, email = ? WHERE id = ?");
                    $stmt->execute([$ho_ten, $email, $user_id]);
                    $success = 'Cập nhật thông tin thành công';
                }

                // Cập nhật lại thông tin người dùng sau khi thay đổi
                $stmt = $conn->prepare("SELECT * FROM nguoi_dung WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
            }
        } catch(PDOException $e) {
            $error = 'Có lỗi xảy ra, vui lòng thử lại sau';
            error_log($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin tài khoản - Fashion Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 80px auto 20px;
            padding: 0 20px;
        }

        .profile-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-header h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .password-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .password-section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.2em;
        }

        .btn-update {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }

        .btn-update:hover {
            background: #0056b3;
        }

        .error-message {
            background: #ffe6e6;
            color: #dc3545;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .success-message {
            background: #e6ffe6;
            color: #28a745;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #666;
            text-decoration: none;
        }

        .back-link:hover {
            color: #333;
        }

        .back-link i {
            margin-right: 5px;
        }

        @media (max-width: 768px) {
            .container {
                margin-top: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Quay lại trang chủ
        </a>

        <div class="profile-card">
            <div class="profile-header">
                <h1>Thông tin tài khoản</h1>
                <p>Cập nhật thông tin cá nhân của bạn</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="ten_dang_nhap">Tên đăng nhập</label>
                    <input type="text" id="ten_dang_nhap" value="<?php echo htmlspecialchars($user['ten_dang_nhap']); ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="ho_ten">Họ và tên</label>
                    <input type="text" id="ho_ten" name="ho_ten" value="<?php echo htmlspecialchars($user['ho_ten']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="password-section">
                    <h2>Đổi mật khẩu</h2>
                    <p>Để trống nếu không muốn thay đổi mật khẩu</p>

                    <div class="form-group">
                        <label for="current_password">Mật khẩu hiện tại</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>

                    <div class="form-group">
                        <label for="new_password">Mật khẩu mới</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu mới</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                </div>

                <button type="submit" class="btn-update">Cập nhật thông tin</button>
            </form>
        </div>
    </div>
<?php require_once '../includes/chat_widget.php'; ?>
</body>
</html>
