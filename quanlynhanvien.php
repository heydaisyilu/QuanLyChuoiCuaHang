<?php
include 'db_connect.php';

// Truy vấn lấy danh sách nhân viên và chi nhánh
$sql = "SELECT nv.id_nhan_vien, nv.ten_nhan_vien, nv.chuc_vu, nv.so_dien_thoai, nv.email, nv.ngay_bat_dau_lam, cn.ten_chi_nhanh 
        FROM NhanVien nv
        JOIN ChiNhanh cn ON nv.id_chi_nhanh = cn.id_chi_nhanh";
$stmt = sqlsrv_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Nhân Viên</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Quản Lý Nhân Viên</h1>
            <nav>
            <a href="index.php">Trang chủ</a>
            <a href="quanlychinhanh.php">Quản lý chi nhánh</a>
            <a href="quanlysanpham.php">Quản lý sản phẩm</a>
                <a href="them_nhan_vien.php">Thêm nhân viên mới</a>
            </nav>
        </div>
    </header>
    <main>
        <div class="table-container">
            <h2>Danh sách nhân viên</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên nhân viên</th>
                        <th>Chức vụ</th>
                        <th>Số điện thoại</th>
                        <th>Email</th>
                        <th>Ngày bắt đầu làm</th>
                        <th>Chi nhánh</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
                        <tr>
                            <td><?= $row['id_nhan_vien'] ?></td>
                            <td><?= $row['ten_nhan_vien'] ?></td>
                            <td><?= $row['chuc_vu'] ?></td>
                            <td><?= $row['so_dien_thoai'] ?></td>
                            <td><?= $row['email'] ?></td>
                            <td><?= $row['ngay_bat_dau_lam']->format('d-m-Y') ?></td>
                            <td><?= $row['ten_chi_nhanh'] ?></td>
                            <td>
                                <a href="sua_nhan_vien.php?id_nhan_vien=<?= $row['id_nhan_vien'] ?>">Sửa</a> |
                                <a href="xoa_nhan_vien.php?id_nhan_vien=<?= $row['id_nhan_vien'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa nhân viên này?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>

<?php
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
