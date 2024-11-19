<?php
include 'db_connect.php';

// Lấy danh sách sản phẩm
$sql = "SELECT sp.id_san_pham, sp.ten_san_pham, sp.mo_ta, sp.gia, sp.so_luong_ton_kho, cn.ten_chi_nhanh 
        FROM SanPham sp
        JOIN ChiNhanh_SanPham cns ON sp.id_san_pham = cns.id_san_pham
        JOIN ChiNhanh cn ON cns.id_chi_nhanh = cn.id_chi_nhanh";
$stmt = sqlsrv_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Quản Lý Sản Phẩm</h1>
            <nav>
            <a href="index.php">Trang chủ</a>
            <a href="quanlychinhanh.php">Quản lý chi nhánh</a>
                <a href="quanlynhanvien.php">Quản lý nhân viên</a>
                <a href="them_san_pham.php">Thêm Sản Phẩm</a>
            </nav>
        </div>
    </header>
    <main>
        <div class="table-container">
            <h2>Danh Sách Sản Phẩm</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Mô Tả</th>
                        <th>Giá</th>
                        <th>Số Lượng</th>
                        <th>Chi Nhánh</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
                        <tr>
                            <td><?= $row['id_san_pham'] ?></td>
                            <td><?= $row['ten_san_pham'] ?></td>
                            <td><?= $row['mo_ta'] ?></td>
                            <td><?= number_format($row['gia'], 0, ',', '.') ?> VNĐ</td> <!-- Hiển thị giá có VNĐ -->
                            <td><?= $row['so_luong_ton_kho'] ?></td>
                            <td><?= $row['ten_chi_nhanh'] ?></td>
                            <td>
                                <a href="sua_san_pham.php?id=<?= $row['id_san_pham'] ?>">Sửa</a>
                                <a href="xoa_san_pham.php?id=<?= $row['id_san_pham'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">Xóa</a>
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
