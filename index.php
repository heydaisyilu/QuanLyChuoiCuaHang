<?php
include 'db_connect.php';

// Lấy danh sách các chi nhánh từ cơ sở dữ liệu
$sql = "SELECT * FROM ChiNhanh";
$stmt_chi_nhanh = sqlsrv_query($conn, $sql);

// Kiểm tra kết nối cơ sở dữ liệu
if ($stmt_chi_nhanh === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý cửa hàng</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #333;
            color: white;
            padding: 10px 0;
            text-align: center;
        }

        header nav a {
            color: white;
            margin: 0 15px;
            text-decoration: none;
            font-weight: bold;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px 0;
        }

        h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 1.8em;
            margin-bottom: 20px;
        }

        .chi-nhanh-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
        }

        .chi-nhanh-item {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: calc(33.33% - 20px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .chi-nhanh-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .chi-nhanh-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chi-nhanh-item h3 {
            font-size: 1.3em;
            color: #333;
            margin-right: 15px; /* Thêm khoảng cách giữa tên và địa chỉ */
        }

        .chi-nhanh-item p {
            font-size: 1em;
            color: #555;
            margin: 0;
        }

        .chi-nhanh-link {
            text-decoration: none;
            color: inherit;
        }

        .chi-nhanh-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .chi-nhanh-item {
                width: calc(50% - 20px);
            }
        }

        @media (max-width: 480px) {
            .chi-nhanh-item {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Hệ thống quản lý cửa hàng</h1>
            <nav>
                <a href="index.php">Trang chủ</a>
                <a href="quanlychinhanh.php">Quản lý chi nhánh</a>
                <a href="quanlynhanvien.php">Quản lý nhân viên</a>
                <a href="quanlysanpham.php">Quản lý sản phẩm</a> <!-- Thêm mục Quản lý sản phẩm -->
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <h2>Danh Sách Chi Nhánh</h2>
            <?php if (sqlsrv_has_rows($stmt_chi_nhanh)): ?>
                <div class="chi-nhanh-list">
                    <?php while ($row = sqlsrv_fetch_array($stmt_chi_nhanh, SQLSRV_FETCH_ASSOC)): ?>
                        <div class="chi-nhanh-item">
                            <a href="chitietchinhanh.php?id_chi_nhanh=<?= $row['id_chi_nhanh'] ?>" class="chi-nhanh-link">
                                <div class="chi-nhanh-info">
                                    <h3>Tên chi nhánh: <?= $row['ten_chi_nhanh'] ?></h3>
                                    <p>Địa chỉ: <?= $row['dia_chi'] ?></p>
                                </div>
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>Không có chi nhánh nào trong hệ thống.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

<?php
sqlsrv_free_stmt($stmt_chi_nhanh);
sqlsrv_close($conn);
?>
