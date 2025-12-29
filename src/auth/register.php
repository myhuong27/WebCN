<?php
session_start();
require_once '../config/connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');

    // Kiểm tra dữ liệu đầu vào
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $error = 'Vui lòng điền đầy đủ thông tin';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } else {
        try {
            // Kiểm tra username đã tồn tại chưa
            $stmt = $conn->prepare("SELECT id FROM nguoi_dung WHERE ten_dang_nhap = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Tên đăng nhập đã tồn tại';
            } else {
                // Kiểm tra email đã tồn tại chưa
                $stmt = $conn->prepare("SELECT id FROM nguoi_dung WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Email đã tồn tại';
                } else {
                    // Thêm người dùng mới - Sử dụng MD5 để tương thích với login
                    $hashed_password = md5($password);
                    
                    $stmt = $conn->prepare("INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, ho_ten, email, vai_tro) VALUES (?, ?, ?, ?, 0)");
                    if ($stmt->execute([$username, $hashed_password, $full_name, $email])) {
                        $success = 'Đăng ký thành công! Đang chuyển đến trang đăng nhập...';
                        header("refresh:2;url=login_update.php");
                    } else {
                        $error = 'Không thể thêm người dùng. Vui lòng thử lại sau.';
                        error_log("Registration Error - Insert failed for user: $username");
                    }
                }
            }
        } catch(PDOException $e) {
            $error = 'Có lỗi xảy ra, vui lòng thử lại sau';
            error_log("Registration Error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - Pet Care Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-left {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .register-left h1 {
            font-size: 32px;
            margin-bottom: 20px;
        }

        .register-left p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .features-list {
            list-style: none;
        }

        .features-list li {
            padding: 10px 0;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .features-list i {
            color: #ffd700;
        }

        .register-right {
            padding: 50px 40px;
            background: white;
        }

        .register-right h2 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .register-subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .error-message {
            background: #fee;
            border-left: 4px solid #f44336;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #c62828;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .success-message {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #2e7d32;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .back-home {
            text-align: center;
            margin-top: 15px;
        }

        .back-home a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .back-home a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .register-container {
                grid-template-columns: 1fr;
            }

            .register-left {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-left">
            <h1><i class="fas fa-paw"></i> Pet Care Center</h1>
            <p>Tham gia cộng đồng yêu thương và chăm sóc thú cưng của chúng tôi!</p>
            <ul class="features-list">
                <li><i class="fas fa-check-circle"></i> Hồ sơ thú cưng chi tiết</li>
                <li><i class="fas fa-check-circle"></i> Đặt lịch chăm sóc dễ dàng</li>
                <li><i class="fas fa-check-circle"></i> Lịch tiêm phòng & chăm sóc</li>
                <li><i class="fas fa-check-circle"></i> Dịch vụ nuôi hộ uy tín</li>
                <li><i class="fas fa-check-circle"></i> Blog & Video hướng dẫn</li>
                <li><i class="fas fa-check-circle"></i> Nhắc lịch tự động</li>
            </ul>
        </div>

        <div class="register-right">
            <h2>Đăng Ký Tài Khoản</h2>
            <p class="register-subtitle">Tạo tài khoản mới để trải nghiệm dịch vụ</p>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" required 
                               placeholder="Nhập tên đăng nhập"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required 
                               placeholder="Nhập email"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Họ và tên</label>
                    <div class="input-wrapper">
                        <i class="fas fa-id-card"></i>
                        <input type="text" id="full_name" name="full_name" required 
                               placeholder="Nhập họ và tên"
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required 
                               placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Nhập lại mật khẩu">
                    </div>
                </div>
                
                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i> Đăng Ký
                </button>
            </form>
            
            <div class="login-link">
                Đã có tài khoản? <a href="login_update.php">Đăng nhập ngay</a>
            </div>

            <div class="back-home">
                <a href="index.php">
                    <i class="fas fa-home"></i> Về trang chủ
                </a>
            </div>
        </div>
    </div>
</body>
</html>
