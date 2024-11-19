<?php
include 'db_connect.php';  // Kết nối cơ sở dữ liệu

// Lấy danh sách chi nhánh
$sql_chi_nhanh = "SELECT * FROM ChiNhanh";
$stmt_chi_nhanh = sqlsrv_query($conn, $sql_chi_nhanh);

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $ten_nhan_vien = $_POST['ten_nhan_vien'];
    $chuc_vu = $_POST['chuc_vu'];
    $so_dien_thoai = $_POST['so_dien_thoai'];
    $email = $_POST['email'];
    $ngay_bat_dau_lam = $_POST['ngay_bat_dau_lam'];
    $id_chi_nhanh = $_POST['id_chi_nhanh'];

    // Kiểm tra định dạng số điện thoại
    if (!preg_match('/^0\d{9}$/', $so_dien_thoai)) {
        $message = "Số điện thoại không hợp lệ. Số điện thoại phải có 10 chữ số và bắt đầu bằng số 0.";
        $alert_class = "alert-error";
    }
    // Kiểm tra định dạng email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email không hợp lệ. Hãy kiểm tra lại.";
        $alert_class = "alert-error";
    } 
    else {
        // Kiểm tra xem số điện thoại đã tồn tại trong cơ sở dữ liệu không
        $sql_check_so_dien_thoai = "SELECT COUNT(*) AS phone_count FROM NhanVien WHERE so_dien_thoai = ?";
        $stmt_check_so_dien_thoai = sqlsrv_query($conn, $sql_check_so_dien_thoai, array($so_dien_thoai));
        $row = sqlsrv_fetch_array($stmt_check_so_dien_thoai, SQLSRV_FETCH_ASSOC);

        // Kiểm tra xem email có tồn tại trong cơ sở dữ liệu không
        $sql_check_email = "SELECT COUNT(*) AS email_count FROM NhanVien WHERE email = ?";
        $stmt_check_email = sqlsrv_query($conn, $sql_check_email, array($email));
        $row_email = sqlsrv_fetch_array($stmt_check_email, SQLSRV_FETCH_ASSOC);

        // Kiểm tra các điều kiện nếu số điện thoại hoặc email đã tồn tại
        if ($row['phone_count'] > 0) {
            // Nếu số điện thoại đã tồn tại
            $message = "Số điện thoại này đã tồn tại trong cơ sở dữ liệu!";
            $alert_class = "alert-error";
        } elseif ($row_email['email_count'] > 0) {
            // Nếu email đã tồn tại
            $message = "Email này đã tồn tại trong cơ sở dữ liệu!";
            $alert_class = "alert-error";
        } else {
            // Nếu không có trùng, thực thi stored procedure để thêm nhân viên
            $sql = "EXEC sp_them_nhan_vien ?, ?, ?, ?, ?, ?";
            $params = [$ten_nhan_vien, $chuc_vu, $so_dien_thoai, $email, $ngay_bat_dau_lam, $id_chi_nhanh];

            // Thực thi câu lệnh
            $stmt = sqlsrv_query($conn, $sql, $params);

            // Kiểm tra xem câu lệnh có thực thi thành công không
            if ($stmt === false) {
                $errors = sqlsrv_errors();
                $message = "Lỗi khi thêm nhân viên: " . print_r($errors, true);
                $alert_class = "alert-error";
            } else {
                $message = "Thêm nhân viên thành công!";
                $alert_class = "alert-success";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Nhân Viên</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Quản Lý Nhân Viên</h1>
            <nav>
                <a href="index.php">Trang chủ</a>
                <a href="quanlynhanvien.php">Quản lý nhân viên</a>
            </nav>
        </div>
    </header>
    <main>
        <div class="form-container">
            <h2>Thêm Nhân Viên Mới</h2>

            <?php if (!empty($message)): ?>
                <div class="alert <?= $alert_class ?>"><?= $message ?></div>
            <?php endif; ?>

            <!-- Form thêm nhân viên -->
            <form action="them_nhan_vien.php" method="POST">
                <div class="form-group">
                    <label for="ten_nhan_vien">Tên Nhân Viên:</label>
                    <input type="text" id="ten_nhan_vien" name="ten_nhan_vien" required>
                </div>

                <div class="form-group">
                    <label for="chuc_vu">Chức Vụ:</label>
                    <input type="text" id="chuc_vu" name="chuc_vu">
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
                    <label for="ngay_bat_dau_lam">Ngày Bắt Đầu Làm:</label>
                    <input type="date" id="ngay_bat_dau_lam" name="ngay_bat_dau_lam" required>
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
                    <input type="submit" value="Thêm Nhân Viên">
                </div>
            </form>
        </div>
    </main>
</body>
</html>

<?php
// Giải phóng bộ nhớ và đóng kết nối
sqlsrv_free_stmt($stmt_chi_nhanh);
sqlsrv_close($conn);
?>
