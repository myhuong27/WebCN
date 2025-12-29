<?php
session_start();
require_once '../config/connect.php';

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [
    'pets' => [],
    'services' => [],
    'blogs' => []
];

if (!empty($search_query)) {
    try {
        // Tách từ khóa thành mảng các từ
        $keywords = explode(' ', $search_query);
        $search_param = "%{$search_query}%";
        
        // Tạo điều kiện tìm kiếm linh hoạt cho từng từ khóa
        $keywords_conditions = [];
        $keywords_params = [];
        foreach ($keywords as $keyword) {
            if (strlen(trim($keyword)) > 0) {
                $keywords_conditions[] = "ten_thu_cung LIKE ? OR loai_thu_cung LIKE ? OR mo_ta LIKE ?";
                $param = "%{$keyword}%";
                $keywords_params[] = $param;
                $keywords_params[] = $param;
                $keywords_params[] = $param;
            }
        }
        
        // Tìm kiếm thú cưng
        if (!empty($keywords_conditions)) {
            $sql = "SELECT * FROM thu_cung 
                   WHERE ten_thu_cung LIKE ? 
                   OR loai_thu_cung LIKE ?
                   OR mo_ta LIKE ?
                   OR (" . implode(' OR ', $keywords_conditions) . ")
                   LIMIT 10";
            $stmt = $conn->prepare($sql);
            $params = array_merge([$search_param, $search_param, $search_param], $keywords_params);
            $stmt->execute($params);
            $results['pets'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Tìm kiếm dịch vụ
        $keywords_conditions = [];
        $keywords_params = [];
        foreach ($keywords as $keyword) {
            if (strlen(trim($keyword)) > 0) {
                $keywords_conditions[] = "ten_dich_vu LIKE ? OR mo_ta LIKE ?";
                $param = "%{$keyword}%";
                $keywords_params[] = $param;
                $keywords_params[] = $param;
            }
        }
        
        if (!empty($keywords_conditions)) {
            $sql = "SELECT * FROM dich_vu 
                   WHERE ten_dich_vu LIKE ? 
                   OR mo_ta LIKE ?
                   OR (" . implode(' OR ', $keywords_conditions) . ")
                   LIMIT 10";
            $stmt = $conn->prepare($sql);
            $params = array_merge([$search_param, $search_param], $keywords_params);
            $stmt->execute($params);
            $results['services'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Tìm kiếm bài viết blog
        $keywords_conditions = [];
        $keywords_params = [];
        foreach ($keywords as $keyword) {
            if (strlen(trim($keyword)) > 0) {
                $keywords_conditions[] = "bv.tieu_de LIKE ? OR bv.noi_dung LIKE ? OR bv.tom_tat LIKE ?";
                $param = "%{$keyword}%";
                $keywords_params[] = $param;
                $keywords_params[] = $param;
                $keywords_params[] = $param;
            }
        }
        
        if (!empty($keywords_conditions)) {
            $sql = "SELECT bv.*, dmbv.ten_danh_muc 
                   FROM bai_viet bv
                   LEFT JOIN danh_muc_bai_viet dmbv ON bv.danh_muc_id = dmbv.id
                   WHERE bv.tieu_de LIKE ? 
                   OR bv.noi_dung LIKE ?
                   OR bv.tom_tat LIKE ?
                   OR (" . implode(' OR ', $keywords_conditions) . ")
                   LIMIT 10";
            $stmt = $conn->prepare($sql);
            $params = array_merge([$search_param, $search_param, $search_param], $keywords_params);
            $stmt->execute($params);
            $results['blogs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

    } catch(PDOException $e) {
        $error = $e->getMessage();
    }
}

$total_results = count($results['pets']) + count($results['services']) + count($results['blogs']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm - Pet Care Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f5f7fa;
        }

        .header {
            background: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-image {
            width: 50px;
            height: 50px;
            background: linear-gradient(45deg, #ff6b9d, #ffa07a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .brand-name {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .back-link {
            text-decoration: none;
            color: #ff6b9d;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .back-link:hover {
            gap: 12px;
        }

        .search-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .search-header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .search-box-large {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-box-large input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .search-box-large input:focus {
            outline: none;
            border-color: #ff6b9d;
            box-shadow: 0 0 0 3px rgba(255, 107, 157, 0.1);
        }

        .search-box-large button {
            padding: 15px 40px;
            background: linear-gradient(135deg, #ff6b9d 0%, #ffa07a 100%);
            border: none;
            color: white;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .search-box-large button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 157, 0.3);
        }

        .search-info {
            color: #666;
            font-size: 14px;
        }

        .search-info strong {
            color: #333;
        }

        .results-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 22px;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #ff6b9d;
        }

        .result-item {
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .result-item:hover {
            border-color: #ff6b9d;
            box-shadow: 0 5px 15px rgba(255, 107, 157, 0.1);
            transform: translateY(-2px);
        }

        .result-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .result-title a {
            color: #333;
            text-decoration: none;
            transition: color 0.3s;
        }

        .result-title a:hover {
            color: #ff6b9d;
        }

        .result-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .result-meta {
            display: flex;
            gap: 15px;
            font-size: 13px;
            color: #999;
        }

        .result-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .no-results i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .no-results h3 {
            font-size: 24px;
            color: #666;
            margin-bottom: 10px;
        }

        .highlight {
            background: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="nav-container">
            <a href="../index.php" style="text-decoration: none; color: inherit;"><div class="logo-container">
                <div class="logo-image">
                    <i class="fas fa-paw"></i>
                </div>
                <div>
                    <div class="brand-name">Pet Care Center</div>
                </div>
            </div>
            <a href="../index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Search Container -->
    <div class="search-container">
        <!-- Search Box -->
        <div class="search-header">
            <form method="GET" action="search.php" class="search-box-large">
                <input 
                    type="text" 
                    name="q" 
                    placeholder="Tìm kiếm thú cưng, dịch vụ, bài viết..." 
                    value="<?php echo htmlspecialchars($search_query); ?>"
                    required
                />
                <button type="submit">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </form>
            <?php if (!empty($search_query)): ?>
                <div class="search-info">
                    Tìm thấy <strong><?php echo $total_results; ?></strong> kết quả cho "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($search_query)): ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>Nhập từ khóa để tìm kiếm</h3>
                <p>Bạn có thể tìm kiếm thú cưng, dịch vụ hoặc bài viết blog</p>
            </div>
        <?php elseif ($total_results == 0): ?>
            <div class="no-results">
                <i class="fas fa-search-minus"></i>
                <h3>Không tìm thấy kết quả nào</h3>
                <p>Thử tìm kiếm với từ khóa khác</p>
            </div>
        <?php else: ?>
            
            <!-- Thú cưng -->
            <?php if (count($results['pets']) > 0): ?>
            <div class="results-section">
                <h2 class="section-title">
                    <i class="fas fa-paw"></i>
                    Thú cưng (<?php echo count($results['pets']); ?>)
                </h2>
                <?php foreach ($results['pets'] as $pet): ?>
                <div class="result-item">
                    <div class="result-title">
                        <a href="chitiet_thucung.php?id=<?php echo $pet['id']; ?>">
                            <?php echo htmlspecialchars($pet['ten_thu_cung']); ?>
                        </a>
                    </div>
                    <div class="result-description">
                        <?php echo htmlspecialchars(substr($pet['mo_ta'] ?? 'Chưa có mô tả', 0, 200)); ?>...
                    </div>
                    <div class="result-meta">
                        <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($pet['loai_thu_cung']); ?></span>
                        <span><i class="fas fa-birthday-cake"></i> <?php echo htmlspecialchars($pet['tuoi']); ?> tuổi</span>
                        <span><i class="fas fa-venus-mars"></i> <?php echo $pet['gioi_tinh'] == 1 ? 'Đực' : 'Cái'; ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Dịch vụ -->
            <?php if (count($results['services']) > 0): ?>
            <div class="results-section">
                <h2 class="section-title">
                    <i class="fas fa-concierge-bell"></i>
                    Dịch vụ (<?php echo count($results['services']); ?>)
                </h2>
                <?php foreach ($results['services'] as $service): ?>
                <div class="result-item">
                    <div class="result-title">
                        <a href="dichvu.php#service-<?php echo $service['id']; ?>">
                            <?php echo htmlspecialchars($service['ten_dich_vu']); ?>
                        </a>
                    </div>
                    <div class="result-description">
                        <?php echo htmlspecialchars(substr($service['mo_ta'] ?? 'Chưa có mô tả', 0, 200)); ?>...
                    </div>
                    <div class="result-meta">
                        <span><i class="fas fa-dollar-sign"></i> <?php echo number_format($service['gia']); ?>đ</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Blog -->
            <?php if (count($results['blogs']) > 0): ?>
            <div class="results-section">
                <h2 class="section-title">
                    <i class="fas fa-newspaper"></i>
                    Bài viết (<?php echo count($results['blogs']); ?>)
                </h2>
                <?php foreach ($results['blogs'] as $blog): ?>
                <div class="result-item">
                    <div class="result-title">
                        <a href="blog_detail.php?id=<?php echo $blog['id']; ?>">
                            <?php echo htmlspecialchars($blog['tieu_de']); ?>
                        </a>
                    </div>
                    <div class="result-description">
                        <?php echo htmlspecialchars(substr($blog['tom_tat'] ?? strip_tags($blog['noi_dung']), 0, 200)); ?>...
                    </div>
                    <div class="result-meta">
                        <span><i class="fas fa-folder"></i> <?php echo htmlspecialchars($blog['ten_danh_muc'] ?? 'Chưa phân loại'); ?></span>
                        <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($blog['ngay_tao'])); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</body>
</html>
