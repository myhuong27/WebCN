<?php
session_start();
require_once 'config/connect.php';

$keyword = $_GET['q'] ?? '';
$results = [];

if ($keyword) {
    try {
        // Tìm bài viết
        $stmt = $conn->prepare("SELECT id, tieu_de, noi_dung, hinh_anh, ngay_tao, 'blog' as loai 
                               FROM bai_viet 
                               WHERE trang_thai = 1 
                               AND (tieu_de LIKE ? OR noi_dung LIKE ?)
                               ORDER BY ngay_tao DESC");
        $searchTerm = "%$keyword%";
        $stmt->execute([$searchTerm, $searchTerm]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Tìm dịch vụ
        $stmt = $conn->prepare("SELECT id, ten_dich_vu, mo_ta, gia_dich_vu, 'service' as loai 
                               FROM dich_vu 
                               WHERE trang_thai = 1 
                               AND (ten_dich_vu LIKE ? OR mo_ta LIKE ?)");
        $stmt->execute([$searchTerm, $searchTerm]);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $results = array_merge($posts, $services);
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm: <?php echo htmlspecialchars($keyword); ?> - Pet Care Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .search-info {
            font-size: 16px;
            opacity: 0.9;
        }

        .results-grid {
            display: grid;
            gap: 25px;
        }

        .result-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: 200px 1fr;
            transition: all 0.3s;
        }

        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .result-image {
            width: 200px;
            height: 200px;
            object-fit: cover;
        }

        .result-content {
            padding: 25px;
        }

        .result-type {
            display: inline-block;
            padding: 5px 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .result-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .result-excerpt {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .result-date {
            font-size: 13px;
            color: #999;
            margin-bottom: 15px;
        }

        .btn-view {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #ff6b9d, #ffa07a);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 157, 0.3);
        }

        .no-results {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
        }

        .no-results i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 30px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            color: #764ba2;
        }

        @media (max-width: 768px) {
            .result-card {
                grid-template-columns: 1fr;
            }

            .result-image {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-search"></i> Kết quả tìm kiếm</h1>
        <p class="search-info">Từ khóa: "<strong><?php echo htmlspecialchars($keyword); ?></strong>" - Tìm thấy <?php echo count($results); ?> kết quả</p>
    </div>

    <div class="container">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Quay lại Trang chủ
        </a>

        <div class="results-grid">
            <?php if (count($results) > 0): ?>
                <?php foreach ($results as $item): ?>
                    <?php if ($item['loai'] == 'blog'): ?>
                        <div class="result-card">
                            <img src="<?php echo htmlspecialchars($item['hinh_anh'] ?? 'https://via.placeholder.com/200x200'); ?>" alt="<?php echo htmlspecialchars($item['tieu_de']); ?>" class="result-image">
                            <div class="result-content">
                                <span class="result-type"><i class="fas fa-newspaper"></i> Bài viết</span>
                                <h2 class="result-title"><?php echo htmlspecialchars($item['tieu_de']); ?></h2>
                                <p class="result-excerpt"><?php echo htmlspecialchars(substr(strip_tags($item['noi_dung']), 0, 200)) . '...'; ?></p>
                                <p class="result-date"><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($item['ngay_tao'])); ?></p>
                                <a href="pages/blog_detail.php?id=<?php echo $item['id']; ?>" class="btn-view">
                                    <i class="fas fa-eye"></i> Xem chi tiết
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="result-card">
                            <div style="width: 200px; height: 200px; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-size: 60px;">
                                <i class="fas fa-concierge-bell"></i>
                            </div>
                            <div class="result-content">
                                <span class="result-type"><i class="fas fa-concierge-bell"></i> Dịch vụ</span>
                                <h2 class="result-title"><?php echo htmlspecialchars($item['ten_dich_vu']); ?></h2>
                                <p class="result-excerpt"><?php echo htmlspecialchars($item['mo_ta']); ?></p>
                                <p class="result-date"><i class="fas fa-money-bill-wave"></i> <?php echo number_format($item['gia_dich_vu'], 0, ',', '.'); ?> VNĐ</p>
                                <a href="pages/dichvu.php#service-<?php echo $item['id']; ?>" class="btn-view">
                                    <i class="fas fa-eye"></i> Xem dịch vụ
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h2>Không tìm thấy kết quả</h2>
                    <p>Không có kết quả nào phù hợp với từ khóa "<strong><?php echo htmlspecialchars($keyword); ?></strong>"</p>
                    <p>Vui lòng thử lại với từ khóa khác</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
