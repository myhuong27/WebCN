<?php
session_start();
require_once 'config/connect.php';

// Đếm số lượng thú cưng đang chăm sóc
$sql_count_pets = "SELECT COUNT(*) as total FROM thu_cung WHERE trang_thai = 1";
$stmt_pets = $conn->prepare($sql_count_pets);
$stmt_pets->execute();
$total_pets = $stmt_pets->fetch(PDO::FETCH_ASSOC)['total'];

// Đếm số lượng dịch vụ
$sql_count_services = "SELECT COUNT(*) as total FROM dich_vu WHERE trang_thai = 1";
$stmt_services = $conn->prepare($sql_count_services);
$stmt_services->execute();
$total_services = $stmt_services->fetch(PDO::FETCH_ASSOC)['total'];

// Lấy danh sách dịch vụ
$sql_services = "SELECT * FROM dich_vu WHERE trang_thai = 1 LIMIT 6";
$stmt_services = $conn->prepare($sql_services);
$stmt_services->execute();
$services = $stmt_services->fetchAll(PDO::FETCH_ASSOC);

// Lấy bài viết mới nhất
$sql_posts = "SELECT * FROM bai_viet WHERE trang_thai = 1 ORDER BY ngay_tao DESC LIMIT 3";
$stmt_posts = $conn->prepare($sql_posts);
$stmt_posts->execute();
$posts = $stmt_posts->fetchAll(PDO::FETCH_ASSOC);

// Lấy video hướng dẫn mới nhất
$sql_videos = "SELECT * FROM video_huong_dan WHERE trang_thai = 1 ORDER BY ngay_tao DESC LIMIT 3";
$stmt_videos = $conn->prepare($sql_videos);
$stmt_videos->execute();
$videos = $stmt_videos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Nuôi Hộ và Chăm Sóc Thú Cưng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e0f7fa 0%, #fce4ec 50%, #fff9c4 100%);
            background-attachment: fixed;
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-image {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #ff6b9d, #ffa07a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 30px;
        }

        .brand-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .brand-slogan {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .nav-menu {
            display: flex;
            gap: 30px;
            align-items: center;
            justify-content: center;
        }

        .nav-link {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            padding: 10px 0;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .nav-link i {
            font-size: 14px;
        }

        .nav-link:hover {
            color: #ff6b9d;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background: #ff6b9d;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* Nav Dropdown */
        .nav-dropdown {
            position: relative;
            display: inline-block;
        }

        .nav-dropdown .nav-link {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .nav-dropdown .nav-link i {
            font-size: 10px;
            transition: transform 0.3s;
        }

        .nav-dropdown:hover .nav-link i {
            transform: rotate(180deg);
        }

        .nav-dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            min-width: 200px;
            border-radius: 8px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
            padding: 10px 0;
            margin-top: 10px;
            z-index: 1000;
        }

        .nav-dropdown:hover .nav-dropdown-menu {
            display: block;
            animation: fadeInDown 0.3s ease;
        }

        .nav-dropdown-menu .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            transition: all 0.2s;
        }

        .nav-dropdown-menu .dropdown-item:hover {
            background: #f8f9fa;
            color: #667eea;
        }

        .nav-dropdown-menu .dropdown-item i {
            width: 20px;
            font-size: 14px;
        }

        /* User Dropdown */
        .user-dropdown {
            position: relative;
        }

        .user-trigger {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .user-trigger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .user-trigger i {
            font-size: 18px;
        }

        .user-trigger span {
            font-weight: 500;
        }

        .user-trigger .dropdown-arrow {
            font-size: 12px;
            transition: transform 0.3s;
        }

        .user-dropdown.active .dropdown-arrow {
            transform: rotate(180deg);
        }

        .user-menu {
            display: none;
            position: absolute;
            top: 50px;
            right: 0;
            background: white;
            min-width: 200px;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
            overflow: hidden;
            z-index: 1000;
        }

        .user-dropdown.active .user-menu {
            display: block;
            animation: fadeInDown 0.3s ease;
        }

        .user-menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            transition: all 0.2s;
            border-bottom: 1px solid #f0f0f0;
        }

        .user-menu-item:last-child {
            border-bottom: none;
        }

        .user-menu-item:hover {
            background: #f8f9fa;
            color: #667eea;
        }

        .user-menu-item i {
            width: 20px;
            font-size: 16px;
        }

        /* Search Box */
        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 8px 40px 8px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            width: 250px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-suggestions {
            display: none;
            position: absolute;
            top: 45px;
            left: 0;
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
        }

        .search-suggestions.show {
            display: block;
            animation: fadeInDown 0.3s ease;
        }

        .suggestion-item {
            padding: 12px 15px;
            cursor: pointer;
            transition: background 0.2s;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-item:hover {
            background: #f8f9fa;
        }

        .suggestion-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .suggestion-icon.pet { background: #d4edda; color: #155724; }
        .suggestion-icon.service { background: #d1ecf1; color: #0c5460; }
        .suggestion-icon.blog { background: #fff3cd; color: #856404; }
        .suggestion-icon.category { background: #f8d7da; color: #721c24; }

        .suggestion-text {
            flex: 1;
            font-size: 14px;
            color: #333;
        }

        .search-box input:focus {
            outline: none;
            border-color: #ff6b9d;
            box-shadow: 0 0 0 3px rgba(255, 107, 157, 0.1);
        }

        .search-box button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(135deg, #ff6b9d 0%, #ffa07a 100%);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .search-box button:hover {
            transform: translateY(-50%) scale(1.1);
        }

        /* Notification Bell */
        .notification-bell {
            position: relative;
            cursor: pointer;
            padding: 8px;
        }

        .notification-bell i {
            font-size: 22px;
            color: #ff6b9d;
            transition: all 0.3s;
        }

        .notification-bell:hover i {
            color: #ffa07a;
            transform: scale(1.1);
        }

        .notification-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background: #e74c3c;
            color: white;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }

        .notification-dropdown {
            display: none;
            position: absolute;
            top: 45px;
            right: 0;
            background: white;
            width: 350px;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
            z-index: 1000;
        }

        .notification-bell.active .notification-dropdown {
            display: block;
            animation: fadeInDown 0.3s ease;
        }

        .notification-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-header h3 {
            font-size: 16px;
            color: #333;
            margin: 0;
        }

        .notification-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f5f5f5;
            display: flex;
            gap: 12px;
            align-items: start;
            cursor: pointer;
            transition: background 0.2s;
        }

        .notification-item:hover {
            background: #f8f9fa;
        }

        .notification-item.unread {
            background: #fff5f7;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .notification-icon.warning { background: #fff3cd; color: #856404; }
        .notification-icon.success { background: #d4edda; color: #155724; }
        .notification-icon.info { background: #d1ecf1; color: #0c5460; }

        .notification-content {
            flex: 1;
        }

        .notification-content .title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .notification-content .message {
            font-size: 13px;
            color: #666;
            line-height: 1.4;
        }

        .notification-content .time {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        .notification-footer {
            padding: 12px 20px;
            text-align: center;
            border-top: 1px solid #eee;
        }

        .notification-footer a {
            color: #ff6b9d;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 80px 20px;
            position: relative;
            overflow: hidden;
            margin-top: -100px;
            padding-top: 180px;
        }

        .hero-slideshow {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .hero-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 2s ease-in-out;
            animation: slideshow 20s infinite;
        }

        .hero-slide:nth-child(1) {
            background-image: url('https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=1920');
            animation-delay: 0s;
        }

        .hero-slide:nth-child(2) {
            background-image: url('https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=1920');
            animation-delay: 4s;
        }

        .hero-slide:nth-child(3) {
            background-image: url('https://images.unsplash.com/photo-1537151608828-ea2b11777ee8?w=1920');
            animation-delay: 8s;
        }

        .hero-slide:nth-child(4) {
            background-image: url('https://images.unsplash.com/photo-1561037404-61cd46aa615b?w=1920');
            animation-delay: 12s;
        }

        .hero-slide:nth-child(5) {
            background-image: url('https://images.unsplash.com/photo-1558788353-f76d92427f16?w=1920');
            animation-delay: 16s;
        }

        @keyframes slideshow {
            0% { opacity: 0; }
            5% { opacity: 1; }
            20% { opacity: 1; }
            25% { opacity: 0; }
            100% { opacity: 0; }
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            color: white;
            max-width: 900px;
            z-index: 2;
        }

        .hero-title {
            font-size: 60px;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.5);
            animation: fadeInDown 1s ease-out;
        }

        .hero-subtitle {
            font-size: 24px;
            margin-bottom: 40px;
            text-shadow: 1px 1px 5px rgba(0,0,0,0.5);
            animation: fadeInUp 1s ease-out;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .cta-button {
            display: inline-block;
            padding: 15px 40px;
            background: #ff6b9d;
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 157, 0.3);
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 107, 157, 0.5);
        }

        .cta-button.secondary {
            background: transparent;
            border: 2px solid white;
        }

        .cta-button.secondary:hover {
            background: white;
            color: #ff6b9d;
        }

        /* Services Section */
        .services {
            padding: 100px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 42px;
            margin-bottom: 60px;
            color: #333;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .service-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .service-icon {
            font-size: 50px;
            color: #ff6b9d;
            margin-bottom: 20px;
        }

        .service-title {
            font-size: 24px;
            margin-bottom: 15px;
            color: #333;
        }

        .service-description {
            color: #666;
            line-height: 1.6;
        }

        .service-price {
            font-size: 20px;
            font-weight: bold;
            color: #ff6b9d;
            margin-top: 15px;
        }

        /* Statistics Section */
        .statistics {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 80px 20px;
            color: white;
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            text-align: center;
        }

        .stat-item {
            padding: 20px;
        }

        .stat-number {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 18px;
            opacity: 0.9;
        }

        /* Features Section */
        .features {
            padding: 100px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }

        .feature-icon {
            font-size: 40px;
            color: #ff6b9d;
            margin-bottom: 15px;
        }

        .feature-title {
            font-size: 20px;
            margin-bottom: 10px;
            color: #333;
        }

        .feature-description {
            color: #666;
            line-height: 1.6;
        }

        /* Blog Section */
        .blog-section, .video-section {
            max-width: 1200px;
            margin: 80px auto;
            padding: 0 20px;
        }

        .blog-grid, .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .blog-card, .video-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .blog-card:hover, .video-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }

        .blog-image, .video-thumbnail {
            position: relative;
            height: 250px;
            overflow: hidden;
        }

        .blog-image img, .video-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .blog-card:hover .blog-image img,
        .video-card:hover .video-thumbnail img {
            transform: scale(1.1);
        }

        .blog-date {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.95);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            color: #667eea;
            font-weight: 600;
        }

        .video-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 60px;
            color: white;
            opacity: 0.9;
            transition: opacity 0.3s;
        }

        .video-card:hover .video-overlay {
            opacity: 1;
        }

        .video-duration {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
        }

        .blog-content, .video-content {
            padding: 25px;
        }

        .blog-title, .video-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 15px;
            font-weight: 600;
            line-height: 1.4;
        }

        .blog-excerpt, .video-description {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .video-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            font-size: 13px;
            color: #999;
        }

        .video-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .blog-link, .video-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: gap 0.3s;
        }

        .blog-link:hover, .video-link:hover {
            gap: 12px;
        }

        .section-footer {
            text-align: center;
            margin-top: 40px;
        }

        .view-all-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 30px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .view-all-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        /* Footer */
        .footer {
            background: #333;
            color: white;
            padding: 60px 20px 30px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 30px;
        }

        .footer-section h3 {
            margin-bottom: 20px;
            font-size: 20px;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #ddd;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #ff6b9d;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: #555;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: #ff6b9d;
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #555;
            color: #999;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="nav-container">
            <div class="logo-container">
                <div class="logo-image">
                    <i class="fas fa-paw"></i>
                </div>
                <div>
                    <div class="brand-name">Pet Care Center</div>
                    <div class="brand-slogan">Yêu thương - Chăm sóc - Tận tâm</div>
                </div>
            </div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-link">Trang chủ</a>
                <a href="pages/dichvu.php" class="nav-link">Dịch vụ</a>
                <a href="pages/dat_lich_kham.php" class="nav-link">Đặt lịch khám</a>
                <a href="pages/gui_nuoiho.php" class="nav-link">Gửi nuôi hộ</a>
                <a href="#lien-he" class="nav-link">Liên hệ</a>
                
                <!-- Search Box -->
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Tìm kiếm..." autocomplete="off" />
                    <button type="button" onclick="performSearch()">
                        <i class="fas fa-search"></i>
                    </button>
                    <div class="search-suggestions" id="searchSuggestions"></div>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Notification Bell -->
                    <div class="notification-bell" id="notificationBell" onclick="toggleNotifications()">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge" id="notificationCount">3</span>
                        
                        <!-- Notification Dropdown -->
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="notification-header">
                                <h3>Thông báo</h3>
                                <a href="javascript:void(0)" onclick="markAllAsRead()" style="color: #ff6b9d; font-size: 13px;">Đánh dấu đã đọc</a>
                            </div>
                            <div class="notification-list">
                                <!-- Thông báo sẽ được load từ API -->
                                <div style="padding: 20px; text-align: center; color: #999;">
                                    <i class="fas fa-bell-slash" style="font-size: 30px; margin-bottom: 10px;"></i>
                                    <p>Chưa có thông báo mới</p>
                                </div>
                            </div>
                            <div class="notification-footer">
                                <a href="#">Xem tất cả thông báo</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (isset($_SESSION['vai_tro']) && $_SESSION['vai_tro'] == 2): ?>
                        <!-- Menu Admin -->
                        <a href="admin/dashboard.php" class="nav-link">
                            <i class="fas fa-tachometer-alt"></i> Quản trị
                        </a>
                    <?php else: ?>
                        <!-- Menu User -->
                        <div class="user-dropdown" id="userDropdown">
                            <div class="user-trigger" onclick="toggleUserMenu()">
                                <i class="fas fa-user-circle"></i>
                                <span><?php echo htmlspecialchars($_SESSION['ho_ten'] ?? $_SESSION['username'] ?? 'User'); ?></span>
                                <i class="fas fa-chevron-down dropdown-arrow"></i>
                            </div>
                            <div class="user-menu">
                                <a href="user/profile.php" class="user-menu-item">
                                    <i class="fas fa-user"></i>
                                    <span>Hồ sơ cá nhân</span>
                                </a>
                                <a href="user/quan_ly_thucung_user.php" class="user-menu-item">
                                    <i class="fas fa-paw"></i>
                                    <span>Thú cưng của tôi</span>
                                </a>
                                <a href="auth/logout.php" class="user-menu-item">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Đăng xuất</span>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="auth/login_update.php" class="nav-link"><i class="fas fa-user"></i> Đăng nhập</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="hero">
        <div class="hero-slideshow">
            <div class="hero-slide"></div>
            <div class="hero-slide"></div>
            <div class="hero-slide"></div>
            <div class="hero-slide"></div>
            <div class="hero-slide"></div>
        </div>
        <div class="hero-content">
            <h1 class="hero-title">Nuôi Hộ & Chăm Sóc Thú Cưng</h1>
            <p class="hero-subtitle">Chúng tôi yêu thú cưng của bạn như của chính mình</p>
            <div class="cta-buttons">
                <a href="pages/dichvu.php" class="cta-button">Dịch vụ của chúng tôi</a>
                <a href="pages/gui_nuoiho.php" class="cta-button secondary">Gửi nuôi hộ</a>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <div class="services">
        <h2 class="section-title">Dịch Vụ Của Chúng Tôi</h2>
        <div class="services-grid">
            <?php
            $icons = [
                'DV001' => 'fa-home',
                'DV002' => 'fa-shower',
                'DV003' => 'fa-cut',
                'DV004' => 'fa-stethoscope',
                'DV005' => 'fa-graduation-cap',
                'DV006' => 'fa-heart'
            ];
            
            foreach ($services as $service):
                $icon = isset($icons[$service['ma_dich_vu']]) ? $icons[$service['ma_dich_vu']] : 'fa-star';
            ?>
            <div class="service-card">
                <div class="service-icon"><i class="fas <?php echo $icon; ?>"></i></div>
                <h3 class="service-title"><?php echo htmlspecialchars($service['ten_dich_vu']); ?></h3>
                <p class="service-description"><?php echo htmlspecialchars($service['mo_ta']); ?></p>
                <div class="service-price"><?php echo number_format($service['gia_dich_vu'], 0, ',', '.'); ?>đ/<?php echo htmlspecialchars($service['don_vi']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Statistics -->
    <div class="statistics">
        <div class="stats-container">
            <div class="stat-item">
                <div class="stat-number"><?php echo $total_pets; ?>+</div>
                <div class="stat-label">Thú cưng đang chăm sóc</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">300+</div>
                <div class="stat-label">Khách hàng hài lòng</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">5+</div>
                <div class="stat-label">Năm kinh nghiệm</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Hỗ trợ khách hàng</div>
            </div>
        </div>
    </div>

    <!-- Features -->
    <div class="features">
        <h2 class="section-title">Tại Sao Chọn Chúng Tôi?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-check-circle"></i></div>
                <h3 class="feature-title">Chuyên nghiệp</h3>
                <p class="feature-description">Đội ngũ nhân viên được đào tạo bài bản, có chứng chỉ chăm sóc thú cưng</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                <h3 class="feature-title">An toàn</h3>
                <p class="feature-description">Cơ sở vật chất hiện đại, đảm bảo an toàn tuyệt đối cho thú cưng</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-video"></i></div>
                <h3 class="feature-title">Giám sát 24/7</h3>
                <p class="feature-description">Hệ thống camera giám sát, bạn có thể xem thú cưng mọi lúc</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-utensils"></i></div>
                <h3 class="feature-title">Dinh dưỡng</h3>
                <p class="feature-description">Thức ăn chất lượng cao, phù hợp với từng loại thú cưng</p>
            </div>
        </div>
    </div>

    <!-- Blog Section -->
    <?php if (count($posts) > 0): ?>
    <div class="blog-section">
        <h2 class="section-title"><i class="fas fa-newspaper"></i> Bài Viết Mới Nhất</h2>
        <div class="blog-grid">
            <?php foreach ($posts as $post): ?>
            <div class="blog-card">
                <div class="blog-image">
                    <img src="<?php echo htmlspecialchars($post['hinh_anh'] ?? 'images/image/h1.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($post['tieu_de']); ?>"
                         onerror="this.src='images/image/h1.jpg'">
                    <div class="blog-date">
                        <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($post['ngay_tao'])); ?>
                    </div>
                </div>
                <div class="blog-content">
                    <h3 class="blog-title"><?php echo htmlspecialchars($post['tieu_de']); ?></h3>
                    <p class="blog-excerpt"><?php echo htmlspecialchars(strip_tags(substr($post['noi_dung'], 0, 150))) . '...'; ?></p>
                    <a href="pages/blog_detail.php?id=<?php echo $post['id']; ?>" class="blog-link">
                        Đọc thêm <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="section-footer">
            <a href="pages/blog.php" class="view-all-btn">Xem tất cả bài viết <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Video Section -->
    <?php if (count($videos) > 0): ?>
    <div class="video-section">
        <h2 class="section-title"><i class="fas fa-play-circle"></i> Video Hướng Dẫn Chăm Sóc</h2>
        <div class="video-grid">
            <?php foreach ($videos as $video): ?>
            <div class="video-card">
                <div class="video-thumbnail">
                    <img src="<?php echo htmlspecialchars($video['thumbnail'] ?? 'images/image/h1.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($video['tieu_de']); ?>"
                         onerror="this.src='images/image/h1.jpg'">
                    <div class="video-overlay">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="video-duration"><?php echo htmlspecialchars($video['thoi_luong'] ?? '5:00'); ?></div>
                </div>
                <div class="video-content">
                    <h3 class="video-title"><?php echo htmlspecialchars($video['tieu_de']); ?></h3>
                    <p class="video-description"><?php echo htmlspecialchars(substr($video['mo_ta'], 0, 100)) . '...'; ?></p>
                    <div class="video-meta">
                        <span><i class="fas fa-eye"></i> <?php echo number_format($video['luot_xem'] ?? 0); ?> lượt xem</span>
                        <span><i class="fas fa-clock"></i> <?php echo date('d/m/Y', strtotime($video['ngay_tao'])); ?></span>
                    </div>
                    <a href="pages/video_detail.php?id=<?php echo $video['id']; ?>" class="video-link">
                        Xem ngay <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="section-footer">
            <a href="pages/video.php" class="view-all-btn">Xem tất cả video <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Reviews Section -->
    <?php
    try {
        $stmt = $conn->query("SELECT dg.*, nd.ho_ten, nd.username,
                             CASE 
                                WHEN dg.loai = 'dich_vu' THEN dv.ten_dich_vu
                                WHEN dg.loai = 'nuoi_ho' THEN 'Dịch vụ nuôi hộ'
                             END as ten_dich_vu
                             FROM danh_gia dg
                             LEFT JOIN nguoi_dung nd ON dg.nguoi_dung_id = nd.id
                             LEFT JOIN dat_lich_dich_vu dl ON dg.dat_lich_id = dl.id
                             LEFT JOIN dich_vu dv ON dl.dich_vu_id = dv.id
                             WHERE dg.phan_hoi_admin IS NOT NULL
                             ORDER BY dg.ngay_tao DESC
                             LIMIT 6");
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $reviews = [];
    }
    ?>

    <?php if (count($reviews) > 0): ?>
    <div class="blog-section">
        <h2 class="section-title"><i class="fas fa-star"></i> Đánh Giá Từ Khách Hàng</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px; margin-top: 40px;">
            <?php foreach ($reviews as $review): ?>
            <div style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); transition: transform 0.3s;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 45px; height: 45px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #667eea; font-size: 20px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($review['ho_ten'] ?? $review['username']); ?></div>
                                <div style="font-size: 12px; opacity: 0.9;"><?php echo date('d/m/Y', strtotime($review['ngay_tao'])); ?></div>
                            </div>
                        </div>
                        <div style="color: #ffc107; font-size: 18px;">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?php echo $i <= $review['so_sao'] ? '' : '-o'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <div style="padding: 20px;">
                    <div style="font-size: 14px; color: #667eea; font-weight: 600; margin-bottom: 12px;">
                        <i class="fas fa-concierge-bell"></i> <?php echo htmlspecialchars($review['ten_dich_vu']); ?>
                    </div>
                    <p style="color: #333; line-height: 1.6; margin-bottom: 15px;">
                        <?php echo htmlspecialchars($review['noi_dung']); ?>
                    </p>

                    <?php if ($review['phan_hoi_admin']): ?>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; border-left: 4px solid #28a745;">
                        <div style="font-weight: 600; color: #28a745; margin-bottom: 8px; font-size: 13px;">
                            <i class="fas fa-reply"></i> Phản hồi từ Pet Care Center
                        </div>
                        <p style="color: #555; font-size: 14px; line-height: 1.5;">
                            <?php echo nl2br(htmlspecialchars($review['phan_hoi_admin'])); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer" id="lien-he">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Về Chúng Tôi</h3>
                <p>Pet Care Center - Trung tâm chăm sóc thú cưng hàng đầu Việt Nam. Chúng tôi mang đến dịch vụ chăm sóc tốt nhất cho người bạn bốn chân của bạn.</p>
            </div>

            <div class="footer-section">
                <h3>Liên Kết</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="pages/dichvu.php">Dịch vụ</a></li>
                    <li><a href="pages/gui_nuoiho.php">Gửi nuôi hộ</a></li>
                    <li><a href="gioithieu.php">Giới thiệu</a></li>
                    <li><a href="#lien-he">Liên hệ</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Liên Hệ</h3>
                <p><i class="fas fa-map-marker-alt"></i> Khóm 9 Phường 6, Phường Trà Vinh, Tỉnh Vĩnh Long</p>
                <p><i class="fas fa-phone"></i> 0379708918</p>
                <p><i class="fas fa-envelope"></i> chauthimyhuong15@gmail.com</p>
            </div>

            <div class="footer-section">
                <h3>Theo Dõi Chúng Tôi</h3>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2025 Pet Care Center. All rights reserved.</p>
        </div>
    </div>

    <script>
        // Toggle user dropdown menu
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('active');
        }

        // Toggle notification dropdown
        function toggleNotifications() {
            const bell = document.getElementById('notificationBell');
            bell.classList.toggle('active');
            
            // Load thông báo khi mở
            if (bell.classList.contains('active')) {
                loadNotifications();
            }
        }
        
        // Load thông báo từ API
        async function loadNotifications() {
            try {
                const response = await fetch('api/notification_api.php?action=get_notifications');
                
                if (!response.ok) {
                    console.warn('Không thể load thông báo');
                    return;
                }
                
                const data = await response.json();
                
                if (data.success && data.notifications.length > 0) {
                    const notificationList = document.querySelector('.notification-list');
                    if (!notificationList) return;
                    
                    notificationList.innerHTML = '';
                    
                    data.notifications.forEach(notif => {
                        const iconClass = getIconClass(notif.loai);
                        const timeAgo = getTimeAgo(notif.ngay_tao);
                        const unreadClass = notif.da_doc == 0 ? 'unread' : '';
                        
                        notificationList.innerHTML += `
                            <div class="notification-item ${unreadClass}" onclick="markAsRead(${notif.id}, '${notif.lien_ket || ''}')">
                                <div class="notification-icon ${notif.loai}">
                                    <i class="fas ${notif.icon}"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="title">${notif.tieu_de}</div>
                                    <div class="message">${notif.noi_dung}</div>
                                    <div class="time">${timeAgo}</div>
                                </div>
                            </div>
                        `;
                    });
                }
            } catch (error) {
                console.warn('Lỗi load thông báo:', error);
            }
        }
        
        // Đánh dấu đã đọc
        async function markAsRead(notificationId, link) {
            try {
                const formData = new FormData();
                formData.append('notification_id', notificationId);
                
                await fetch('api/notification_api.php?action=mark_as_read', {
                    method: 'POST',
                    body: formData
                });
                
                // Cập nhật số badge
                loadUnreadCount();
                
                // Chuyển trang nếu có link
                if (link) {
                    window.location.href = link;
                }
            } catch (error) {
                console.error('Lỗi:', error);
            }
        }
        
        // Load số lượng thông báo chưa đọc
        async function loadUnreadCount() {
            try {
                const response = await fetch('api/notification_api.php?action=get_unread_count');
                
                if (!response.ok) {
                    console.warn('Không thể load unread count');
                    return;
                }
                
                const data = await response.json();
                
                if (data.success) {
                    const badge = document.getElementById('notificationCount');
                    if (badge) {
                        if (data.count > 0) {
                            badge.textContent = data.count;
                            badge.style.display = 'flex';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                }
            } catch (error) {
                console.warn('Lỗi load unread count:', error);
            }
        }
        
        // Helper functions
        function getIconClass(loai) {
            const icons = {
                'success': 'fa-check-circle',
                'warning': 'fa-calendar-check',
                'error': 'fa-exclamation-circle',
                'info': 'fa-info-circle'
            };
            return icons[loai] || 'fa-bell';
        }
        
        function getTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            
            if (diffMins < 60) return diffMins + ' phút trước';
            if (diffHours < 24) return diffHours + ' giờ trước';
            return diffDays + ' ngày trước';
        }
        
        // Đánh dấu tất cả đã đọc
        async function markAllAsRead() {
            try {
                await fetch('api/notification_api.php?action=mark_as_read', {
                    method: 'POST'
                });
                loadNotifications();
                loadUnreadCount();
            } catch (error) {
                console.error('Lỗi:', error);
            }
        }
        
        // Load unread count khi trang load
        <?php if (isset($_SESSION['user_id'])): ?>
        window.addEventListener('DOMContentLoaded', () => {
            // Chỉ load nếu có element notification
            const badge = document.getElementById('notificationCount');
            if (badge) {
                loadUnreadCount();
                // Refresh mỗi 60 giây (tăng từ 30s)
                setInterval(loadUnreadCount, 60000);
            }
        });
        <?php endif; ?>

        // Search function
        function performSearch() {
            const searchInput = document.getElementById('searchInput').value.trim();
            if (searchInput === '') {
                alert('Vui lòng nhập từ khóa tìm kiếm');
                return;
            }
            // Redirect to search page
            window.location.href = 'search.php?q=' + encodeURIComponent(searchInput);
        }

        // Allow Enter key to trigger search
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });

            // Autocomplete suggestions
            let debounceTimer;
            searchInput.addEventListener('input', function(e) {
                clearTimeout(debounceTimer);
                const query = e.target.value.trim();
                
                if (query.length < 2) {
                    hideSuggestions();
                    return;
                }
                
                debounceTimer = setTimeout(() => {
                    fetchSuggestions(query);
                }, 300);
            });

            searchInput.addEventListener('blur', function() {
                // Delay to allow clicking on suggestions
                setTimeout(() => hideSuggestions(), 500);
            });

            // Prevent blur when clicking on suggestions
            const suggestionsContainer = document.getElementById('searchSuggestions');
            if (suggestionsContainer) {
                suggestionsContainer.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                });
            }
        }

        function fetchSuggestions(query) {
            fetch('search_suggestions.php?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    displaySuggestions(data);
                })
                .catch(error => {
                    console.error('Error fetching suggestions:', error);
                });
        }

        function displaySuggestions(suggestions) {
            const container = document.getElementById('searchSuggestions');
            
            if (suggestions.length === 0) {
                hideSuggestions();
                return;
            }
            
            const iconMap = {
                'pet': 'fa-paw',
                'service': 'fa-concierge-bell',
                'blog': 'fa-newspaper',
                'category': 'fa-tag'
            };
            
            let html = '';
            suggestions.forEach(item => {
                const icon = iconMap[item.type] || 'fa-search';
                html += `
                    <a href="${item.url}" class="suggestion-item" style="display: flex; align-items: center; gap: 10px; padding: 12px 15px; cursor: pointer; transition: background 0.2s; border-bottom: 1px solid #f0f0f0; text-decoration: none; color: inherit;">
                        <div class="suggestion-icon ${item.type}" style="width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                            <i class="fas fa-${item.icon}"></i>
                        </div>
                        <div class="suggestion-text" style="flex: 1; font-size: 14px; color: #333;">${item.title}</div>
                    </a>
                `;
            });
            
            container.innerHTML = html;
            container.classList.add('show');
        }

        function hideSuggestions() {
            const container = document.getElementById('searchSuggestions');
            if (container) {
                container.classList.remove('show');
            }
        }

        function selectSuggestion(text) {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.value = text;
                hideSuggestions();
                performSearch();
            }
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            if (dropdown && !dropdown.contains(event.target)) {
                dropdown.classList.remove('active');
            }

            const bell = document.getElementById('notificationBell');
            if (bell && !bell.contains(event.target)) {
                bell.classList.remove('active');
            }
        });

        // Mark notification as read on click
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                this.classList.remove('unread');
                
                // Update badge count
                const badge = document.getElementById('notificationCount');
                if (badge) {
                    let count = parseInt(badge.textContent);
                    if (count > 0) {
                        count--;
                        badge.textContent = count;
                        if (count === 0) {
                            badge.style.display = 'none';
                        }
                    }
                }
            });
        });
    </script>
    
    <?php require_once 'includes/chat_widget.php'; ?>
</body>
</html>
Something is wrong with the XAMPP installation :-(
