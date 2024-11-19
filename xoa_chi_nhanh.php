<?php
include 'db_connect.php';  // Kết nối cơ sở dữ liệu

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $id_chi_nhanh = $_POST['id_chi_nhanh'];

    // Gọi stored procedure để xóa chi nhánh
    $sql = "EXEC sp_xoa_chi_nhanh ?";
    $params = [$id_chi_nhanh];
    $stmt = sqlsrv_query($conn, $sql, $params);

    // Kiểm tra lỗi khi thực thi câu lệnh
    if ($stmt === false) {
        $errors = sqlsrv_errors();
        $message = "Lỗi khi xóa chi nhánh: " . print_r($errors, true);
        $alert_class = "alert-error";
    } else {
        // Lấy thông báo từ stored procedure
        $message = "Xóa chi nhánh thành công!";
        $alert_class = "alert-success";
    }

    // Giải phóng tài nguyên
    sqlsrv_free_stmt($stmt);
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xóa Chi Nhánh</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Quản Lý Chi Nhánh</h1>
            <nav>
                <a href="index.php">Trang chủ</a>
                <a href="quanlychinhanh.php">Quản lý chi nhánh</a>
            </nav>
        </div>
    </header>
    <main>
        <div class="form-container">
            <h2>Xóa Chi Nhánh</h2>

            <?php if (!empty($message)): ?>
                <div class="alert <?= $alert_class ?>"><?= $message ?></div>
            <?php endif; ?>

            <!-- Form xóa chi nhánh -->
            <form action="xoa_chi_nhanh.php" method="POST">
                <div class="form-group">
                    <label for="id_chi_nhanh">Mã Chi Nhánh:</label>
                    <input type="text" id="id_chi_nhanh" name="id_chi_nhanh" required>
                </div>

                <div class="form-group">
                    <input type="submit" value="Xóa Chi Nhánh">
                </div>
            </form>
        </div>
    </main>
</body>
</html>

<?php
// Giải phóng tài nguyên và đóng kết nối
sqlsrv_close($conn);
?>
