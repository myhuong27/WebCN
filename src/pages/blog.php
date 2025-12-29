<?php
session_start();
require_once '../config/connect.php';

// Lấy danh sách bài viết
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

try {
    if ($category == 'all') {
        $stmt = $conn->query("SELECT bv.*, nd.ho_ten as tac_gia
                              FROM bai_viet bv
                              LEFT JOIN nguoi_dung nd ON bv.tac_gia_id = nd.id
                              WHERE bv.trang_thai = 1
                              ORDER BY bv.ngay_tao DESC");
    } else {
        $stmt = $conn->prepare("SELECT bv.*, nd.ho_ten as tac_gia
                               FROM bai_viet bv
                               LEFT JOIN nguoi_dung nd ON bv.tac_gia_id = nd.id
                               WHERE bv.trang_thai = 1 AND bv.danh_muc = ?
                               ORDER BY bv.ngay_tao DESC");
        $stmt->execute([$category]);
    }
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Chăm Sóc Thú Cưng - Pet Care Center</title>
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
            background: #f8f9fa;
            line-height: 1.6;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .header h1 {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .header p {
            font-size: 20px;
            opacity: 0.9;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        .categories {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
            overflow-x: auto;
            padding: 10px 0;
        }

        .category-btn {
            padding: 12px 25px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 25px;
            cursor: pointer;
            font-size: 15px;
            transition: all 0.3s;
            white-space: nowrap;
            text-decoration: none;
            color: #555;
        }

        .category-btn:hover {
            border-color: #3498db;
            color: #3498db;
            background: #f0f8ff;
        }

        .category-btn.active {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            border-color: transparent;
        }

        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }

        .post-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            cursor: pointer;
        }

        .post-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .post-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }

        .post-content {
            padding: 25px;
        }

        .post-category {
            display: inline-block;
            padding: 5px 15px;
            background: #3498db;
            color: white;
            border-radius: 15px;
            font-size: 12px;
            margin-bottom: 15px;
        }

        .post-title {
            font-size: 22px;
            color: #2c3e50;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .post-excerpt {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .post-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #999;
        }

        .post-author {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .read-more {
            color: #3498db;
            font-weight: 600;
            text-decoration: none;
        }

        .read-more:hover {
            color: #2c3e50;
            text-decoration: underline;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .featured-post {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .featured-post .post-image {
            height: 100%;
            min-height: 400px;
        }

        .featured-post .post-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px;
        }

        .featured-badge {
            background: #f39c12;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 15px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .featured-post {
                grid-template-columns: 1fr;
            }

            .posts-grid {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1><i class="fas fa-newspaper"></i> Blog Chăm Sóc Thú Cưng</h1>
        <p>Chia sẻ kinh nghiệm và kiến thức nuôi dưỡng thú cưng khỏe mạnh</p>
    </div>

    <div class="container">
        <!-- Categories -->
        <div class="categories">
            <a href="blog.php?category=all" class="category-btn <?php echo $category == 'all' ? 'active' : ''; ?>">
                <i class="fas fa-th"></i> Tất cả
            </a>
            <a href="blog.php?category=cham-soc" class="category-btn <?php echo $category == 'cham-soc' ? 'active' : ''; ?>">
                <i class="fas fa-heart"></i> Chăm sóc
            </a>
            <a href="blog.php?category=dinh-duong" class="category-btn <?php echo $category == 'dinh-duong' ? 'active' : ''; ?>">
                <i class="fas fa-utensils"></i> Dinh dưỡng
            </a>
            <a href="blog.php?category=huan-luyen" class="category-btn <?php echo $category == 'huan-luyen' ? 'active' : ''; ?>">
                <i class="fas fa-graduation-cap"></i> Huấn luyện
            </a>
            <a href="blog.php?category=suc-khoe" class="category-btn <?php echo $category == 'suc-khoe' ? 'active' : ''; ?>">
                <i class="fas fa-heartbeat"></i> Sức khỏe
            </a>
            <a href="blog.php?category=hanh-vi" class="category-btn <?php echo $category == 'hanh-vi' ? 'active' : ''; ?>">
                <i class="fas fa-brain"></i> Hành vi
            </a>
        </div>

        <!-- Posts Grid -->
        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Chưa có bài viết nào</h3>
                <p>Hãy quay lại sau để đọc những bài viết mới nhé!</p>
            </div>
        <?php else: ?>
            <div class="posts-grid">
                <?php foreach ($posts as $index => $post): ?>
                    <?php if ($index == 0 && $category == 'all'): // Featured post ?>
                        <div class="featured-post">
                            <img src="<?php echo htmlspecialchars($post['hinh_anh'] ?? 'https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=800'); ?>" 
                                 alt="<?php echo htmlspecialchars($post['tieu_de']); ?>"
                                 class="post-image">
                            <div class="post-content">
                                <span class="featured-badge">
                                    <i class="fas fa-star"></i> Nổi bật
                                </span>
                                <span class="post-category">
                                    <?php 
                                    $categories = [
                                        'cham-soc' => 'Chăm sóc',
                                        'dinh-duong' => 'Dinh dưỡng',
                                        'huan-luyen' => 'Huấn luyện',
                                        'suc-khoe' => 'Sức khỏe',
                                        'hanh-vi' => 'Hành vi'
                                    ];
                                    echo $categories[$post['danh_muc']] ?? $post['danh_muc'];
                                    ?>
                                </span>
                                <h2 class="post-title"><?php echo htmlspecialchars($post['tieu_de']); ?></h2>
                                <p class="post-excerpt"><?php echo htmlspecialchars(substr($post['tom_tat'], 0, 200)); ?>...</p>
                                <div class="post-meta">
                                    <div class="post-author">
                                        <i class="fas fa-user-circle"></i>
                                        <span><?php echo htmlspecialchars($post['tac_gia']); ?></span>
                                    </div>
                                    <div>
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('d/m/Y', strtotime($post['ngay_tao'])); ?>
                                    </div>
                                </div>
                                <br>
                                <a href="blog_detail.php?id=<?php echo $post['id']; ?>" class="read-more">
                                    Đọc tiếp <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php else: // Regular posts ?>
                        <div class="post-card" onclick="window.location='blog_detail.php?id=<?php echo $post['id']; ?>'">
                            <img src="<?php echo htmlspecialchars($post['hinh_anh'] ?? 'https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=400'); ?>" 
                                 alt="<?php echo htmlspecialchars($post['tieu_de']); ?>"
                                 class="post-image">
                            <div class="post-content">
                                <span class="post-category">
                                    <?php 
                                    $categories = [
                                        'cham-soc' => 'Chăm sóc',
                                        'dinh-duong' => 'Dinh dưỡng',
                                        'huan-luyen' => 'Huấn luyện',
                                        'suc-khoe' => 'Sức khỏe',
                                        'hanh-vi' => 'Hành vi'
                                    ];
                                    echo $categories[$post['danh_muc']] ?? $post['danh_muc'];
                                    ?>
                                </span>
                                <h3 class="post-title"><?php echo htmlspecialchars($post['tieu_de']); ?></h3>
                                <p class="post-excerpt"><?php echo htmlspecialchars(substr($post['tom_tat'], 0, 120)); ?>...</p>
                                <div class="post-meta">
                                    <div class="post-author">
                                        <i class="fas fa-user-circle"></i>
                                        <span><?php echo htmlspecialchars($post['tac_gia']); ?></span>
                                    </div>
                                    <div>
                                        <i class="fas fa-eye"></i>
                                        <?php echo number_format($post['luot_xem']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 50px;">
            <a href="../index.php" style="padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 5px; text-decoration: none; display: inline-block;">
                <i class="fas fa-home"></i> Về trang chủ
            </a>
        </div>
    </div>
<?php require_once '../includes/chat_widget.php'; ?>
</body>
</html>
