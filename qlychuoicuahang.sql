CREATE DATABASE QuanLyCuaHang;
GO
USE QuanLyCuaHang;
GO

CREATE TABLE ChiNhanh (
    id_chi_nhanh INT IDENTITY(1,1) PRIMARY KEY,  -- Tự động tăng
    ten_chi_nhanh NVARCHAR(100),
    dia_chi NVARCHAR(255),
    so_dien_thoai VARCHAR(20),
    email VARCHAR(100)
);
ALTER TABLE ChiNhanh
ADD CONSTRAINT chk_email_format1 CHECK (email LIKE '%_@__%.__%');
ALTER TABLE ChiNhanh
ADD CONSTRAINT uc1_email UNIQUE (email);

ALTER TABLE ChiNhanh
ADD CONSTRAINT chk_so_dien_thoai_format1 
CHECK (LEN(so_dien_thoai) = 10 AND so_dien_thoai LIKE '0%' AND so_dien_thoai NOT LIKE '%[^0-9]%');
ALTER TABLE ChiNhanh
ADD CONSTRAINT uc1_so_dien_thoai UNIQUE (so_dien_thoai);


INSERT INTO ChiNhanh (ten_chi_nhanh, dia_chi, so_dien_thoai, email)
VALUES ('Cửa hàng A', 'Địa chỉ A', '0123456788', 'c@store.com');


CREATE PROCEDURE sp_them_chi_nhanh
    @ten_chi_nhanh NVARCHAR(100),
    @dia_chi NVARCHAR(255),
    @so_dien_thoai VARCHAR(20),
    @email VARCHAR(100)
AS
BEGIN
    BEGIN TRY
        -- Bắt đầu một giao dịch
        BEGIN TRANSACTION;

        -- Kiểm tra định dạng số điện thoại (10 số và bắt đầu bằng 0)
        IF NOT (@so_dien_thoai LIKE '0%' AND LEN(@so_dien_thoai) = 10 AND @so_dien_thoai NOT LIKE '%[^0-9]%')
        BEGIN
            THROW 50000, 'Số điện thoại không hợp lệ. Số điện thoại phải có 10 chữ số và bắt đầu bằng số 0.', 1;
        END

        -- Kiểm tra định dạng email (phải có '@' và '.')
        IF NOT (@email LIKE '%@%' AND @email LIKE '%.%')
        BEGIN
            THROW 50001, 'Địa chỉ email không hợp lệ. Email phải chứa "@" và ".".', 1;
        END

        -- Kiểm tra nếu số điện thoại đã tồn tại
        IF EXISTS (SELECT 1 FROM ChiNhanh WHERE so_dien_thoai = @so_dien_thoai)
        BEGIN
            THROW 50002, 'Số điện thoại đã tồn tại cho chi nhánh khác!', 1;
        END

        -- Kiểm tra nếu email đã tồn tại
        IF EXISTS (SELECT 1 FROM ChiNhanh WHERE email = @email)
        BEGIN
            THROW 50003, 'Email đã tồn tại cho chi nhánh khác!', 1;
        END

        -- Thêm chi nhánh mới
        INSERT INTO ChiNhanh (ten_chi_nhanh, dia_chi, so_dien_thoai, email)
        VALUES (@ten_chi_nhanh, @dia_chi, @so_dien_thoai, @email);

        -- Hoàn tất giao dịch
        COMMIT TRANSACTION;

        -- Thông báo thành công
        PRINT 'Thêm chi nhánh thành công!';
    END TRY
    BEGIN CATCH
        -- Rollback giao dịch nếu có lỗi
        ROLLBACK TRANSACTION;

        -- Ném lỗi ra ngoài
        THROW;
    END CATCH
END;

EXEC sp_them_chi_nhanh 
    @ten_chi_nhanh = N'Chi Nhánh A',
    @dia_chi = N'123 Đường ABC',
    @so_dien_thoai = '0123456789',
    @email = 'chinhanhA@gmail.com';


CREATE PROCEDURE sp_sua_chi_nhanh
    @id_chi_nhanh INT,
    @ten_chi_nhanh NVARCHAR(100),
    @dia_chi NVARCHAR(255),
    @so_dien_thoai VARCHAR(20),
    @email VARCHAR(100)
AS
BEGIN
    -- Bắt đầu giao dịch
    BEGIN TRANSACTION;

    BEGIN TRY
        -- Kiểm tra số điện thoại đúng định dạng
        IF NOT (@so_dien_thoai LIKE '0%' AND LEN(@so_dien_thoai) = 10 AND @so_dien_thoai NOT LIKE '%[^0-9]%')
        BEGIN
            RAISERROR('Số điện thoại không hợp lệ. Số điện thoại phải có 10 chữ số và bắt đầu bằng số 0.', 16, 1);
            ROLLBACK TRANSACTION;
            RETURN;
        END

        -- Kiểm tra email đúng định dạng
        IF NOT (@email LIKE '%_@__%.__%')
        BEGIN
            RAISERROR('Email không hợp lệ. Hãy kiểm tra lại định dạng email.', 16, 1);
            ROLLBACK TRANSACTION;
            RETURN;
        END

        -- Kiểm tra nếu số điện thoại đã tồn tại cho chi nhánh khác
        IF EXISTS (SELECT 1 FROM ChiNhanh WHERE so_dien_thoai = @so_dien_thoai AND id_chi_nhanh != @id_chi_nhanh)
        BEGIN
            RAISERROR('Số điện thoại đã tồn tại cho chi nhánh khác!', 16, 1);
            ROLLBACK TRANSACTION;
            RETURN;
        END

        -- Kiểm tra nếu email đã tồn tại cho chi nhánh khác
        IF EXISTS (SELECT 1 FROM ChiNhanh WHERE email = @email AND id_chi_nhanh != @id_chi_nhanh)
        BEGIN
            RAISERROR('Email đã tồn tại cho chi nhánh khác!', 16, 1);
            ROLLBACK TRANSACTION;
            RETURN;
        END

        -- Nếu không có lỗi, tiến hành cập nhật thông tin chi nhánh
        UPDATE ChiNhanh
        SET ten_chi_nhanh = @ten_chi_nhanh,
            dia_chi = @dia_chi,
            so_dien_thoai = @so_dien_thoai,
            email = @email
        WHERE id_chi_nhanh = @id_chi_nhanh;

        -- In thông báo thành công
        PRINT 'Sửa chi nhánh thành công!';
        COMMIT TRANSACTION;
    END TRY
    BEGIN CATCH
        -- Xử lý lỗi
        ROLLBACK TRANSACTION;
        DECLARE @ErrorMessage NVARCHAR(4000), @ErrorSeverity INT, @ErrorState INT;
        SELECT @ErrorMessage = ERROR_MESSAGE(), @ErrorSeverity = ERROR_SEVERITY(), @ErrorState = ERROR_STATE();
        RAISERROR(@ErrorMessage, @ErrorSeverity, @ErrorState);
    END CATCH
END;
EXEC sp_sua_chi_nhanh 
    @id_chi_nhanh = 10, 
    @ten_chi_nhanh = N'Tên mới', 
    @dia_chi = N'Địa chỉ mới', 
    @so_dien_thoai = '0123456789', 
    @email = 'email@example.com';




CREATE PROCEDURE sp_chuyen_nhan_vien_sang_chi_nhanh
    @id_chi_nhanh_cu INT,
    @id_chi_nhanh_moi INT
AS
BEGIN
    BEGIN TRY
        -- Kiểm tra chi nhánh mới có tồn tại không
        IF NOT EXISTS (SELECT 1 FROM ChiNhanh WHERE id_chi_nhanh = @id_chi_nhanh_moi)
        BEGIN
            PRINT 'Chi nhánh mới không tồn tại.';
            RETURN;
        END

        -- Cập nhật nhân viên sang chi nhánh mới
        UPDATE NhanVien
        SET id_chi_nhanh = @id_chi_nhanh_moi
        WHERE id_chi_nhanh = @id_chi_nhanh_cu;

        PRINT 'Chuyển nhân viên sang chi nhánh mới thành công!';
    END TRY
    BEGIN CATCH
        PRINT 'Lỗi khi chuyển nhân viên sang chi nhánh mới: ' + ERROR_MESSAGE();
    END CATCH
END;

EXEC sp_chuyen_nhan_vien_sang_chi_nhanh 
    @id_chi_nhanh_cu = 10, 
    @id_chi_nhanh_moi = 9;

CREATE PROCEDURE sp_xoa_chi_nhanh
    @id_chi_nhanh INT
AS
BEGIN
    BEGIN TRY
        BEGIN TRANSACTION;

        -- Kiểm tra xem chi nhánh còn nhân viên không
        IF EXISTS (SELECT 1 FROM NhanVien WHERE id_chi_nhanh = @id_chi_nhanh)
        BEGIN
            RAISERROR ('Chi nhánh vẫn còn nhân viên. Không thể xóa!', 16, 1);
            ROLLBACK TRANSACTION;
            RETURN;
        END;

        -- Kiểm tra xem chi nhánh còn sản phẩm không
        IF EXISTS (SELECT 1 FROM ChiNhanh_SanPham WHERE id_chi_nhanh = @id_chi_nhanh)
        BEGIN
            RAISERROR ('Chi nhánh vẫn còn sản phẩm. Không thể xóa!', 16, 1);
            ROLLBACK TRANSACTION;
            RETURN;
        END;

        -- Xóa chi nhánh
        DELETE FROM ChiNhanh
        WHERE id_chi_nhanh = @id_chi_nhanh;

        PRINT 'Xóa chi nhánh thành công!';
        COMMIT TRANSACTION;
    END TRY
    BEGIN CATCH
        ROLLBACK TRANSACTION;
        PRINT 'Lỗi khi xóa chi nhánh: ' + ERROR_MESSAGE();
    END CATCH
END;


CREATE TABLE NhanVien (
    id_nhan_vien INT PRIMARY KEY IDENTITY(1,1), -- Khóa chính tự động tăng
    ten_nhan_vien NVARCHAR(100) NOT NULL,       -- Tên nhân viên, không được để trống
    chuc_vu NVARCHAR(50),                      -- Chức vụ của nhân viên (tùy chọn)
    id_chi_nhanh INT,                          -- Khóa ngoại tham chiếu đến bảng ChiNhanh
    so_dien_thoai NVARCHAR(20),                -- Số điện thoại nhân viên
    email NVARCHAR(100),                       -- Email của nhân viên
    ngay_bat_dau_lam DATE,                     -- Ngày bắt đầu làm việc
    FOREIGN KEY (id_chi_nhanh) REFERENCES ChiNhanh(id_chi_nhanh) -- Khóa ngoại
);


-- Thêm ràng buộc kiểm tra định dạng email cơ bản
ALTER TABLE NhanVien
ADD CONSTRAINT chk_email_format CHECK (email LIKE '%_@__%.__%');

-- Đảm bảo rằng email là duy nhất
ALTER TABLE NhanVien
ADD CONSTRAINT uc_email UNIQUE (email);

ALTER TABLE NhanVien
ADD CONSTRAINT chk_so_dien_thoai_format 
CHECK (LEN(so_dien_thoai) = 10 AND so_dien_thoai LIKE '0%' AND so_dien_thoai NOT LIKE '%[^0-9]%');

ALTER TABLE NhanVien
ADD CONSTRAINT uc_so_dien_thoai UNIQUE (so_dien_thoai);



CREATE PROCEDURE sp_them_nhan_vien
    @ten_nhan_vien NVARCHAR(100),
    @chuc_vu NVARCHAR(50),
    @so_dien_thoai NVARCHAR(20),
    @email NVARCHAR(100),
    @ngay_bat_dau_lam DATE,
    @id_chi_nhanh INT
AS
BEGIN
    -- Bắt đầu giao dịch
    BEGIN TRANSACTION;

    BEGIN TRY
        -- Kiểm tra chi nhánh có tồn tại không
        IF NOT EXISTS (SELECT 1 FROM ChiNhanh WHERE id_chi_nhanh = @id_chi_nhanh)
        BEGIN
            PRINT 'Chi nhánh không tồn tại!';
            ROLLBACK TRANSACTION;
            RETURN;
        END

        -- Kiểm tra số điện thoại đã tồn tại chưa
        IF EXISTS (SELECT 1 FROM NhanVien WHERE so_dien_thoai = @so_dien_thoai)
        BEGIN
            PRINT 'Số điện thoại đã tồn tại!';
            ROLLBACK TRANSACTION;
            RETURN;
        END

        -- Kiểm tra email đã tồn tại chưa
        IF EXISTS (SELECT 1 FROM NhanVien WHERE email = @email)
        BEGIN
            PRINT 'Email đã tồn tại!';
            ROLLBACK TRANSACTION;
            RETURN;
        END

        -- Thêm nhân viên vào cơ sở dữ liệu
        INSERT INTO NhanVien (ten_nhan_vien, chuc_vu, so_dien_thoai, email, ngay_bat_dau_lam, id_chi_nhanh)
        VALUES (@ten_nhan_vien, @chuc_vu, @so_dien_thoai, @email, @ngay_bat_dau_lam, @id_chi_nhanh);

        -- Commit giao dịch nếu không có lỗi
        COMMIT TRANSACTION;
        PRINT 'Thêm nhân viên thành công!';
    END TRY
    BEGIN CATCH
        -- Rollback giao dịch nếu xảy ra lỗi
        ROLLBACK TRANSACTION;
        PRINT 'Lỗi khi thêm nhân viên: ' + ERROR_MESSAGE();
    END CATCH
END;

EXEC sp_them_nhan_vien
    @ten_nhan_vien = N'Nguyễn Văn A',
    @chuc_vu = N'Nhân Viên Kinh Doanh',
    @so_dien_thoai = '0987654321',
    @email = 'nguyenvana@example.com',
    @ngay_bat_dau_lam = '2024-11-20',
    @id_chi_nhanh = 1;



CREATE PROCEDURE sp_sua_nhan_vien
    @id_nhan_vien INT,
    @ten_nhan_vien NVARCHAR(100),
    @chuc_vu NVARCHAR(50),
    @so_dien_thoai NVARCHAR(20),
    @email NVARCHAR(100),
    @ngay_bat_dau_lam DATE,
    @id_chi_nhanh INT
AS
BEGIN
    -- Bắt đầu giao dịch
    BEGIN TRANSACTION;

    BEGIN TRY
        -- Kiểm tra nếu chi nhánh tồn tại
        IF NOT EXISTS (SELECT 1 FROM ChiNhanh WHERE id_chi_nhanh = @id_chi_nhanh)
        BEGIN
            PRINT 'Chi nhánh không tồn tại!';
            ROLLBACK TRANSACTION;
            RETURN;
        END

        -- Kiểm tra nếu số điện thoại đã tồn tại cho nhân viên khác
        IF EXISTS (SELECT 1 FROM NhanVien WHERE so_dien_thoai = @so_dien_thoai AND id_nhan_vien != @id_nhan_vien)
        BEGIN
            PRINT 'Số điện thoại đã tồn tại cho nhân viên khác!';
            ROLLBACK TRANSACTION;
            RETURN;
        END

        -- Kiểm tra nếu email đã tồn tại cho nhân viên khác
        IF EXISTS (SELECT 1 FROM NhanVien WHERE email = @email AND id_nhan_vien != @id_nhan_vien)
        BEGIN
            PRINT 'Email đã tồn tại cho nhân viên khác!';
            ROLLBACK TRANSACTION;
            RETURN;
        END

        -- Cập nhật thông tin nhân viên
        UPDATE NhanVien
        SET 
            ten_nhan_vien = @ten_nhan_vien,
            chuc_vu = @chuc_vu,
            so_dien_thoai = @so_dien_thoai,
            email = @email,
            ngay_bat_dau_lam = @ngay_bat_dau_lam,
            id_chi_nhanh = @id_chi_nhanh
        WHERE id_nhan_vien = @id_nhan_vien;

        -- Commit giao dịch nếu không có lỗi
        COMMIT TRANSACTION;
        PRINT 'Sửa thông tin nhân viên thành công!';
    END TRY
    BEGIN CATCH
        -- Rollback giao dịch nếu gặp lỗi
        ROLLBACK TRANSACTION;
        PRINT 'Lỗi khi sửa thông tin nhân viên: ' + ERROR_MESSAGE();
    END CATCH
END;



EXEC sp_sua_nhan_vien
    @id_nhan_vien = 101,
    @ten_nhan_vien = N'Trần Văn B',
    @chuc_vu = N'Trưởng Phòng',
    @so_dien_thoai = '0909123456',
    @email = 'tranvanb@example.com',
    @ngay_bat_dau_lam = '2024-11-20',
    @id_chi_nhanh = 2;


CREATE PROCEDURE sp_xoa_nhan_vien
    @id_nhan_vien INT
AS
BEGIN
    BEGIN TRY
        DELETE FROM NhanVien
        WHERE id_nhan_vien = @id_nhan_vien;

        PRINT 'Xóa nhân viên thành công!';
    END TRY
    BEGIN CATCH
        PRINT 'Lỗi khi xóa nhân viên: ' + ERROR_MESSAGE();
    END CATCH
END;



CREATE TABLE SanPham (
    id_san_pham INT PRIMARY KEY IDENTITY(1,1),
    ten_san_pham NVARCHAR(100) NOT NULL,
    mo_ta NVARCHAR(255),
    gia DECIMAL(18, 2) NOT NULL,
    so_luong_ton_kho INT NOT NULL
);

ALTER TABLE SanPham
ADD id_chi_nhanh INT;

ALTER TABLE SanPham
ADD CONSTRAINT FK_SanPham_ChiNhanh FOREIGN KEY (id_chi_nhanh) REFERENCES ChiNhanh(id_chi_nhanh);

ALTER TABLE SanPham
ADD CONSTRAINT UC_SanPham_TenChiNhanh UNIQUE (ten_san_pham, id_chi_nhanh)

-- Thêm sản phẩm
INSERT INTO SanPham (ten_san_pham, mo_ta, gia, so_luong_ton_kho, id_chi_nhanh)
VALUES (N'Sản phẩm A', N'Mô tả sản phẩm A', 100.00, 50, 10);

-- Thử thêm sản phẩm có tên trùng trong cùng chi nhánh
INSERT INTO SanPham (ten_san_pham, mo_ta, gia, so_luong_ton_kho, id_chi_nhanh)
VALUES (N'Sản phẩm A', N'Mô tả sản phẩm A', 150.00, 30, 10);  -- Sẽ báo lỗi trùng tên sản phẩm trong chi nhánh

CREATE TABLE ChiNhanh_SanPham (
    id_chi_nhanh INT,
    id_san_pham INT,
    so_luong_ton_kho INT,
    PRIMARY KEY (id_chi_nhanh, id_san_pham),
    CONSTRAINT FK_ChiNhanh_SanPham_ChiNhanh FOREIGN KEY (id_chi_nhanh) REFERENCES ChiNhanh(id_chi_nhanh),
    CONSTRAINT FK_ChiNhanh_SanPham_SanPham FOREIGN KEY (id_san_pham) REFERENCES SanPham(id_san_pham)
);



CREATE PROCEDURE sp_them_san_pham
    @ten_san_pham NVARCHAR(100),
    @mo_ta NVARCHAR(255),
    @gia DECIMAL(18, 2),
    @so_luong_ton_kho INT,
    @id_chi_nhanh INT
AS
BEGIN
    -- Bắt đầu giao dịch ngay từ đầu
    BEGIN TRANSACTION;

    BEGIN TRY
        -- Kiểm tra giá phải lớn hơn hoặc bằng 0
        IF @gia < 0
        BEGIN
            THROW 50000, 'Giá sản phẩm phải lớn hơn hoặc bằng 0.', 1;
        END

        -- Kiểm tra xem tên sản phẩm có trùng trong chi nhánh không
        IF EXISTS (SELECT 1 FROM SanPham WHERE ten_san_pham = @ten_san_pham AND id_chi_nhanh = @id_chi_nhanh)
        BEGIN
            -- Nếu sản phẩm đã tồn tại, rollback giao dịch và ném lỗi
            THROW 50001, 'Tên sản phẩm đã tồn tại trong chi nhánh này.', 1;
        END

        -- Thêm sản phẩm mới
        INSERT INTO SanPham (ten_san_pham, mo_ta, gia, so_luong_ton_kho, id_chi_nhanh)
        VALUES (@ten_san_pham, @mo_ta, @gia, @so_luong_ton_kho, @id_chi_nhanh);

        -- Lấy ID của sản phẩm vừa thêm
        DECLARE @id_san_pham INT = SCOPE_IDENTITY();

        -- Liên kết sản phẩm với chi nhánh
        INSERT INTO ChiNhanh_SanPham (id_chi_nhanh, id_san_pham, so_luong_ton_kho)
        VALUES (@id_chi_nhanh, @id_san_pham, @so_luong_ton_kho);

        -- Commit giao dịch nếu mọi thứ thành công
        COMMIT TRANSACTION;
    END TRY
    BEGIN CATCH
        -- Nếu có lỗi xảy ra, rollback giao dịch và ném lỗi
        ROLLBACK TRANSACTION;
        THROW;
    END CATCH
END;



CREATE PROCEDURE sp_sua_san_pham
    @id_san_pham INT,
    @ten_san_pham NVARCHAR(100),
    @mo_ta NVARCHAR(255),
    @gia DECIMAL(18, 2),
    @so_luong_ton_kho INT,
    @id_chi_nhanh INT
AS
BEGIN
    -- Bắt đầu transaction
    BEGIN TRY
        BEGIN TRANSACTION;  -- Đảm bảo BEGIN TRANSACTION được thực thi đúng

        -- Kiểm tra giá phải lớn hơn hoặc bằng 0
        IF @gia < 0
        BEGIN
            THROW 50000, 'Giá sản phẩm phải lớn hơn hoặc bằng 0.', 1;
        END

        -- Kiểm tra xem tên sản phẩm có trùng trong chi nhánh không
        IF EXISTS (SELECT 1 FROM SanPham WHERE ten_san_pham = @ten_san_pham AND id_chi_nhanh = @id_chi_nhanh AND id_san_pham != @id_san_pham)
        BEGIN
            THROW 50001, 'Tên sản phẩm đã tồn tại trong chi nhánh này.', 1;
        END

        -- Sửa thông tin sản phẩm
        UPDATE SanPham
        SET ten_san_pham = @ten_san_pham,
            mo_ta = @mo_ta,
            gia = @gia,
            so_luong_ton_kho = @so_luong_ton_kho,
            id_chi_nhanh = @id_chi_nhanh  -- Cập nhật lại chi nhánh
        WHERE id_san_pham = @id_san_pham;

        -- Cập nhật số lượng tồn kho trong bảng ChiNhanh_SanPham (nếu cần thiết)
        UPDATE ChiNhanh_SanPham
        SET so_luong_ton_kho = @so_luong_ton_kho, id_chi_nhanh = @id_chi_nhanh
        WHERE id_san_pham = @id_san_pham;

        COMMIT TRANSACTION; -- Đảm bảo commit nếu không có lỗi
    END TRY
    BEGIN CATCH
        ROLLBACK TRANSACTION;  -- Nếu có lỗi, rollback
        THROW; -- Ném lại lỗi để xử lý ở phía PHP
    END CATCH
END;


CREATE PROCEDURE sp_xoa_san_pham
    @id_san_pham INT
AS
BEGIN
    BEGIN TRY
        BEGIN TRANSACTION;

        -- Xóa liên kết sản phẩm tại các chi nhánh
        DELETE FROM ChiNhanh_SanPham
        WHERE id_san_pham = @id_san_pham;

        -- Xóa sản phẩm
        DELETE FROM SanPham
        WHERE id_san_pham = @id_san_pham;

        COMMIT TRANSACTION;
    END TRY
    BEGIN CATCH
        ROLLBACK TRANSACTION;
        THROW;
    END CATCH
END;

