<?php
include 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $ten_chi_nhanh = $_POST['ten_chi_nhanh'];
    $dia_chi = $_POST['dia_chi'];
    $so_dien_thoai = $_POST['so_dien_thoai'];
    $email = $_POST['email'];

    // Kiểm tra định dạng email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Định dạng email không hợp lệ.";
        $alert_class = "alert-error";
    }
    // Kiểm tra định dạng số điện thoại (10 chữ số, bắt đầu bằng 0)
    elseif (!preg_match('/^0\d{9}$/', $so_dien_thoai)) {
        $message = "Số điện thoại phải gồm 10 chữ số và bắt đầu bằng số 0.";
        $alert_class = "alert-error";
    } else {
        // Kiểm tra email hoặc số điện thoại đã tồn tại chưa
        $sql_check = "SELECT * FROM ChiNhanh WHERE email = ? OR so_dien_thoai = ?";
        $params_check = [$email, $so_dien_thoai];
        $stmt_check = sqlsrv_query($conn, $sql_check, $params_check);

        if ($stmt_check === false) {
            // Nếu câu truy vấn thất bại, hiển thị lỗi và dừng lại
            $errors = sqlsrv_errors();
            $message = "Lỗi khi kiểm tra trùng lặp: " . print_r($errors, true);
            $alert_class = "alert-error";
        } else {
            // Kiểm tra kết quả truy vấn
            if (sqlsrv_has_rows($stmt_check)) {
                $message = "Email hoặc số điện thoại đã tồn tại!";
                $alert_class = "alert-error";
            } else {
                // Nếu không trùng, thực hiện thêm chi nhánh
                $sql_insert = "EXEC sp_them_chi_nhanh ?, ?, ?, ?";
                $params_insert = [$ten_chi_nhanh, $dia_chi, $so_dien_thoai, $email];
                $stmt_insert = sqlsrv_query($conn, $sql_insert, $params_insert);

                if ($stmt_insert === false) {
                    $errors = sqlsrv_errors();
                    $message = "Lỗi khi thêm chi nhánh: " . print_r($errors, true);
                    $alert_class = "alert-error";
                } else {
                    $message = "Thêm chi nhánh thành công!";
                    $alert_class = "alert-success";
                }

                // Giải phóng tài nguyên của câu lệnh thêm
                if ($stmt_insert !== false) {
                    sqlsrv_free_stmt($stmt_insert);
                }
            }
            // Giải phóng tài nguyên của câu lệnh kiểm tra
            sqlsrv_free_stmt($stmt_check);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Chi Nhánh</title>
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
            <h2>Thêm Chi Nhánh</h2>

            <?php if (!empty($message)): ?>
                <div class="alert <?= $alert_class ?>"><?= $message ?></div>
            <?php endif; ?>

            <!-- Form thêm chi nhánh -->
            <form action="them_chi_nhanh.php" method="POST">
                <div class="form-group">
                    <label for="ten_chi_nhanh">Tên Chi Nhánh:</label>
                    <input type="text" id="ten_chi_nhanh" name="ten_chi_nhanh" required>
                </div>

                <div class="form-group">
                    <label for="dia_chi">Địa Chỉ:</label>
                    <input type="text" id="dia_chi" name="dia_chi" required>
                </div>

                <div class="form-group">
                    <label for="so_dien_thoai">Số Điện Thoại:</label>
                    <input type="text" id="so_dien_thoai" name="so_dien_thoai" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <input type="submit" value="Thêm Chi Nhánh">
                </div>
            </form>
        </div>
    </main>
</body>
</html>

<?php
// Đóng kết nối
sqlsrv_close($conn);
?>
