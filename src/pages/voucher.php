<?php
session_start();
require_once '../config/connect.php';

// Lấy danh sách voucher
$vouchers = [
    [
        'id' => 1,
        'code' => 'PETCARE10',
        'title' => 'Giảm 10% cho khách hàng mới',
        'description' => 'Áp dụng cho đơn hàng đầu tiên, giảm 10% tối đa 100.000đ',
        'discount' => '10%',
        'min_order' => 200000,
        'max_discount' => 100000,
        'expiry' => '31/12/2025',
        'type' => 'percent',
        'icon' => 'fa-star'
    ],
    [
        'id' => 2,
        'code' => 'SPA50K',
        'title' => 'Giảm 50.000đ dịch vụ Spa',
        'description' => 'Áp dụng cho tất cả dịch vụ tắm và spa thú cưng',
        'discount' => '50.000đ',
        'min_order' => 300000,
        'max_discount' => 50000,
        'expiry' => '31/01/2026',
        'type' => 'fixed',
        'icon' => 'fa-spa'
    ],
    [
        'id' => 3,
        'code' => 'FREESHIP',
        'title' => 'Miễn phí vận chuyển',
        'description' => 'Miễn phí giao thú cưng tận nơi cho đơn từ 500.000đ',
        'discount' => 'FREE',
        'min_order' => 500000,
        'max_discount' => 0,
        'expiry' => '28/02/2026',
        'type' => 'shipping',
        'icon' => 'fa-truck'
    ],
    [
        'id' => 4,
        'code' => 'VACCINE20',
        'title' => 'Giảm 20% dịch vụ tiêm phòng',
        'description' => 'Áp dụng cho các gói tiêm phòng định kỳ',
        'discount' => '20%',
        'min_order' => 100000,
        'max_discount' => 150000,
        'expiry' => '31/03/2026',
        'type' => 'percent',
        'icon' => 'fa-syringe'
    ],
    [
        'id' => 5,
        'code' => 'COMBO30',
        'title' => 'Combo chăm sóc giảm 30%',
        'description' => 'Đặt 3 dịch vụ trở lên trong tháng được giảm 30%',
        'discount' => '30%',
        'min_order' => 500000,
        'max_discount' => 300000,
        'expiry' => '30/04/2026',
        'type' => 'percent',
        'icon' => 'fa-gift'
    ],
    [
        'id' => 6,
        'code' => 'REFER100K',
        'title' => 'Giới thiệu bạn nhận 100K',
        'description' => 'Giới thiệu bạn bè đăng ký, cả hai nhận voucher 100.000đ',
        'discount' => '100.000đ',
        'min_order' => 0,
        'max_discount' => 100000,
        'expiry' => '31/12/2026',
        'type' => 'referral',
        'icon' => 'fa-users'
    ]
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ưu đãi đặc biệt - Pet Care Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, #fef5f9 0%, #f5f0fb 50%, #f0ebf8 100%);
            min-height: 100vh;
            padding: 0;
            color: #2c3e50;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(236, 179, 213, 0.08) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(187, 157, 224, 0.08) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
            position: relative;
            z-index: 1;
        }

        .header {
            text-align: center;
            margin-bottom: 60px;
            padding: 60px 20px 40px;
        }

        .back-btn {
            position: absolute;
            top: 30px;
            left: 30px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #a855f7;
            text-decoration: none;
            font-size: 15px;
            padding: 10px 20px;
            background: rgba(255,255,255,0.6);
            border: 1px solid rgba(168, 85, 247, 0.2);
            border-radius: 8px;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.85);
            border-color: rgba(168, 85, 247, 0.4);
            color: #a855f7;
        }

        .header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 56px;
            margin-bottom: 15px;
            font-weight: 700;
            background: linear-gradient(135deg, #a855f7 0%, #ec4899 50%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -1px;
        }

        .header p {
            font-size: 18px;
            color: #6c757d;
            font-weight: 300;
        }

        .vouchers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .voucher-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.85) 0%, rgba(254, 245, 249, 0.95) 100%);
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid rgba(236, 179, 213, 0.25);
            transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
            cursor: pointer;
            position: relative;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(168, 85, 247, 0.08);
        }

        .voucher-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(236, 179, 213, 0.1) 0%, rgba(187, 157, 224, 0.08) 50%, transparent 100%);
            opacity: 0;
            transition: opacity 0.5s;
            pointer-events: none;
        }

        .voucher-card:hover {
            transform: translateY(-8px);
            border-color: rgba(236, 179, 213, 0.45);
            box-shadow: 0 20px 40px rgba(168, 85, 247, 0.15);
        }

        .voucher-card:hover::before {
            opacity: 1;
        }

        .voucher-header {
            padding: 35px 30px 30px;
            position: relative;
            background: linear-gradient(135deg, rgba(250, 232, 255, 0.6) 0%, rgba(252, 231, 243, 0.7) 100%);
            border-bottom: 1px solid rgba(236, 179, 213, 0.2);
        }

        .voucher-icon {
            font-size: 40px;
            margin-bottom: 15px;
            display: block;
            opacity: 0.75;
            background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .voucher-code {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #2c3e50;
            letter-spacing: 1px;
        }

        .copy-btn {
            background: rgba(255,255,255,0.6);
            border: 1px solid rgba(168, 85, 247, 0.2);
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            color: #a855f7;
            transition: all 0.3s;
            font-size: 13px;
        }

        .copy-btn:hover {
            background: rgba(255,255,255,0.9);
            border-color: rgba(168, 85, 247, 0.4);
            color: #9333ea;
        }

        .discount-value {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 5px;
            background: linear-gradient(135deg, #a855f7 0%, #ec4899 50%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .voucher-body {
            padding: 30px;
        }

        .voucher-title {
            font-size: 20px;
            font-wei2c3e50;
        }

        .voucher-description {
            color: #6c757d;
            margin-bottom: 25px;
            line-height: 1.6;
            font-size: 14px;
        }

        .voucher-details {
            border-top: 1px dashed rgba(140,150,170,0.25);
            padding-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #6c757d;
            font-size: 13px;
        }

        .detail-item i {
            width: 18px;
            color: #a855f7;
            opacity: 0.7;
        }

        .use-now-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.08) 0%, rgba(236, 72, 153, 0.08) 100%);
            color: #a855f7;
            border: 1px solid rgba(168, 85, 247, 0.25);
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .use-now-btn:hover {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.15) 0%, rgba(236, 72, 153, 0.12) 100%);
            border-color: rgba(168, 85, 247, 0.4);
            transform: scale(1.01);
            color: #9333ea;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .voucher-card {
            animation: fadeInUp 0.8s ease forwards;
            opacity: 0;
        }

        .voucher-card:nth-child(1) { animation-delay: 0.1s; }
        .voucher-card:nth-child(2) { animation-delay: 0.2s; }
        .voucher-card:nth-child(3) { animation-delay: 0.3s; }
        .voucher-card:nth-child(4) { animation-delay: 0.4s; }
        .voucher-card:nth-child(5) { animation-delay: 0.5s; }
        .voucher-card:nth-child(6) { animation-delay: 0.6s; }

        .premium-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.15), rgba(236, 72, 153, 0.15));
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            color: #a855f7;
            border: 1px solid rgba(168, 85, 247, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 40px;
            }
            
            .vouchers-grid {
                grid-template-columns: 1fr;
            }

            .back-btn {
                top: 20px;
                left: 20px;
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
        
        <div class="header">
            <h1>Ưu Đãi Đặc Biệt</h1>
            <p>Săn ngay các voucher hấp dẫn dành riêng cho thú cưng của bạn</p>
        </div>

        <div class="vouchers-grid">
            <?php foreach ($vouchers as $voucher): ?>
                <div class="voucher-card">
                    <?php if ($voucher['type'] == 'referral'): ?>
                        <span class="premium-badge">Premium</span>
                    <?php endif; ?>
                    <div class="voucher-header">
                        <i class="fas <?php echo $voucher['icon']; ?> voucher-icon"></i>
                        <div class="voucher-code">
                            <span><?php echo $voucher['code']; ?></span>
                            <button class="copy-btn" onclick="copyCode('<?php echo $voucher['code']; ?>')">
                                <i class="fas fa-copy"></i> Sao chép
                            </button>
                        </div>
                        <div class="discount-value"><?php echo $voucher['discount']; ?></div>
                    </div>
                    <div class="voucher-body">
                        <h3 class="voucher-title"><?php echo $voucher['title']; ?></h3>
                        <p class="voucher-description"><?php echo $voucher['description']; ?></p>
                        <div class="voucher-details">
                            <?php if ($voucher['min_order'] > 0): ?>
                            <div class="detail-item">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Đơn tối thiểu: <?php echo number_format($voucher['min_order'], 0, ',', '.'); ?>₫</span>
                            </div>
                            <?php endif; ?>
                            <?php if ($voucher['type'] != 'shipping' && $voucher['max_discount'] > 0): ?>
                            <div class="detail-item">
                                <i class="fas fa-tag"></i>
                                <span>Giảm tối đa: <?php echo number_format($voucher['max_discount'], 0, ',', '.'); ?>₫</span>
                            </div>
                            <?php endif; ?>
                            <div class="detail-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Hạn sử dụng: <?php echo $voucher['expiry']; ?></span>
                            </div>
                        </div>
                        <button class="use-now-btn" onclick="useVoucher('<?php echo $voucher['code']; ?>')">
                            <i class="fas fa-arrow-right"></i>
                            Sử dụng ngay
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function copyCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                const notification = document.createElement('div');
                notification.style.cssText = `
                    position: fixed;
                    top: 30px;
                    right: 30px;
                    background: rgba(255, 255, 255, 0.95);
                    color: #2c3e50;
                    padding: 16px 24px;
                    border-radius: 12px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
                    z-index: 10000;
                    border: 1px solid rgba(140,150,170,0.25);
                    backdrop-filter: blur(10px);
                    animation: slideIn 0.3s ease;
                `;
                notification.innerHTML = `
                    <i class="fas fa-check-circle" style="color: #4ade80; margin-right: 8px;"></i>
                    Đã sao chép mã: <strong>${code}</strong>
                `;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.style.animation = 'slideOut 0.3s ease';
                    setTimeout(() => notification.remove(), 300);
                }, 2500);
            });
        }

        function useVoucher(code) {
            localStorage.setItem('selectedVoucher', code);
            window.location.href = 'dat_lich_thanh_toan.php';
        }
    </script>
</body>
</html>
