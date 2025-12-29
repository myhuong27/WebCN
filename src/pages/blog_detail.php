<?php
session_start();
require_once '../config/connect.php';

$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    // Tăng lượt xem
    $stmt = $conn->prepare("UPDATE bai_viet SET luot_xem = luot_xem + 1 WHERE id = ?");
    $stmt->execute([$post_id]);
    
    // Lấy thông tin bài viết
    $stmt = $conn->prepare("SELECT bv.*, nd.ho_ten as tac_gia
                           FROM bai_viet bv
                           LEFT JOIN nguoi_dung nd ON bv.tac_gia_id = nd.id
                           WHERE bv.id = ? AND bv.trang_thai = 1");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        die("Không tìm thấy bài viết!");
    }
    
    // Bài viết liên quan
    $stmt = $conn->prepare("SELECT * FROM bai_viet 
                           WHERE danh_muc = ? AND id != ? AND trang_thai = 1 
                           ORDER BY ngay_tao DESC LIMIT 3");
    $stmt->execute([$post['danh_muc'], $post_id]);
    $related_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}

$categories = [
    'cham-soc' => 'Chăm sóc',
    'dinh-duong' => 'Dinh dưỡng',
    'huan-luyen' => 'Huấn luyện',
    'suc-khoe' => 'Sức khỏe',
    'hanh-vi' => 'Hành vi'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['tieu_de']); ?> - Pet Care Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f6fa;
            line-height: 1.6;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 30px;
        }

        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background: white;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .back-btn:hover {
            background: #f0f0f0;
        }

        .article {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }

        .article-header {
            padding: 40px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .article-category {
            display: inline-block;
            padding: 6px 18px;
            background: rgba(255,255,255,0.3);
            border-radius: 20px;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .article-title {
            font-size: 42px;
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .article-meta {
            display: flex;
            gap: 30px;
            font-size: 15px;
            opacity: 0.9;
        }

        .article-meta i {
            margin-right: 8px;
        }

        .article-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
        }

        .article-content {
            padding: 50px;
            font-size: 18px;
            color: #333;
        }

        .article-content p {
            margin-bottom: 20px;
        }

        .article-content h2 {
            margin-top: 35px;
            margin-bottom: 15px;
            color: #f5576c;
        }

        .article-content ul, .article-content ol {
            margin: 20px 0;
            padding-left: 30px;
        }

        .article-content li {
            margin-bottom: 10px;
        }

        .article-summary {
            background: #fff3cd;
            padding: 20px;
            border-left: 4px solid #ffc107;
            margin: 30px 0;
            border-radius: 5px;
        }

        .article-summary strong {
            color: #856404;
        }

        .related-posts {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .related-posts h3 {
            margin-bottom: 30px;
            font-size: 28px;
            color: #333;
        }

        .related-grid {
            display: grid;
            gap: 20px;
        }

        .related-card {
            display: flex;
            gap: 20px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 10px;
            transition: all 0.3s;
            text-decoration: none;
            color: #333;
        }

        .related-card:hover {
            border-color: #f5576c;
            transform: translateX(10px);
        }

        .related-card img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }

        .related-card-content h4 {
            margin-bottom: 10px;
            color: #333;
        }

        .related-card-content p {
            color: #666;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .article-title {
                font-size: 28px;
            }

            .article-content {
                padding: 30px 20px;
                font-size: 16px;
            }

            .related-card {
                flex-direction: column;
            }

            .related-card img {
                width: 100%;
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin') !== false): ?>
            <a href="../admin/quan_ly_baiviet.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại Quản lý bài viết
            </a>
        <?php else: ?>
            <a href="blog.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại Blog
            </a>
        <?php endif; ?>

        <article class="article">
            <div class="article-header">
                <span class="article-category">
                    <?php echo $categories[$post['danh_muc']] ?? $post['danh_muc']; ?>
                </span>
                <h1 class="article-title"><?php echo htmlspecialchars($post['tieu_de']); ?></h1>
                <div class="article-meta">
                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($post['tac_gia']); ?></span>
                    <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($post['ngay_tao'])); ?></span>
                    <span><i class="fas fa-eye"></i> <?php echo number_format($post['luot_xem']); ?> lượt xem</span>
                </div>
            </div>

            <img src="<?php echo htmlspecialchars($post['hinh_anh'] ?? 'https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=900'); ?>" 
                 alt="<?php echo htmlspecialchars($post['tieu_de']); ?>"
                 class="article-image">

            <div class="article-content">
                <div class="article-summary">
                    <strong>Tóm tắt:</strong> <?php echo htmlspecialchars($post['tom_tat']); ?>
                </div>

                <?php echo $post['noi_dung']; ?>
            </div>
        </article>

        <?php if (!empty($related_posts)): ?>
            <div class="related-posts">
                <h3><i class="fas fa-newspaper"></i> Bài viết liên quan</h3>
                <div class="related-grid">
                    <?php foreach ($related_posts as $related): ?>
                        <a href="blog_detail.php?id=<?php echo $related['id']; ?>" class="related-card">
                            <img src="<?php echo htmlspecialchars($related['hinh_anh'] ?? 'https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=200'); ?>" 
                                 alt="<?php echo htmlspecialchars($related['tieu_de']); ?>">
                            <div class="related-card-content">
                                <h4><?php echo htmlspecialchars($related['tieu_de']); ?></h4>
                                <p><?php echo htmlspecialchars(substr($related['tom_tat'], 0, 100)); ?>...</p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
