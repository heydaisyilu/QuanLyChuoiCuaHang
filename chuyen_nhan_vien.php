<?php
include 'db_connect.php';

// Kiểm tra xem id_chi_nhanh_cu và id_chi_nhanh_moi có được gửi qua không
if (isset($_POST['id_chi_nhanh_cu'], $_POST['id_chi_nhanh_moi'])) {
    $id_chi_nhanh_cu = $_POST['id_chi_nhanh_cu'];
    $id_chi_nhanh_moi = $_POST['id_chi_nhanh_moi'];

    // Gọi stored procedure để chuyển nhân viên
    $sql = "EXEC sp_chuyen_nhan_vien_sang_chi_nhanh ?, ?";
    $params = array($id_chi_nhanh_cu, $id_chi_nhanh_moi);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        echo "Lỗi khi chuyển nhân viên: " . print_r(sqlsrv_errors(), true);
    } else {
        echo "Chuyển nhân viên sang chi nhánh mới thành công!";
    }

    // Giải phóng tài nguyên
    sqlsrv_free_stmt($stmt);
}

// Đóng kết nối
sqlsrv_close($conn);
?>
