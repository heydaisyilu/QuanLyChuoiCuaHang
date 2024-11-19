<?php
include 'db_connect.php';

// Lấy danh sách các chi nhánh từ cơ sở dữ liệu
$sql = "SELECT * FROM ChiNhanh";
$stmt_chi_nhanh = sqlsrv_query($conn, $sql);

// Kiểm tra kết nối cơ sở dữ liệu
if ($stmt_chi_nhanh === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>
<?php
sqlsrv_free_stmt($stmt_chi_nhanh);
sqlsrv_close($conn);
?>
