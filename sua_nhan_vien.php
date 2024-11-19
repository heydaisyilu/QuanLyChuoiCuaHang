<?php
include 'db_connect.php';  // Kết nối cơ sở dữ liệu

// Lấy thông tin nhân viên cần sửa từ URL
$id_nhan_vien = $_GET['id_nhan_vien'];

// Truy vấn thông tin nhân viên từ cơ sở dữ liệu
$sql = "SELECT * FROM NhanVien WHERE id_nhan_vien = ?";
$params = [$id_nhan_vien];
$stmt = sqlsrv_query($conn, $sql, $params);

// Kiểm tra xem có dữ liệu trả về không
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$employee = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$employee) {
    die("Không tìm thấy nhân viên với ID: " . $id_nhan_vien);
}

// Chuyển đổi ngày tháng từ kiểu `DATE` sang định dạng `YYYY-MM-DD`
$ngay_bat_dau_lam = $employee['ngay_bat_dau_lam'];
if ($ngay_bat_dau_lam instanceof DateTime) {
    $ngay_bat_dau_lam = $ngay_bat_dau_lam->format('Y-m-d');
}

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
        // Kiểm tra số điện thoại đã tồn tại chưa (ngoại trừ nhân viên hiện tại)
        $sql_check_phone = "SELECT * FROM NhanVien WHERE so_dien_thoai = ? AND id_nhan_vien != ?";
        $params_check_phone = [$so_dien_thoai, $id_nhan_vien];
        $stmt_check_phone = sqlsrv_query($conn, $sql_check_phone, $params_check_phone);
        
        if (sqlsrv_has_rows($stmt_check_phone)) {
            $message = "Số điện thoại đã tồn tại cho nhân viên khác!";
            $alert_class = "alert-error";
        } else {
            // Kiểm tra email đã tồn tại chưa (ngoại trừ nhân viên hiện tại)
            $sql_check_email = "SELECT * FROM NhanVien WHERE email = ? AND id_nhan_vien != ?";
            $params_check_email = [$email, $id_nhan_vien];
            $stmt_check_email = sqlsrv_query($conn, $sql_check_email, $params_check_email);

            if (sqlsrv_has_rows($stmt_check_email)) {
                $message = "Email đã tồn tại cho nhân viên khác!";
                $alert_class = "alert-error";
            } else {
                // Thực thi stored procedure để sửa thông tin nhân viên
                $sql_update = "EXEC sp_sua_nhan_vien ?, ?, ?, ?, ?, ?, ?";
                $params_update = [$id_nhan_vien, $ten_nhan_vien, $chuc_vu, $so_dien_thoai, $email, $ngay_bat_dau_lam, $id_chi_nhanh];
                $stmt_update = sqlsrv_query($conn, $sql_update, $params_update);

                // Kiểm tra lỗi khi thực thi câu lệnh
                if ($stmt_update === false) {
                    $errors = sqlsrv_errors();
                    $message = "Lỗi khi sửa thông tin nhân viên: " . print_r($errors, true);
                    $alert_class = "alert-error";
                } else {
                    // Lấy thông báo từ stored procedure
                    $message = "Sửa thông tin nhân viên thành công!";
                    $alert_class = "alert-success";
                }
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
    <title>Sửa Nhân Viên</title>
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
            <h2>Sửa Thông Tin Nhân Viên</h2>

            <?php if (!empty($message)): ?>
                <div class="alert <?= $alert_class ?>"><?= $message ?></div>
            <?php endif; ?>

            <!-- Form sửa nhân viên -->
            <form action="sua_nhan_vien.php?id_nhan_vien=<?= $id_nhan_vien ?>" method="POST">
                <div class="form-group">
                    <label for="ten_nhan_vien">Tên Nhân Viên:</label>
                    <input type="text" id="ten_nhan_vien" name="ten_nhan_vien" value="<?= $employee['ten_nhan_vien'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="chuc_vu">Chức Vụ:</label>
                    <input type="text" id="chuc_vu" name="chuc_vu" value="<?= $employee['chuc_vu'] ?>">
                </div>

                <div class="form-group">
                    <label for="so_dien_thoai">Số Điện Thoại:</label>
                    <input type="text" id="so_dien_thoai" name="so_dien_thoai" value="<?= $employee['so_dien_thoai'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= $employee['email'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="ngay_bat_dau_lam">Ngày Bắt Đầu Làm:</label>
                    <input type="date" id="ngay_bat_dau_lam" name="ngay_bat_dau_lam" value="<?= $ngay_bat_dau_lam ?>" required>
                </div>

                <div class="form-group">
                    <label for="id_chi_nhanh">Chi Nhánh:</label>
                    <select id="id_chi_nhanh" name="id_chi_nhanh" required>
                        <?php while ($row = sqlsrv_fetch_array($stmt_chi_nhanh, SQLSRV_FETCH_ASSOC)): ?>
                            <option value="<?= $row['id_chi_nhanh'] ?>" <?= $row['id_chi_nhanh'] == $employee['id_chi_nhanh'] ? 'selected' : '' ?>>
                                <?= $row['ten_chi_nhanh'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <input type="submit" value="Sửa Nhân Viên">
                </div>
            </form>
        </div>
    </main>
</body>
</html>

<?php
// Giải phóng tài nguyên và đóng kết nối
sqlsrv_free_stmt($stmt_chi_nhanh);
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
