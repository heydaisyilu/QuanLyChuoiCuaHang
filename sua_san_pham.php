<?php
include 'db_connect.php';  // Kết nối cơ sở dữ liệu

// Lấy ID sản phẩm từ URL
if (isset($_GET['id'])) {
    $id_san_pham = $_GET['id'];

    // Truy vấn thông tin sản phẩm
    $sql = "SELECT * FROM SanPham WHERE id_san_pham = ?";
    $params = [$id_san_pham];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $product = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if (!$product) {
        die("Không tìm thấy sản phẩm với ID: " . $id_san_pham);
    }

    // Giải phóng tài nguyên
    sqlsrv_free_stmt($stmt);
}

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $ten_san_pham = $_POST['ten_san_pham'];
    $mo_ta = $_POST['mo_ta'];
    $gia = $_POST['gia'];
    $so_luong_ton_kho = $_POST['so_luong_ton_kho'];
    $id_chi_nhanh = $_POST['id_chi_nhanh'];

    // Kiểm tra xem tên sản phẩm có trùng trong cùng chi nhánh không
    $sql_check_name = "SELECT 1 
                       FROM SanPham 
                       WHERE ten_san_pham = ? 
                       AND id_chi_nhanh = ? 
                       AND id_san_pham != ?";
    $params_check_name = [$ten_san_pham, $id_chi_nhanh, $id_san_pham];
    $stmt_check_name = sqlsrv_query($conn, $sql_check_name, $params_check_name);

    if (sqlsrv_has_rows($stmt_check_name)) {
        // Nếu có sản phẩm trùng tên, hiển thị thông báo lỗi
        $message = "Tên sản phẩm đã tồn tại trong chi nhánh này!";
        $alert_class = "alert-error";
        sqlsrv_free_stmt($stmt_check_name);  // Giải phóng tài nguyên
    } else {
        // Gọi stored procedure để sửa sản phẩm
        $sql_update = "EXEC sp_sua_san_pham ?, ?, ?, ?, ?, ?";
        $params_update = [$id_san_pham, $ten_san_pham, $mo_ta, $gia, $so_luong_ton_kho, $id_chi_nhanh];
        $stmt_update = sqlsrv_query($conn, $sql_update, $params_update);

        // Kiểm tra lỗi khi thực thi câu lệnh
        if ($stmt_update === false) {
            $errors = sqlsrv_errors();
            $message = "Lỗi khi sửa thông tin sản phẩm: " . print_r($errors, true);
            $alert_class = "alert-error";
        } else {
            // Thông báo sửa thành công
            $message = "Sửa thông tin sản phẩm thành công!";
            $alert_class = "alert-success";
        }
        sqlsrv_free_stmt($stmt_update);  // Giải phóng tài nguyên
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Sản Phẩm</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Quản Lý Sản Phẩm</h1>
            <nav>
                <a href="index.php">Trang chủ</a>
                <a href="quanlysanpham.php">Quản lý sản phẩm</a>
                <a href="them_san_pham.php">Thêm Sản Phẩm</a>
            </nav>
        </div>
    </header>
    <main>
        <div class="form-container">
            <h2>Sửa Thông Tin Sản Phẩm</h2>

            <?php if (!empty($message)): ?>
                <div class="alert <?= $alert_class ?>"><?= $message ?></div>
            <?php endif; ?>

            <!-- Form sửa sản phẩm -->
            <form action="sua_san_pham.php?id=<?= $id_san_pham ?>" method="POST">
                <div class="form-group">
                    <label for="ten_san_pham">Tên Sản Phẩm:</label>
                    <input type="text" id="ten_san_pham" name="ten_san_pham" value="<?= $product['ten_san_pham'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="mo_ta">Mô Tả:</label>
                    <input type="text" id="mo_ta" name="mo_ta" value="<?= $product['mo_ta'] ?>">
                </div>

                <div class="form-group">
                    <label for="gia">Giá:</label>
                    <input type="number" id="gia" name="gia" value="<?= $product['gia'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="so_luong_ton_kho">Số Lượng Tồn Kho:</label>
                    <input type="number" id="so_luong_ton_kho" name="so_luong_ton_kho" value="<?= $product['so_luong_ton_kho'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="id_chi_nhanh">Chi Nhánh:</label>
                    <select id="id_chi_nhanh" name="id_chi_nhanh" required>
                        <?php
                        // Lấy danh sách chi nhánh để hiển thị trong dropdown
                        $sql_chi_nhanh = "SELECT * FROM ChiNhanh";
                        $stmt_chi_nhanh = sqlsrv_query($conn, $sql_chi_nhanh);
                        while ($row = sqlsrv_fetch_array($stmt_chi_nhanh, SQLSRV_FETCH_ASSOC)) {
                            $selected = $row['id_chi_nhanh'] == $product['id_chi_nhanh'] ? 'selected' : '';
                            echo "<option value='{$row['id_chi_nhanh']}' $selected>{$row['ten_chi_nhanh']}</option>";
                        }
                        sqlsrv_free_stmt($stmt_chi_nhanh);
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <input type="submit" value="Sửa Sản Phẩm">
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