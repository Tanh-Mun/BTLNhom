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

 6. Các chức năng nhóm đã cập nhật được sau buổi 2
  - Màn hình & Form chính
    Form tạo cuộc hẹn mới: Tách riêng 3 trường nhập liệu gồm Tên sinh viên (Text), Ngày hẹn (Date), và Giờ hẹn (Time).

    Bảng thống kê tổng quan: Hiển thị 5 chỉ số theo thời gian thực (Tổng số cuộc hẹn, Chờ duyệt, Đã duyệt, Hoàn thành, Từ chối).

    Danh sách cuộc hẹn: Dạng bảng chi tiết hiển thị mã ID, tên sinh viên, thời gian hẹn, trạng thái badge màu sắc và các nút thao tác tương ứng (Duyệt, Từ chối, Đánh dấu xong).

    Bảng tổng hợp theo sinh viên: Thống kê tần suất đặt lịch, số lần hoàn thành và thời điểm cuộc hẹn mới nhất của từng sinh viên (GROUP BY student_name).

    - Quy tắc Validation & Nghiệp vụ
    Kiểm tra dữ liệu bắt buộc (Required): Không cho phép gửi form rỗng.

    Độ dài chuỗi: Trường Tên sinh viên bắt buộc từ 2 đến 100 ký tự.

    Định dạng dữ liệu:

    Ngày hẹn đúng định dạng YYYY-MM-DD.

    Giờ hẹn đúng định dạng HH:MM.

    Thông báo lỗi tức thì: Lỗi được gán và hiển thị ngay bên dưới từng trường nhập liệu tương ứng.

    Giữ lại dữ liệu cũ (Sticky Form): Khi form bị lỗi, các dữ liệu đúng người dùng đã nhập trước đó không bị mất đi.

    - An toàn thông tin & Chuẩn hóa
    Chống XSS: Tất cả dữ liệu đầu ra hiển thị lên giao diện HTML đều được bọc qua hàm htmlspecialchars().

    Chuẩn hóa chuỗi: Sử dụng trim() loại bỏ khoảng trắng thừa đầu/cuối trước khi xử lý.

    Chống SQL Injection: Sử dụng PDO Prepared Statements ($pdo->prepare()) cho toàn bộ các truy vấn cơ sở dữ liệu.

    - Xử lý Route & Điều hướng (Request Handling)
    POST / (Tạo mới): Xử lý gửi form thêm cuộc hẹn, ghép Ngày + Giờ thành kiểu DATETIME chuẩn của SQL Server.

    GET /?action=approve&id=X: Chuyển trạng thái sang approved.

    GET /?action=reject&id=X: Chuyển trạng thái sang rejected.

   GET /?action=complete&id=X: Chuyển trạng thái sang completed.

   Chuyển hướng (PRG Pattern): Sử dụng header("Location: ...") sau khi thực hiện action để tránh trùng lặp dữ liệu khi người dùng ấn F5.

 7. 🗄️ Cơ Sở Dữ Liệu (Database)

Hệ thống sử dụng cơ sở dữ liệu **Microsoft SQL Server** với tên CSDL là appointment_system. 

 - 1. Mô tả chi tiết các bảng (Database Schema)

Hệ thống bao gồm 8 bảng chính để quản lý người dùng, lịch hẹn và đánh giá:

  * roles: Quản lý vai trò người dùng (VD: Sinh viên, Giảng viên, Admin).
  * id (PK, INT, IDENTITY): Mã vai trò.
  * name (NVARCHAR(50), UNIQUE): Tên vai trò.

  *users: Quản lý thông tin tài khoản người dùng trong hệ thống.
  * id (PK, INT, IDENTITY): Mã người dùng.
  * username (VARCHAR(50), UNIQUE): Tên đăng nhập.
  * password (VARCHAR(255)): Mật khẩu đã mã hóa.
  * full_name (NVARCHAR(100)): Họ và tên.
  * email (VARCHAR(100), UNIQUE): Địa chỉ email.
  * role_id (FK): Khóa ngoại liên kết với bảng roles.
  * created_at (DATETIME): Thời gian tạo tài khoản (mặc định: thời gian hiện tại).

  *lecturer_profiles: Thông tin hồ sơ chi tiết của Giảng viên.
  * user_id (PK, FK): Mã giảng viên (liên kết với users.id, xóa tự động theo CASCADE).
  * department (NVARCHAR(100)): Khoa/Bộ môn.
  * bio (NVARCHAR(MAX)): Tiểu sử / Giới thiệu bản thân.

  *specialties: Danh mục chuyên môn/lĩnh vực tư vấn của Giảng viên.
  * id (PK, INT, IDENTITY): Mã chuyên môn.
  * name (NVARCHAR(100), UNIQUE): Tên chuyên môn/lĩnh vực.

  * lecturer_specialties: Bảng trung gian thể hiện quan hệ nhiều - nhiều giữa Giảng viên và Chuyên môn.
  * lecturer_id (PK, FK): Mã giảng viên (liên kết users.id).
  * specialty_id (PK, FK): Mã chuyên môn (liên kết specialties.id, xóa tự động theo CASCADE).

  *slots: Quản lý các khung giờ rảnh mà Giảng viên mở để sinh viên đăng ký.
  * id (PK, INT, IDENTITY): Mã khung giờ.
  * lecturer_id (FK): Mã giảng viên tạo slot (liên kết users.id, xóa tự động theo CASCADE).
  * start_time (DATETIME): Thời gian bắt đầu.
  * end_time (DATETIME): Thời gian kết thúc.
  * status (VARCHAR(20)): Trạng thái slot (available, booked - Mặc định: available).

  *appointments: Quản lý các cuộc hẹn do Sinh viên đặt.
  * id (PK, INT, IDENTITY): Mã cuộc hẹn.
  * slot_id (FK): Mã khung giờ (liên kết slots.id).
  * student_id` (FK): Mã sinh viên đặt lịch (liên kết users.id).
  * topic (NVARCHAR(MAX)): Chủ đề/Nội dung trao đổi.
  * status (VARCHAR(20)): Trạng thái lịch hẹn (pending, approved, rejected, completed, cancelled - Mặc định: pending).
  * created_at (DATETIME): Thời gian tạo lịch hẹn.
  * Ràng buộc: Ràng buộc UNIQUE unique_active_slot trên (slot_id, status).

  *feedbacks`**: Quản lý phản hồi/đánh giá của Sinh viên sau cuộc hẹn.
  * id (PK, INT, IDENTITY): Mã phản hồi.
  * appointment_id (FK, UNIQUE): Mã cuộc hẹn (liên kết appointments.id, mỗi cuộc hẹn chỉ có 1 phản hồi, xóa tự động theo CASCADE).
  * rating (TINYINT): Điểm đánh giá (từ 1 đến 5).
  * comment (NVARCHAR(MAX)): Nhận xét/Góp ý.
  * created_at (DATETIME): Thời gian tạo đánh giá.

- 2. Sơ đồ Quan hệ Thực thể (ERD)

![Sơ đồ ERD](./docs/erd-diagram.png) *(Thay đổi đường dẫn tới ảnh ERD của bạn)*

mermaid
erDiagram
    roles ||--o{ users : "role_id"
    users ||--o| lecturer_profiles : "user_id"
    users ||--o{ lecturer_specialties : "lecturer_id"
    specialties ||--o{ lecturer_specialties : "specialty_id"
    users ||--o{ slots : "lecturer_id"
    users ||--o{ appointments : "student_id"
    slots ||--o{ appointments : "slot_id"
    appointments ||--o| feedbacks : "appointment_id"
