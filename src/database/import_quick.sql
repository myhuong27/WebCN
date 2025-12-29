-- IMPORT NHANH DU LIEU MAU - DA SUA CHO PHUOP HOP
-- Buoc 1: Chay cau lenh nay TRUOC (neu chua co cot loai_thu_cung):
-- ALTER TABLE bai_viet ADD COLUMN loai_thu_cung VARCHAR(50) DEFAULT 'Chung' AFTER hinh_anh;
-- ALTER TABLE video_huong_dan ADD COLUMN loai_thu_cung VARCHAR(50) DEFAULT 'Chung' AFTER hinh_anh_thumbnail;

-- Buoc 2: Sau do chay phan duoi:

-- 1. Xoa du lieu cu (neu co)
DELETE FROM bai_viet;
DELETE FROM video_huong_dan;

-- 2. Them 3 bai viet mau
INSERT INTO bai_viet (tieu_de, noi_dung, hinh_anh, loai_thu_cung, trang_thai, ngay_tao) VALUES
('10 Dieu Can Biet Khi Nuoi Cho Lan Dau', 
'Nuoi cho la mot trach nhiem lon va doi hoi su chuan bi ky luong. Ban can tim hieu ve giong cho phu hop voi khong gian song, thoi gian cham soc, va kha nang tai chinh cua minh. Cho can duoc tiem phong day du, an uong dinh duong, tap luyen deu dan va duoc kham suc khoe dinh ky. Viec huan luyen tu nho giup cho phat trien tot ve tinh cach va hanh vi. Dung quen danh thoi gian choi dua va tao moi quan he gan bo voi nguoi ban bon chan cua minh.',
'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=600',
'Cho',
1,
NOW());

INSERT INTO bai_viet (tieu_de, noi_dung, hinh_anh, loai_thu_cung, trang_thai, ngay_tao) VALUES
('Huong Dan Cham Soc Meo Con Tu 0-6 Thang Tuoi',
'Meo con trong giai doan 0-6 thang tuoi can duoc cham soc dac biet can than. Trong 4 tuan dau, meo con can duoc cho bu sua me hoac sua cong thuc chuyen dung. Tu tuan thu 4, ban co the bat dau cho meo con an thuc an mem. Tiem phong la rat quan trong - meo con can tiem vac-xin ba mui vao luc 6, 8 va 12 tuan tuoi. Tam rua nen bat dau tu 8 tuan tuoi voi dau goi chuyen dung cho meo con. Dung quen chuan bi khay ve sinh va huan luyen meo su dung tu som.',
'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=600',
'Meo',
1,
NOW());

INSERT INTO bai_viet (tieu_de, noi_dung, hinh_anh, loai_thu_cung, trang_thai, ngay_tao) VALUES
('Che Do Dinh Duong Khoa Hoc Cho Thu Cung',
'Dinh duong dong vai tro quyet dinh den suc khoe va tuoi tho cua thu cung. Cho can che do an giau protein tu thit, ca, trung ket hop voi rau cu va carbohydrate. Meo la dong vat an thit nen can luong protein cao hon, dac biet la taurine. Tranh cho thu cung an chocolate, nho, hanh toi, xuong nho co the gay nghen. Nuoc sach can duoc thay moi hang ngay. Chia nho bua an trong ngay giup he tieu hoa hoat dong tot hon. Nen tham khao y kien bac si thu y de co che do dinh duong phu hop voi tung giai doan phat trien.',
'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=600',
'Chung',
1,
NOW());

-- 3. Them 3 video mau
INSERT INTO video_huong_dan (tieu_de, mo_ta, url_video, hinh_anh_thumbnail, loai_thu_cung, thoi_luong, luot_xem, trang_thai, ngay_tao) VALUES
('Cach Tam Cho Cho Dung Cach Tai Nha',
'Video huong dan chi tiet cach tam cho cho tai nha an toan va hieu qua. Tu chuan bi dung cu, nhiet do nuoc phu hop, cach xoa dau goi, xa sach va say kho dung ky thuat de cho khong bi cam lanh.',
'https://www.youtube.com/embed/dQw4w9WgXcQ',
'https://images.unsplash.com/photo-1600369671738-ffcaf6c5f3dc?w=600',
'Cho',
'8:45',
1520,
1,
NOW());

INSERT INTO video_huong_dan (tieu_de, mo_ta, url_video, hinh_anh_thumbnail, loai_thu_cung, thoi_luong, luot_xem, trang_thai, ngay_tao) VALUES
('Huan Luyen Meo Di Ve Sinh Dung Cho',
'Huong dan huan luyen meo su dung khay ve sinh tu nhung ngay dau tien. Video bao gom: chon loai khay va cat ve sinh phu hop, dat khay o vi tri hop ly, ky thuat huan luyen tung buoc va cach xu ly khi meo khong hop tac.',
'https://www.youtube.com/embed/dQw4w9WgXcQ',
'https://images.unsplash.com/photo-1573865526739-10c1d3a1f0cc?w=600',
'Meo',
'6:30',
2340,
1,
NOW());

INSERT INTO video_huong_dan (tieu_de, mo_ta, url_video, hinh_anh_thumbnail, loai_thu_cung, thoi_luong, luot_xem, trang_thai, ngay_tao) VALUES
('Cham Soc Rang Mieng Cho Thu Cung',
'Rang mieng khoe manh la nen tang suc khoe tong the. Video huong dan cach danh rang cho cho meo, chon ban chai va kem danh rang chuyen dung, tan suat ve sinh va nhan biet cac dau hieu benh rang mieng can dua thu cung den bac si.',
'https://www.youtube.com/embed/dQw4w9WgXcQ',
'https://images.unsplash.com/photo-1568640347023-a616a30bc3bd?w=600',
'Chung',
'7:15',
980,
1,
NOW());

-- 4. Them hinh anh cho thu cung (neu can)
UPDATE thu_cung SET hinh_anh = 'https://images.unsplash.com/photo-1633722715463-d30f4f325e24?w=400' WHERE id = 1;
UPDATE thu_cung SET hinh_anh = 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?w=400' WHERE id = 2;
UPDATE thu_cung SET hinh_anh = 'https://images.unsplash.com/photo-1568572933382-74d440642117?w=400' WHERE id = 3;

-- HOAN TAT!


