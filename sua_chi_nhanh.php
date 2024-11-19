<?php
include 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Lấy thông tin chi nhánh cần sửa từ URL
$id_chi_nhanh = $_GET['id_chi_nhanh'];

// Truy vấn thông tin chi nhánh từ cơ sở dữ liệu
$sql = "SELECT * FROM ChiNhanh WHERE id_chi_nhanh = ?";
$params = [$id_chi_nhanh];
$stmt = sqlsrv_query($conn, $sql, $params);

// Kiểm tra xem có dữ liệu trả về không
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$branch = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$branch) {
    die("Không tìm thấy chi nhánh với ID: " . $id_chi_nhanh);
}

// Lấy tất cả chi nhánh
$sql_chi_nhanh_list = "SELECT * FROM ChiNhanh";
$stmt_chi_nhanh_list = sqlsrv_query($conn, $sql_chi_nhanh_list);

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $ten_chi_nhanh = $_POST['ten_chi_nhanh'];
    $dia_chi = $_POST['dia_chi'];
    $so_dien_thoai = $_POST['so_dien_thoai'];
    $email = $_POST['email'];

    // Kiểm tra định dạng số điện thoại
    if (!preg_match('/^0\d{9}$/', $so_dien_thoai)) {
        $message = "Số điện thoại không hợp lệ. Số điện thoại phải có 10 chữ số và bắt đầu bằng số 0.";
        $alert_class = "alert-error";
    } 
    // Kiểm tra định dạng email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email không hợp lệ. Hãy kiểm tra lại.";
        $alert_class = "alert-error";
    } else {
        // Kiểm tra số điện thoại đã tồn tại chưa (ngoại trừ chi nhánh hiện tại)
        $sql_check_phone = "SELECT * FROM ChiNhanh WHERE so_dien_thoai = ? AND id_chi_nhanh != ?";
        $params_check_phone = [$so_dien_thoai, $id_chi_nhanh];
        $stmt_check_phone = sqlsrv_query($conn, $sql_check_phone, $params_check_phone);

        if (sqlsrv_has_rows($stmt_check_phone)) {
            $message = "Số điện thoại đã tồn tại cho chi nhánh khác!";
            $alert_class = "alert-error";
        } else {
            // Kiểm tra email đã tồn tại chưa (ngoại trừ chi nhánh hiện tại)
            $sql_check_email = "SELECT * FROM ChiNhanh WHERE email = ? AND id_chi_nhanh != ?";
            $params_check_email = [$email, $id_chi_nhanh];
            $stmt_check_email = sqlsrv_query($conn, $sql_check_email, $params_check_email);

            if (sqlsrv_has_rows($stmt_check_email)) {
                $message = "Email đã tồn tại cho chi nhánh khác!";
                $alert_class = "alert-error";
            } else {
                // Thực thi stored procedure để sửa chi nhánh
                $sql_update = "EXEC sp_sua_chi_nhanh ?, ?, ?, ?, ?";
                $params_update = [$id_chi_nhanh, $ten_chi_nhanh, $dia_chi, $so_dien_thoai, $email];
                $stmt_update = sqlsrv_query($conn, $sql_update, $params_update);

                // Kiểm tra lỗi khi thực thi câu lệnh
                if ($stmt_update === false) {
                    $errors = sqlsrv_errors();
                    $message = "Lỗi khi sửa chi nhánh: " . print_r($errors, true);
                    $alert_class = "alert-error";
                } else {
                    // Thông báo thành công
                    $message = "Sửa chi nhánh thành công!";
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
    <title>Sửa Chi Nhánh</title>
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
            <h2>Sửa Thông Tin Chi Nhánh</h2>

            <?php if (!empty($message)): ?>
                <div class="alert <?= $alert_class ?>"><?= $message ?></div>
            <?php endif; ?>

            <!-- Form sửa chi nhánh -->
            <form action="sua_chi_nhanh.php?id_chi_nhanh=<?= $id_chi_nhanh ?>" method="POST">
                <div class="form-group">
                    <label for="ten_chi_nhanh">Tên Chi Nhánh:</label>
                    <input type="text" id="ten_chi_nhanh" name="ten_chi_nhanh" value="<?= htmlspecialchars($branch['ten_chi_nhanh']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="dia_chi">Địa Chỉ:</label>
                    <input type="text" id="dia_chi" name="dia_chi" value="<?= htmlspecialchars($branch['dia_chi']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="so_dien_thoai">Số Điện Thoại:</label>
                    <input type="text" id="so_dien_thoai" name="so_dien_thoai" value="<?= htmlspecialchars($branch['so_dien_thoai']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($branch['email']) ?>" required>
                </div>

                <div class="form-group">
                    <input type="submit" value="Sửa Chi Nhánh">
                </div>
            </form>
        </div>
    </main>
</body>
</html>

<?php
// Giải phóng tài nguyên và đóng kết nối
sqlsrv_free_stmt($stmt_chi_nhanh_list);
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
