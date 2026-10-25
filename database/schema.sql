CREATE DATABASE IF NOT EXISTS car_showroom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE car_showroom;
ALTER TABLE phieuban ADD COLUMN ngayban DATETIME NULL AFTER ngaytao;
CREATE TABLE IF NOT EXISTS taikhoan (
  mataikhoan INT AUTO_INCREMENT PRIMARY KEY,
  tendangnhap VARCHAR(50) NOT NULL UNIQUE,
  matkhau VARCHAR(255) NOT NULL,
  phanquyen VARCHAR(20) NOT NULL,
  trangthai TINYINT NOT NULL DEFAULT 1,
  ngaytao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS nhanvien (
  manhanvien INT AUTO_INCREMENT PRIMARY KEY,
  hoten VARCHAR(100) NOT NULL,
  ngaysinh DATE NULL,
  gioitinh VARCHAR(10) NULL,
  sodienthoai VARCHAR(15) NULL,
  email VARCHAR(100) NULL,
  diachi VARCHAR(255) NULL,
  chucvu VARCHAR(50) NULL,
  mataikhoan INT NOT NULL UNIQUE,
  CONSTRAINT fk_nhanvien_taikhoan
    FOREIGN KEY (mataikhoan) REFERENCES taikhoan(mataikhoan)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS doitac (
  madoitac INT AUTO_INCREMENT PRIMARY KEY,
  tendoitac VARCHAR(150) NOT NULL,
  loai_doitac VARCHAR(30) NOT NULL,
  sodienthoai VARCHAR(15) NULL,
  diachi VARCHAR(255) NULL,
  sotk_nganhang VARCHAR(50) NULL,
  ghichu TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS xe (
  maxe INT AUTO_INCREMENT PRIMARY KEY,
  bienso VARCHAR(20) NOT NULL UNIQUE,
  sokhung VARCHAR(50) NOT NULL UNIQUE,
  somay VARCHAR(50) NULL,
  hangxe VARCHAR(50) NOT NULL,
  dongxe VARCHAR(50) NOT NULL,
  namsanxuat YEAR NOT NULL,
  odo INT NOT NULL DEFAULT 0,
  mausac VARCHAR(30) NULL,
  xuatxu VARCHAR(30) NULL,
  tinhtrang_phaply VARCHAR(100) NULL,
  trangthai VARCHAR(20) NOT NULL DEFAULT 'NEW',
  giamua_vao DECIMAL(15,2) NOT NULL DEFAULT 0,
  tongchiphi_spa DECIMAL(15,2) NOT NULL DEFAULT 0,
  giavon DECIMAL(15,2) NOT NULL DEFAULT 0,
  giaban_dukien DECIMAL(15,2) NULL,
  mota TEXT NULL,
  hinhanh VARCHAR(255) NULL,
  ngaynhap DATE NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS khachhang (
  makhachhang INT AUTO_INCREMENT PRIMARY KEY,
  hoten VARCHAR(100) NOT NULL,
  socccd VARCHAR(20) NULL UNIQUE,
  sodienthoai VARCHAR(15) NOT NULL,
  email VARCHAR(100) NULL,
  diachi VARCHAR(255) NULL,
  nguonkhach VARCHAR(50) NULL,
  nhucau VARCHAR(255) NULL,
  ghichu TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS phieunhap (
  maphieunhap INT AUTO_INCREMENT PRIMARY KEY,
  maxe INT NOT NULL UNIQUE,
  manhanvien INT NOT NULL,
  madoitac INT NULL,
  tenchuxe VARCHAR(100) NOT NULL,
  sdt_chuxe VARCHAR(15) NOT NULL,
  giamua_chot DECIMAL(15,2) NOT NULL,
  tiencoc_mua DECIMAL(15,2) NOT NULL DEFAULT 0,
  hinhthucgiayto VARCHAR(30) NOT NULL,
  ngaycongchung DATE NULL,
  ngaynhap DATE NOT NULL,
  trangthai VARCHAR(20) NOT NULL,
  CONSTRAINT fk_phieunhap_xe
    FOREIGN KEY (maxe) REFERENCES xe(maxe)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_phieunhap_nhanvien
    FOREIGN KEY (manhanvien) REFERENCES nhanvien(manhanvien)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_phieunhap_doitac
    FOREIGN KEY (madoitac) REFERENCES doitac(madoitac)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS phieuspa (
  maphieuspa INT AUTO_INCREMENT PRIMARY KEY,
  maxe INT NOT NULL,
  madoitac INT NOT NULL,
  manhanvien INT NOT NULL,
  ngaybatdau DATE NOT NULL,
  ngayketthuc_dukien DATE NULL,
  tongchiphi_dukien DECIMAL(15,2) NULL,
  trangthai VARCHAR(20) NOT NULL,
  ghichu TEXT NULL,
  CONSTRAINT fk_phieuspa_xe
    FOREIGN KEY (maxe) REFERENCES xe(maxe)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_phieuspa_doitac
    FOREIGN KEY (madoitac) REFERENCES doitac(madoitac)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_phieuspa_nhanvien
    FOREIGN KEY (manhanvien) REFERENCES nhanvien(manhanvien)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chitietspa (
  machitietspa INT AUTO_INCREMENT PRIMARY KEY,
  maphieuspa INT NOT NULL,
  hangmuc_congviec VARCHAR(100) NOT NULL,
  chiphi_thucte DECIMAL(15,2) NOT NULL DEFAULT 0,
  ghichu TEXT NULL,
  CONSTRAINT fk_chitietspa_phieuspa
    FOREIGN KEY (maphieuspa) REFERENCES phieuspa(maphieuspa)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS phieuban (
  maphieuban INT AUTO_INCREMENT PRIMARY KEY,
  maxe INT NOT NULL UNIQUE,
  makhachhang INT NOT NULL,
  manhanvien INT NOT NULL,
  giaban_chot DECIMAL(15,2) NOT NULL,
  tiencoc_ban DECIMAL(15,2) NOT NULL DEFAULT 0,
  hinhthucthanhtoan VARCHAR(30) NOT NULL,
  nganhang_hotro INT NULL,
  trangthaivay VARCHAR(20) NULL,
  ngaytao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  trangthai_giaodich VARCHAR(20) NOT NULL,
  CONSTRAINT fk_phieuban_xe
    FOREIGN KEY (maxe) REFERENCES xe(maxe)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_phieuban_khachhang
    FOREIGN KEY (makhachhang) REFERENCES khachhang(makhachhang)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_phieuban_nhanvien
    FOREIGN KEY (manhanvien) REFERENCES nhanvien(manhanvien)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_phieuban_nganhang
    FOREIGN KEY (nganhang_hotro) REFERENCES doitac(madoitac)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS giaodichtaichinh (
  magiaodich INT AUTO_INCREMENT PRIMARY KEY,
  loaiphieu VARCHAR(10) NOT NULL,
  sotien DECIMAL(15,2) NOT NULL,
  noidung VARCHAR(255) NOT NULL,
  hinhthucthanhtoan VARCHAR(30) NOT NULL,
  ngaygiaodich DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  manhanvien INT NOT NULL,
  maphieunhap INT NULL,
  maphieuban INT NULL,
  maphieuspa INT NULL,
  CONSTRAINT fk_gd_nhanvien
    FOREIGN KEY (manhanvien) REFERENCES nhanvien(manhanvien)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_gd_phieunhap
    FOREIGN KEY (maphieunhap) REFERENCES phieunhap(maphieunhap)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_gd_phieuban
    FOREIGN KEY (maphieuban) REFERENCES phieuban(maphieuban)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_gd_phieuspa
    FOREIGN KEY (maphieuspa) REFERENCES phieuspa(maphieuspa)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bangiaoxe (
  mabanggiao INT AUTO_INCREMENT PRIMARY KEY,
  maphieuban INT NOT NULL UNIQUE,
  ngaybangiao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  noidungbangiao TEXT NOT NULL,
  ghichu TEXT NULL,
  CONSTRAINT fk_bangiaoxe_phieuban
    FOREIGN KEY (maphieuban) REFERENCES phieuban(maphieuban)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS trangthaixe_log (
  malog INT AUTO_INCREMENT PRIMARY KEY,
  maxe INT NOT NULL,
  trangthai_cu VARCHAR(20) NOT NULL,
  trangthai_moi VARCHAR(20) NOT NULL,
  manhanvien INT NOT NULL,
  thoigianthaydoi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ghichu TEXT NULL,
  CONSTRAINT fk_log_xe
    FOREIGN KEY (maxe) REFERENCES xe(maxe)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_log_nhanvien
    FOREIGN KEY (manhanvien) REFERENCES nhanvien(manhanvien)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Seed admin: admin / 123456
INSERT INTO taikhoan (tendangnhap, matkhau, phanquyen, trangthai)
SELECT 'admin', '$2y$10$9IKkccF1l1JjRvpsxkuQBu2tsimz5kG8mngDQ6tckGfN3mz0qfdqG', 'admin', 1
WHERE NOT EXISTS (SELECT 1 FROM taikhoan WHERE tendangnhap='admin');

INSERT INTO nhanvien (hoten, chucvu, mataikhoan)
SELECT 'Quản trị viên', 'Admin', t.mataikhoan
FROM taikhoan t
WHERE t.tendangnhap='admin'
  AND NOT EXISTS (SELECT 1 FROM nhanvien WHERE mataikhoan=t.mataikhoan);
