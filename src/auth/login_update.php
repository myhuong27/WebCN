<?php
/**
 * TRANG ĐĂNG NHẬP CẬP NHẬT - Hỗ trợ cả username và email
 */
session_start();
require_once '../config/connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_input = trim($_POST['login_input']); // Có thể là username hoặc email
    $password = $_POST['password'];
    
    try {
        // Tìm user theo username HOẶC email
        $stmt = $conn->prepare("SELECT * FROM nguoi_dung 
                               WHERE (ten_dang_nhap = ? OR email = ?)");
        $stmt->execute([$login_input, $login_input]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && md5($password) === $user['mat_khau']) {
            // Đăng nhập thành công
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['ten_dang_nhap'] = $user['ten_dang_nhap'];
            $_SESSION['ho_ten'] = $user['ho_ten'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['vai_tro'] = $user['vai_tro'];
            $_SESSION['avatar'] = isset($user['avatar']) ? $user['avatar'] : null;
            
            // Kiểm tra redirect URL
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
            
            // Chuyển hướng theo vai trò
            if ($user['vai_tro'] == 2) {
                header('Location: ../admin/dashboard.php');
            } else {
                // User thường luôn về trang chủ
                header('Location: ../index.php');
            }
            exit;
        } else {
            $error = 'Tên đăng nhập/Email hoặc mật khẩu không đúng!';
        }
    } catch(PDOException $e) {
        $error = 'Lỗi hệ thống: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Pet Care Center</title>
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

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .login-left {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-left h1 {
            font-size: 36px;
            margin-bottom: 20px;
        }

        .login-left p {
            font-size: 16px;
            line-height: 1.6;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .login-left .features {
            list-style: none;
        }

        .login-left .features li {
            padding: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .login-left .features i {
            font-size: 20px;
        }

        .login-right {
            padding: 60px 40px;
        }

        .login-right h2 {
            color: #333;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #666;
            margin-bottom: 8px;
            font-weight: 600;
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
            padding: 15px 15px 15px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
            }

            .login-left {
                padding: 40px 30px;
            }

            .login-right {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <h1><i class="fas fa-paw"></i> Pet Care Center</h1>
            <p>Hệ thống quản lý nuôi hộ và chăm sóc thú cưng chuyên nghiệp</p>
            <ul class="features">
                <li><i class="fas fa-check-circle"></i> Quản lý thông tin thú cưng</li>
                <li><i class="fas fa-check-circle"></i> Lịch tiêm phòng & chăm sóc</li>
                <li><i class="fas fa-check-circle"></i> Dịch vụ nuôi hộ uy tín</li>
                <li><i class="fas fa-check-circle"></i> Blog & Video hướng dẫn</li>
                <li><i class="fas fa-check-circle"></i> Nhắc lịch tự động</li>
            </ul>
        </div>

        <div class="login-right">
            <h2>Đăng Nhập</h2>

            <?php if (isset($_GET['redirect'])): ?>
                <div class="info-message" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin-bottom: 20px; border-radius: 5px; color: #856404;">
                    <i class="fas fa-info-circle"></i> Vui lòng đăng nhập để tiếp tục
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Tên đăng nhập hoặc Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" name="login_input" required 
                               placeholder="Nhập username hoặc email">
                    </div>
                </div>

                <div class="form-group">
                    <label>Mật khẩu</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" required 
                               placeholder="Nhập mật khẩu">
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập
                </button>
            </form>

            <div class="register-link">
                Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
            </div>
        </div>
    </div>
</body>
</html>
