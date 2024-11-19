<?php
include 'db_connect.php'; // Kết nối tới cơ sở dữ liệu

// Lấy danh sách chi nhánh
$sql = "SELECT * FROM ChiNhanh";
$stmt_chi_nhanh = sqlsrv_query($conn, $sql);

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten_san_pham = $_POST['ten_san_pham'];
    $mo_ta = $_POST['mo_ta'];
    $gia = $_POST['gia'];
    $so_luong_ton_kho = $_POST['so_luong_ton_kho'];
    $id_chi_nhanh = $_POST['id_chi_nhanh'];

    // Thực thi stored procedure để thêm sản phẩm
    $sql = "EXEC sp_them_san_pham ?, ?, ?, ?, ?";
    $params = array($ten_san_pham, $mo_ta, $gia, $so_luong_ton_kho, $id_chi_nhanh);

    // Thực thi câu lệnh và kiểm tra kết quả
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        // Lấy lỗi chi tiết từ SQL Server
        $errors = sqlsrv_errors();
        
        // Kiểm tra mã lỗi 50001 (lỗi sản phẩm đã tồn tại)
        if (isset($errors[0]) && $errors[0]['code'] == 50001) {
            $message = "Tên sản phẩm đã tồn tại trong chi nhánh này. Vui lòng chọn tên sản phẩm khác.";
            $alert_class = "alert-error";
        } else {
            $message = "Lỗi khi thêm sản phẩm: " . print_r($errors, true);
            $alert_class = "alert-error";
        }
    } else {
        $message = "Thêm sản phẩm thành công!";
        $alert_class = "alert-success";
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Sản Phẩm</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Quản Lý Sản Phẩm</h1>
            <nav>
                <a href="index.php">Trang chủ</a>
                <a href="quanlysanpham.php">Quản lý sản phẩm</a>
            </nav>
        </div>
    </header>
    <main>
        <div class="form-container">
            <h2>Thêm Sản Phẩm Mới</h2>
            <?php if (!empty($message)): ?>
                <div class="alert <?= $alert_class ?>"><?= $message ?></div>
            <?php endif; ?>
            <form action="them_san_pham.php" method="POST">
                <div class="form-group">
                    <label for="ten_san_pham">Tên Sản Phẩm:</label>
                    <input type="text" id="ten_san_pham" name="ten_san_pham" required>
                </div>

                <div class="form-group">
                    <label for="mo_ta">Mô Tả:</label>
                    <input type="text" id="mo_ta" name="mo_ta" required>
                </div>

                <div class="form-group">
                    <label for="gia">Giá:</label>
                    <input type="number" id="gia" name="gia" step="1" required>
                </div>

                <div class="form-group">
                    <label for="so_luong_ton_kho">Số Lượng Tồn Kho:</label>
                    <input type="number" id="so_luong_ton_kho" name="so_luong_ton_kho" required>
                </div>

                <div class="form-group">
                    <label for="id_chi_nhanh">Chi Nhánh:</label>
                    <select id="id_chi_nhanh" name="id_chi_nhanh" required>
                        <?php while ($row = sqlsrv_fetch_array($stmt_chi_nhanh, SQLSRV_FETCH_ASSOC)): ?>
                            <option value="<?= $row['id_chi_nhanh'] ?>"><?= $row['ten_chi_nhanh'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <input type="submit" value="Thêm Sản Phẩm">
                </div>
            </form>
        </div>
    </main>
</body>
</html>

<?php
// Giải phóng tài nguyên và đóng kết nối
sqlsrv_free_stmt($stmt_chi_nhanh);
sqlsrv_close($conn);
?>
