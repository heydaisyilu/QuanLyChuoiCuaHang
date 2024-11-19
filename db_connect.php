<?php
$serverName = "DAISY\SQLEXPRESS01"; // Hoặc tên server của bạn
$connectionOptions = array(
    "Database" => "QuanLyCuaHang", // Tên cơ sở dữ liệu của bạn
    "Uid" => "sa", // Tên người dùng
    "PWD" => "123456789", // Mật khẩu
    "CharacterSet" => "UTF-8"  // Đảm bảo mã hóa là UTF-8
);

// Kết nối với SQL Server
$conn = sqlsrv_connect($serverName, $connectionOptions);

// Kiểm tra kết nối
if( !$conn ) {
    die( print_r(sqlsrv_errors(), true)); // Nếu không kết nối được, in lỗi
}
?>
