-- Tạo cơ sở dữ liệu
CREATE DATABASE IF NOT EXISTS WEB2_BookStore;
USE WEB2_BookStore;

-- Tạo bảng LOAISACH
CREATE TABLE LOAISACH (
    loaisach_id INT PRIMARY KEY AUTO_INCREMENT,
    ten_loai VARCHAR(255),
    trang_thai VARCHAR(8) CHECK (trang_thai IN ('active', 'deleted'))
);

-- Tạo bảng SACH
CREATE TABLE SACH (
    sach_id INT PRIMARY KEY AUTO_INCREMENT,
    tieu_de VARCHAR(255),
    tac_gia VARCHAR(255),
    gia_tien DECIMAL(10,2),
    so_luong INT,
    loaisach_id INT,
    mo_ta VARCHAR(1000),
    hinh_anh VARCHAR(255),
    nha_xuat_ban VARCHAR(255),
    trang_thai VARCHAR(8) CHECK (trang_thai IN ('active', 'deleted')),
    FOREIGN KEY (loaisach_id) REFERENCES LOAISACH(loaisach_id)
);

-- Tạo bảng USER
CREATE TABLE `USER` (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    mat_khau VARCHAR(255),
    ho_ten VARCHAR(255),
    sdt VARCHAR(20),
    dia_chi VARCHAR(255),
    ngay_sinh DATETIME,
    quyen VARCHAR(20) CHECK (quyen IN ('Admin', 'KhachHang')),
    trang_thai VARCHAR(10) CHECK (trang_thai IN ('active', 'isBlocked')),
    giohang_id INT
);

-- Tạo bảng DONHANG
CREATE TABLE DONHANG (
    donhang_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    ngay_dat DATETIME,
    tong_tien DECIMAL(10,2),
    trang_thai VARCHAR(20) CHECK (trang_thai IN ('cho_xac_nhan', 'da_xac_nhan', 'da_duoc_giao', 'da_bi_huy')),
    FOREIGN KEY (user_id) REFERENCES `USER`(user_id)
);

-- Tạo bảng CHITIETDONHANG
CREATE TABLE CHITIETDONHANG (
    chitiet_id INT PRIMARY KEY AUTO_INCREMENT,
    donhang_id INT,
    sach_id INT,
    gia_tien DECIMAL(10,2),
    so_luong INT,
    FOREIGN KEY (donhang_id) REFERENCES DONHANG(donhang_id),
    FOREIGN KEY (sach_id) REFERENCES SACH(sach_id)
);

-- Tạo bảng GIOHANG (sửa đổi để loại bỏ tham chiếu DONHANG)
CREATE TABLE GIOHANG (
    giohang_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    sach_id INT,
    so_luong INT,
    FOREIGN KEY (user_id) REFERENCES `USER`(user_id),
    FOREIGN KEY (sach_id) REFERENCES SACH(sach_id)
);

-- Tạo bảng FEEDBACK
CREATE TABLE FEEDBACK (
    feedback_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    sach_id INT,
    rating INT,
    noi_dung VARCHAR(255),
    ngay_feedback DATETIME,
    FOREIGN KEY (user_id) REFERENCES `USER`(user_id),
    FOREIGN KEY (sach_id) REFERENCES SACH(sach_id)
);

-- Tạo bảng CHITIETSACH (chỉ lưu bản sao đã bán)
CREATE TABLE CHITIETSACH (
    chitietsach_id VARCHAR(255) PRIMARY KEY,
    sach_id INT,
    chitietdonhang_id INT,
    FOREIGN KEY (sach_id) REFERENCES SACH(sach_id),
    FOREIGN KEY (chitietdonhang_id) REFERENCES CHITIETDONHANG(chitiet_id)
);

-- Tạo bảng DONBAOHANH (liên kết với đơn hàng và bản sao)
CREATE TABLE DONBAOHANH (
    donbaohanh_id INT PRIMARY KEY AUTO_INCREMENT,
    donhang_id INT,
    chitietsach_id VARCHAR(255),
    ly_do VARCHAR(255),
    ngay DATE,
    trang_thai VARCHAR(20) DEFAULT 'Chua hoan thanh',
    FOREIGN KEY (donhang_id) REFERENCES DONHANG(donhang_id),
    FOREIGN KEY (chitietsach_id) REFERENCES CHITIETSACH(chitietsach_id),
    CONSTRAINT chk_trang_thai CHECK (trang_thai IN ('Chua hoan thanh', 'Hoan thanh', 'Tu choi'))
);

-- Stored procedure để tạo CHITIETSACH khi bán sách
DELIMITER //
CREATE PROCEDURE CreateChiTietSach(
    IN p_sach_id INT,
    IN p_chitietdonhang_id INT,
    IN p_so_luong INT
)
BEGIN
    DECLARE v_count INT;
    DECLARE v_index INT DEFAULT 1;
    DECLARE v_chitietsach_id VARCHAR(255);
    
    -- Lấy số bản sao hiện có để tiếp tục đánh số
    SELECT COUNT(*) INTO v_count
    FROM CHITIETSACH
    WHERE sach_id = p_sach_id;
    
    -- Tạo chitietsach_id cho từng bản sao
    WHILE v_index <= p_so_luong DO
        SET v_chitietsach_id = CONCAT('CT', LPAD(p_sach_id, 3, '0'), '_', LPAD(v_count + v_index, 2, '0'));
        INSERT INTO CHITIETSACH (chitietsach_id, sach_id, chitietdonhang_id)
        VALUES (v_chitietsach_id, p_sach_id, p_chitietdonhang_id);
        SET v_index = v_index + 1;
    END WHILE;
END //
DELIMITER ;

-- Chèn dữ liệu vào bảng LOAISACH
INSERT INTO LOAISACH (ten_loai, trang_thai) VALUES
('Văn học', 'active'), ('Kinh tế', 'active'), ('Khoa học', 'active'), ('Tiểu thuyết', 'active'), ('Lịch sử', 'active'),
('Tâm lý', 'active'), ('Kỹ thuật', 'active'), ('Truyện tranh', 'active'), ('Giáo dục', 'active');

-- Chèn dữ liệu vào bảng SACH
INSERT INTO SACH (tieu_de, tac_gia, gia_tien, so_luong, loaisach_id, mo_ta, hinh_anh, nha_xuat_ban, trang_thai) VALUES
('Blue Box tập 2', 'Kouji Miura', 250000, 50, 8, 'Blue Box - Tập 2 - Một Cô Gái Bình Thường...', '../Picture/Products/bluebox.jpg', 'NXB Kim Đồng','active'),
('BlueLock tập 24', 'Không có', 260000, 30, 8, 'BlueLock - Tập 24...', '../Picture/Products/bluelock.jpg', 'NXB Kim Đồng', 'active'),
('Bocchi The Rock tập 5', 'Không có', 180000, 40, 8, 'Truyện tranh', '../Picture/Products/bocchi.jpg', 'NXB Kim Đồng', 'active'),
('Búp Sen', 'Sơn Tùng MTP', 120000, 60, 9, 'Giáo dục', '../Picture/Products/bupsen.webp', 'NXB Trẻ', 'active'),
('Dược sư tự sự tập 11', 'Không có', 130000, 35, 8, 'Truyện tranh', '../Picture/Products/duocsu.jpg', 'NXB Kim Đồng', 'active'),
('Lũ Trẻ Đường Tàu', 'Edith Nesbit', 90000, 80, 4, 'Tiểu thuyết', '../Picture/Products/lutre.jpg', 'NXB Thanh Niên', 'active'),
('Nhà Giả Kim', 'PAULO COELHO', 170000, 45, 5, 'Lịch sử', '../Picture/Products/nhagiakim.jpg', 'NXB Giáo dục', 'active'),
('Nhật Kí Trong Tù', 'Hồ Chí Minh', 150000, 50, 5, 'Lịch sử', '../Picture/Products/nhatki.jpg', 'NXB Văn học', 'active'),
('Tăng cường khả năng học tập', 'Không có', 200000, 30, 9, 'Giáo dục', '../Picture/Products/quyluat.webp', 'NXB Giáo Dục', 'active'),
('Điều kì diệu của tiệm tạp hóa NAMIYA', 'Higashino Keigo', 180000, 40, 3, 'Tiểu thuyết', '../Picture/Products/taphoa.jpg', 'NXB Trẻ', 'active'),
('Tiền có tệ?', 'Không có', 120000, 60, 4, 'Giáo dục', '../Picture/Products/tien.webp', 'NXB Trẻ', 'active'),
('250 bài toán chọn lọc', 'Nhiều tác giả', 130000, 35, 9, 'Giáo dục', '../Picture/Products/toan.jpg', 'NXB Tâm lý', 'active'),
('Trường học biết tuốt', 'Không có', 250000, 20, 8, 'Truyện tranh', '../Picture/Products/truonghoc.webp', 'NXB Thông tin', 'active'),
('Nghệ thuật đàm phán', 'Đỗ Nam Trung', 90000, 80, 6, 'Tâm lý', '../Picture/Products/trump.jpg', 'NXB Giáo Dục', 'active'),
('Giáo dục hiện đại', 'John Dewey', 170000, 45, 9, 'Lý thuyết giáo dục', '../Picture/Products/tien.webp', 'NXB Giáo dục', 'active');

-- Chèn dữ liệu vào bảng USER
INSERT INTO `USER` (mat_khau, ho_ten, sdt, dia_chi, ngay_sinh, quyen, giohang_id, trang_thai) VALUES
('123', 'Le Van C', '0123456789', 'Ho Chi Minh', '1988-03-10', 'Admin', NULL, 'active'),
('pass123', 'Nguyen Van A', '0901234567', 'Ha Noi', '1990-05-15', 'KhachHang', 1, 'active'),
('pass456', 'Tran Thi B', '0912345678', 'Ho Chi Minh', '1992-07-20', 'KhachHang', 2, 'active'),
('pass101', 'Pham Thi D', '0934567890', 'Can Tho', '1995-09-25', 'KhachHang', 3, 'active'),
('pass202', 'Hoang Van E', '0945678901', 'Hai Phong', '1991-11-30', 'KhachHang', 4, 'active'),
('pass303', 'Do Thi F', '0956789012', 'Quang Ninh', '1987-04-05', 'KhachHang', 5, 'active'),
('pass505', 'N Thi H', '0978901234', 'Nha Trang', '1994-08-20', 'KhachHang', 6, 'active'),
('pass606', 'Dang Van I', '0989012345', 'Vung Tau', '1989-12-10', 'KhachHang', 7, 'active'),
('pass707', 'Bui Thi K', '0990123456', 'Da Lat', '1996-02-25', 'KhachHang', 8, 'active');

-- Chèn dữ liệu vào bảng GIOHANG
INSERT INTO GIOHANG (user_id, sach_id, so_luong) VALUES
(2, 1, 2),
(3, 2, 1),
(4, 3, 1),
(5, 4, 2),
(6, 5, 1),
(7, 6, 1),
(8, 7, 1),
(9, 8, 3);

-- Chèn dữ liệu vào bảng DONHANG
INSERT INTO DONHANG (user_id, ngay_dat, tong_tien, trang_thai) VALUES
(2, '2025-03-02 11:00:00', 540000, 'da_xac_nhan'), -- Mua 3 bản Bocchi
(2, '2025-03-01 10:00:00', 300000, 'cho_xac_nhan'),
(4, '2025-03-03 12:00:00', 180000, 'da_duoc_giao'),
(5, '2025-03-04 13:00:00', 240000, 'cho_xac_nhan'),
(6, '2025-03-05 14:00:00', 350000, 'da_xac_nhan'),
(7, '2025-03-06 15:00:00', 150000, 'da_bi_huy'),
(8, '2025-03-08 17:00:00', 390000, 'da_duoc_giao'),
(9, '2025-03-09 18:00:00', 200000, 'da_xac_nhan'),
(2, '2025-03-10 19:00:00', 310000, 'cho_xac_nhan');

-- Chèn dữ liệu vào bảng CHITIETDONHANG
INSERT INTO CHITIETDONHANG (donhang_id, sach_id, gia_tien, so_luong) VALUES
(1, 3, 180000, 3), -- 3 bản Bocchi
(2, 1, 150000, 2),
(3, 2, 200000, 1),
(4, 4, 120000, 2),
(5, 5, 220000, 1),
(6, 6, 130000, 1),
(7, 7, 250000, 1),
(8, 8, 90000, 3),
(9, 9, 170000, 1);

-- Tạo CHITIETSACH cho đơn hàng 1 (3 bản Bocchi)
CALL CreateChiTietSach(3, 1, 3);

-- Chèn dữ liệu vào bảng DONBAOHANH (bảo hành 1 bản Bocchi)
INSERT INTO DONBAOHANH (donhang_id, chitietsach_id, ly_do, ngay, trang_thai) VALUES
(1, 'CT003_01', 'In mờ', '2025-03-10', 'Chua hoan thanh');

-- Chèn dữ liệu vào bảng FEEDBACK
INSERT INTO FEEDBACK (user_id, sach_id, rating, noi_dung, ngay_feedback) VALUES
(1, 1, 5, 'Sách rất hay', '2025-03-01 10:00:00'),
(2, 2, 4, 'Hữu ích', '2025-03-02 11:00:00'),
(4, 3, 5, 'Tuyệt vời', '2025-03-03 12:00:00'),
(5, 4, 3, 'Bình thường', '2025-03-04 13:00:00'),
(6, 5, 4, 'Rất tốt', '2025-03-05 14:00:00'),
(7, 6, 5, 'Nội dung sâu sắc', '2025-03-06 15:00:00'),
(8, 7, 4, 'Hữu ích', '2025-03-07 16:00:00'),
(9, 8, 5, 'Hấp dẫn', '2025-03-08 17:00:00'),
(1, 9, 3, 'Tạm ổn', '2025-03-09 18:00:00');