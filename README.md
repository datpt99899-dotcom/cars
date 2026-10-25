# Car Showroom (PHP + MySQL) - Mini HTTT Kinh doanh Ô tô

Web app tối giản (đúng yêu cầu môn, chạy được thật):
- Đăng ký / Đăng nhập (tài khoản lưu DB)
- Quản lý Xe (CRUD + bộ lọc)
- Quản lý Khách hàng (CRUD)
- Giao dịch (Đặt cọc / Bán) -> cập nhật trạng thái xe + log
- Báo cáo theo tháng (số xe bán, tổng doanh thu)

> Lưu ý: Schema được thiết kế khớp với phần báo cáo CSDL mức khái niệm/logic/vật lý (Taikhoan, Nhanvien, Xe, Khachhang, Phieuban, Giaodichtaichinh, ...)

---

## 1) Cài đặt & chạy

### 1.1 Copy source vào htdocs
- XAMPP mặc định: `C:\xampp\htdocs\car_showroom`
- Nếu bạn dùng thư mục khác: đặt trong `...\htdocs\car_showroom`

### 1.2 Tạo DB + import schema
1) Mở XAMPP Control Panel → Start **Apache** và **MySQL**
2) Vào phpMyAdmin: `http://localhost/phpmyadmin`
3) Tạo database: **car_showroom** (Collation: utf8mb4_unicode_ci)
4) Import file:
- `schema.sql` (ở thư mục gốc project) **hoặc**
- `database/schema.sql`

Sau khi import xong sẽ có sẵn user mặc định:
- Username: **admin**
- Password: **123456**

### 1.3 Chạy web
Mở:
- `http://localhost/car_showroom/`

---

## 2) Gợi ý demo (để thầy xem)
1) Đăng nhập admin
2) Vào **Xe** → thêm 1 xe → để trạng thái **READY**
3) Vào **Khách hàng** → thêm 1 khách
4) Vào **Giao dịch**:
   - Đặt cọc → xe chuyển **DEPOSITED**
   - Xác nhận bán → xe chuyển **SOLD**
5) Vào **Báo cáo** → chọn tháng hiện tại để xem thống kê

---

## 3) Ghi chú bảo mật tối thiểu
- Mật khẩu lưu dưới dạng **hash** (password_hash/password_verify)
- Dùng **PDO prepared statements** chống SQL injection
- Session kiểm soát đăng nhập

