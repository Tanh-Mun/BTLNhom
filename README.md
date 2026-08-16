1. Đề tài của nhóm em là Hệ thống đặt lịch tư vấn / hẹn gặp giảng viên

2. Thành viên nhóm : 
Đặng Thị Ngọc Anh : Nhiệm vụ Danh sách giảng viên , hồ sơ và slot trống 
Trần Tuấn Anh : dashboard giảng viên duyệt từ chối cuộc hẹn thống kê số cuộc hẹn 
Trần Thuỳ Dung : Đặt lịch và lịch của sinh viên 
Đoàn Quang Huy : Gửi phản hồi thống kê và đánh giá 
Nguyễn Thu Quỳnh : Quản lí slot ( crud khung giờ trống của giảng viên )

3. Các đối tượng dữ liệu chính :
User (Người dùng)
Mô tả: Lưu trữ thông tin tài khoản chung của tất cả người dùng trong hệ thống (Sinh viên, Giảng viên, Admin).

LecturerProfile (Hồ sơ giảng viên)
Mô tả: Lưu thông tin bổ sung dành riêng cho vai trò Giảng viên (liên kết 1:1 với User).

Specialty / Field (Lĩnh vực tư vấn)
Mô tả: Lưu danh mục các chuyên môn, chủ đề tư vấn.

Slot (Khung giờ tư vấn)
Mô tả: Khung giờ rảnh do giảng viên khởi tạo để sinh viên đăng ký.

Appointment (Cuộc hẹn)
Mô tả: Lưu thông tin đăng ký lịch giữa sinh viên và giảng viên tại một Slot cụ thể.

Feedback / Review (Phản hồi)
Mô tả: Lưu đánh giá và phản hồi của sinh viên sau khi cuộc hẹn hoàn thành.

4. Các chức năng dự kiến 
 Hồ sơ giảng viên và lĩnh vực tư vấn.
 CRUD khung giờ tư vấn.
 Đặt/hủy/duyệt/từ chối/hoàn thành cuộc hẹn.
 Tìm theo giảng viên, đơn vị, chủ đề hoặc ngày.
 Endpoint JSON tải slot trống theo giảng viên và ngày.
 Phản hồi và thống kê số cuộc hẹn

5. Chức năng thực hiện đến buổi 2 
 tạo được giao diện cơ bản 
 Dự án đang trong giai đoạn xây dựng và phát triển

 
