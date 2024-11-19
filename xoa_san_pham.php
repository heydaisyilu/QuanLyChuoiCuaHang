<?php
include 'db_connect.php';

if (isset($_GET['id'])) {
    $id_san_pham = $_GET['id'];

    // Thực thi stored procedure để xóa sản phẩm
    $sql = "EXEC sp_xoa_san_pham ?";
    $params = array($id_san_pham);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    } else {
        echo "Sản phẩm đã được xóa thành công!";
        header("Location: quanlysanpham.php"); // Điều hướng lại trang quản lý sản phẩm sau khi xóa
    }

    sqlsrv_free_stmt($stmt);
}

sqlsrv_close($conn);
?>
