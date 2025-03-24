CREATE DATABASE IF NOT EXISTS WEB2_BookStore;
USE WEB2_BookStore;

-- Tạo cấu trúc cơ sở dữ liệu
CREATE TABLE LOAISACH (
    loaisach_id INT PRIMARY KEY AUTO_INCREMENT,
    ten_loai VARCHAR(255)
);

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
    FOREIGN KEY (loaisach_id) REFERENCES LOAISACH(loaisach_id)
);

CREATE TABLE `USER` (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    mat_khau VARCHAR(255),
    ho_ten VARCHAR(255),
    sdt VARCHAR(20),
    dia_chi VARCHAR(255),
    email VARCHAR(255),
    ngay_sinh DATETIME,
    quyen VARCHAR(20) CHECK (quyen IN ('Admin', 'KhachHang')),
    giohang_id INT
);

CREATE TABLE DONHANG (
    donhang_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    ngay_dat DATETIME,
    tong_tien DECIMAL(10,2),
    trang_thai VARCHAR(20) CHECK (trang_thai IN ('cho_xac_nhan', 'da_xac_nhan', 'da_duoc_giao', 'da_bi_huy')),
    FOREIGN KEY (user_id) REFERENCES `USER`(user_id)
);

CREATE TABLE CHITIETDONHANG (
    chitiet_id INT PRIMARY KEY AUTO_INCREMENT,
    donhang_id INT,
    sach_id INT,
    gia_tien DECIMAL(10,2),
    so_luong INT,
    FOREIGN KEY (donhang_id) REFERENCES DONHANG(donhang_id),
    FOREIGN KEY (sach_id) REFERENCES SACH(sach_id)
);

CREATE TABLE GIOHANG (
    giohang_id INT PRIMARY KEY AUTO_INCREMENT,
    sach_id INT,
    donhang_id INT,
    so_luong INT,
    FOREIGN KEY (sach_id) REFERENCES SACH(sach_id),
    FOREIGN KEY (donhang_id) REFERENCES DONHANG(donhang_id)
);

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

CREATE TABLE CHITIETSACH (
    chitietsach_id VARCHAR(255) PRIMARY KEY,
    sach_id INT,
    so_luong INT,
    FOREIGN KEY (sach_id) REFERENCES SACH(sach_id)
);

CREATE TABLE DONBAOHANH (
    donbaohanh_id INT PRIMARY KEY AUTO_INCREMENT,
    ly_do VARCHAR(255),
    ngay DATE,
    trang_thai VARCHAR(20) CHECK (trang_thai IN ('Hoan thanh', 'Chua hoan thanh'))
);

CREATE TABLE CHITIET_BAOHANH (
    chitietbaohanh_id INT PRIMARY KEY AUTO_INCREMENT,
    donbaohanh_id INT,
    chitietsach_id VARCHAR(255),
    soluong_sachbaohanh INT,
    FOREIGN KEY (donbaohanh_id) REFERENCES DONBAOHANH(donbaohanh_id),
    FOREIGN KEY (chitietsach_id) REFERENCES CHITIETSACH(chitietsach_id)
);

-- Chèn dữ liệu vào bảng LOAISACH
INSERT INTO LOAISACH (ten_loai) VALUES
('Văn học'),
('Kinh tế'),
('Khoa học'),
('Tiểu thuyết'),
('Lịch sử'),
('Tâm lý'),
('Kỹ thuật'),
('Truyện tranh'),
('Giáo dục');

-- Chèn dữ liệu vào bảng SACH
INSERT INTO SACH (tieu_de, tac_gia, gia_tien, so_luong, loaisach_id, mo_ta, hinh_anh, nha_xuat_ban) VALUES
('Blue Box tập 2', 'Kouji Miura', 250000, 50, 8, 'Blue Box - Tập 2 - Một Cô Gái Bình Thường
Taiki được ghép cặp với đàn anh Haryu. Trải qua chuỗi ngày luyện tập gian khổ, cuối cùng vòng loại cấp khu vực đã bắt đầu. Đang băng băng tiến vào vòng trong, thì Taiki gặp một đối thủ đang tìm cách có được số điện thoại của chị Chinatsu! Trận đấu này cậu nhất định không được thua. Cậu phải dốc hết sức cho cả việc luyện tập lẫn tình yêu của mình.', '../Picture/Products/bluebox.jpg', 'NXB Kim Đồng'),
('BlueLock tập 24', 'Không có', 260000, 30, 8, 'BlueLock - Tập 24
CHỈ TRONG “KHOẢNH KHẮC” THÔI LÀ KHÔNG ĐỦ! “CHIẾN THẮNG HOÀN TOÀN” MỚI LÀ THỨ CẦN PHẢI THEO ĐUỔI!!
Đội Đức đã hạ gục đội Anh nhờ pha phối hợp sút bóng của Isagi và Yukimiya. Isagi đang chăm chỉ luyện tập hơn nữa để định hình lí thuyết “hoàn hảo” của mình, nhằm đánh bại Kaiser bằng bàn thắng của bản thân vào lần tới. Trái ngược hẳn với điều đó thì Hiori, người vẫn chưa được ra sân lại mang một vẻ mặt chán nản. “Quá khứ” được chôn giấu trong lòng Hiori là gì, và đâu mới là “cảm xúc thực sự” cậu dành cho bóng đá? Đối thủ trong trận đấu tiếp theo của đội Đức chính là đội bóng mà Baro đầu quân, Ý! 11 cầu thủ ra sân trong trận đấu được mọi ánh mắt đổ dồn vào sẽ là ai đây!?
BẮT ĐẦU TRẬN ĐẤU THỨ 5 VỚI UBERS! MỌI THỨ SẼ ĐƯỢC CHỨNG MINH BẰNG “BÀN THẮNG”! VÀ TÔI MỚI LÀ “TIỀN ĐẠO THỰC THỤ”!!', '../Picture/Products/bluelock.jpg', 'NXB Kim Đồng'),
('Bocchi The Rock tập 5', 'Không có', 180000, 40, 8, 'Truyện tranh', '../Picture/Products/bocchi.jpg', 'NXB Kim Đồng'),
('Búp Sen', 'Sơn Tùng MTP', 120000, 60, 9, 'Giáo dục', '../Picture/Products/bupsen.webp', 'NXB Trẻ'),
('Dược sư tự sự tập 11', 'Không có', 130000, 35, 8, 'Truyện tranh', '../Picture/Products/duocsu.jpg', 'NXB Kim Đồng'),
('Lũ Trẻ Đường Tàu', 'Edith Nesbit', 90000, 80, 4, 'Tiểu thuyết', '../Picture/Products/lutre.jpg', 'NXB Thanh Niên'),
('Nhà Giả Kim', 'PAULO COELHO', 170000, 45, 5, 'Lịch sử', '../Picture/Products/nhagiakim.jpg', 'NXB Giáo dục'),
('Nhật Kí Trong Tù', 'Hồ Chí Minh', 150000, 50, 5, 'Lịch sử', '../Picture/Products/nhatki.jpg', 'NXB Văn học'),
('Tăng cường khả năng học tập', 'Không có', 200000, 30, 9, 'Giáo dục', '../Picture/Products/quyluat.webp', 'NXB Giáo Dục'),
('Điều kì diệu của tiệm tạp hóa NAMIYA', 'Higashino Keigo', 180000, 40, 3, 'Tiểu thuyết', '../Picture/Products/taphoa.jpg', 'NXB Trẻ'),
('Tiền có tệ?', 'Không có', 120000, 60, 4, 'Giáo dục', '../Picture/Products/tien.webp', 'NXB Trẻ'),
('250 bài toán chọn lọc', 'Nhiều tác giả', 130000, 35, 9, 'Giáo dục', '../Picture/Products/toan.jpg', 'NXB Tâm lý'),
('Trường học biết tuốt', 'Không có', 250000, 20, 8, 'Truyện tranh', '../Picture/Products/truonghoc.webp', 'NXB Thông tin'),
('Nghệ thuật đàm phán', 'Đỗ Nam Trung', 90000, 80, 6, 'Tâm lý', '../Picture/Products/trump.jpg', 'NXB Giáo Dục'),
('Giáo dục hiện đại', 'John Dewey', 170000, 45, 9, 'Lý thuyết giáo dục', '../Picture/Products/tien.webp', 'NXB Giáo dục');

-- Chèn dữ liệu vào bảng USER
INSERT INTO `USER` (mat_khau, ho_ten, sdt, dia_chi, email, ngay_sinh, quyen, giohang_id) VALUES
('pass123', 'Nguyen Van A', '0901234567', 'Ha Noi', 'a.nguyen@gmail.com', '1990-05-15', 'KhachHang', 1),
('pass456', 'Tran Thi B', '0912345678', 'Ho Chi Minh', 'b.tran@gmail.com', '1992-07-20', 'KhachHang', 2),
('admin', 'Le Van C', '0923456789', 'Ho Chi Minh', 'c.le@gmail.com', '1988-03-10', 'Admin', null),
('pass101', 'Pham Thi D', '0934567890', 'Can Tho', 'd.pham@gmail.com', '1995-09-25', 'KhachHang', 4),
('pass202', 'Hoang Van E', '0945678901', 'Hai Phong', 'e.hoang@gmail.com', '1991-11-30', 'KhachHang', 5),
('pass303', 'Do Thi F', '0956789012', 'Quang Ninh', 'f.do@outlook.com', '1987-04-05', 'KhachHang', 6),
('pass505', 'N Thi H', '0978901234', 'Nha Trang', 'h.n@outlook.com', '1994-08-20', 'KhachHang', 7),
('pass606', 'Dang Van I', '0989012345', 'Vung Tau', 'i.dang@outlook.com', '1989-12-10', 'KhachHang', 8),
('pass707', 'Bui Thi K', '0990123456', 'Da Lat', 'k.bui@outlook.com', '1996-02-25', 'KhachHang', 9);

-- Chèn dữ liệu vào bảng DONHANG
INSERT INTO DONHANG (user_id, ngay_dat, tong_tien, trang_thai) VALUES
(1, '2025-03-01 10:00:00', 300000, 'cho_xac_nhan'),
(2, '2025-03-02 11:00:00', 450000, 'da_xac_nhan'),
(4, '2025-03-03 12:00:00', 180000, 'da_duoc_giao'),
(5, '2025-03-04 13:00:00', 240000, 'cho_xac_nhan'),
(6, '2025-03-05 14:00:00', 350000, 'da_xac_nhan'),
(7, '2025-03-06 15:00:00', 150000, 'da_bi_huy'),
(8, '2025-03-08 17:00:00', 390000, 'da_duoc_giao'),
(9, '2025-03-09 18:00:00', 200000, 'da_xac_nhan'),
(1, '2025-03-10 19:00:00', 310000, 'cho_xac_nhan');

-- Chèn dữ liệu vào bảng CHITIETDONHANG
INSERT INTO CHITIETDONHANG (donhang_id, sach_id, gia_tien, so_luong) VALUES
(1, 1, 150000, 2),
(2, 2, 200000, 1),
(3, 3, 180000, 1),
(4, 4, 120000, 2),
(5, 5, 220000, 1),
(6, 6, 130000, 1),
(7, 7, 250000, 1),
(8, 8, 90000, 3),
(9, 9, 170000, 1);

-- Chèn dữ liệu vào bảng GIOHANG
INSERT INTO GIOHANG (sach_id, donhang_id, so_luong) VALUES
(1, 1, 2),
(2, 2, 1),
(3, 3, 1),
(4, 4, 2),
(5, 5, 1),
(6, 6, 1),
(7, 7, 1),
(8, 8, 3),
(9, 9, 1);

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
(1, 9, 3, 'Tạm ổn', '2025-03-09 18:00:00'),
(2, 10, 4, 'Đáng đọc', '2025-03-10 19:00:00');

-- Chèn dữ liệu vào bảng CHITIETSACH
INSERT INTO CHITIETSACH (chitietsach_id, sach_id, so_luong) VALUES
('CT001', 1, 50),
('CT002', 2, 30),
('CT003', 3, 40),
('CT004', 4, 60),
('CT005', 5, 25),
('CT006', 6, 35),
('CT007', 7, 20),
('CT008', 8, 80),
('CT009', 9, 45),
('CT010', 10, 30);

-- Chèn dữ liệu vào bảng DONBAOHANH
INSERT INTO DONBAOHANH (ly_do, ngay, trang_thai) VALUES
('Sách rách', '2025-03-01', 'Chua hoan thanh'),
('In lỗi', '2025-03-02', 'Hoan thanh'),
('Giao sai', '2025-03-03', 'Chua hoan thanh'),
('Hỏng bìa', '2025-03-04', 'Hoan thanh'),
('Thiếu trang', '2025-03-05', 'Chua hoan thanh'),
('Mực lem', '2025-03-06', 'Hoan thanh'),
('Bìa rách', '2025-03-07', 'Chua hoan thanh'),
('Sai nội dung', '2025-03-08', 'Hoan thanh'),
('Hỏng gáy', '2025-03-09', 'Chua hoan thanh'),
('In mờ', '2025-03-10', 'Hoan thanh');

-- Chèn dữ liệu vào bảng CHITIET_BAOHANH
INSERT INTO CHITIET_BAOHANH (donbaohanh_id, chitietsach_id, soluong_sachbaohanh) VALUES
(1, 'CT001', 1),
(2, 'CT002', 1),
(3, 'CT003', 2),
(4, 'CT004', 1),
(5, 'CT005', 1),
(6, 'CT006', 2),
(7, 'CT007', 1),
(8, 'CT008', 3),
(9, 'CT009', 1),
(10, 'CT010', 2);