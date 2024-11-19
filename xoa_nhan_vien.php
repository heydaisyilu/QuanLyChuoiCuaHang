<?php
include 'db_connect.php';

if (isset($_GET['id_nhan_vien'])) {
    $id_nhan_vien = $_GET['id_nhan_vien'];

    // Xóa nhân viên khỏi cơ sở dữ liệu
    $sql = "DELETE FROM NhanVien WHERE id_nhan_vien = ?";
    $params = array($id_nhan_vien);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        $message = "Lỗi khi xóa nhân viên!";
    } else {
        $message = "Xóa nhân viên thành công!";
    }

    // Giải phóng tài nguyên
    if ($stmt !== false) {
        sqlsrv_free_stmt($stmt);
    }

    // Đóng kết nối
    sqlsrv_close($conn);

    // Chuyển hướng về trang quản lý nhân viên
    header("Location: quanlynhanvien.php?message=" . urlencode($message));
}
?>
