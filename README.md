1. Chuẩn bị
# Hướng Dẫn Cài Đặt Laravel API Trên Docker

Hướng dẫn này sẽ giúp bạn cài đặt và chạy dự án Laravel API sử dụng Docker Compose. Hãy làm theo các bước dưới đây để thiết lập mọi thứ.

## Yêu Cầu Trước

Trước khi bắt đầu, hãy đảm bảo rằng bạn đã cài đặt các phần mềm sau trên hệ thống của mình:

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

## Cấu Trúc Dự Án

Dưới đây là cấu trúc của dự án:

├── docker-compose.yml
├── Dockerfile
├── .env
└── ... (các tệp dự án Laravel khác)

## Các Bước Thiết Lập

### 1. Clone Repository

Clone repository về máy của bạn:

```bash
git clone https://github.com/your-username/your-repo.git
cd your-repo

2. Tạo và Cấu Hình Tệp .env
Tạo tệp .env trong thư mục gốc của dự án. Bạn có thể sử dụng tệp .env.example làm mẫu:

cp .env.example .env

Chỉnh sửa tệp .env để phù hợp với cấu hình của bạn. Hãy chắc chắn thiết lập các biến sau:

DB_DATABASE
DB_USERNAME
DB_PASSWORD
DB_PASSWORD_ROOT
APP_ROOT
QUEUE_CONNECTION
QUEUE_OPTIONS
NUM_PROCS
PMA_ARBITRARY
DB_HOST

3. Build và Khởi Động Các Container
Sử dụng Docker Compose để build và khởi động các container:
docker-compose up --build -d
Lệnh này sẽ build các Docker image và khởi động các container ở chế độ nền (detached mode).

4. Truy Cập Các Dịch Vụ
Laravel API: Truy cập Laravel API tại http://localhost:8000.
phpMyAdmin: Truy cập phpMyAdmin tại http://localhost:8080. Sử dụng thông tin đăng nhập database được định nghĩa trong tệp .env.
Redis: Redis chạy trên cổng 6379.

5. Chạy Migrations và Seeders
Sau khi các container đã được khởi động, bạn cần chạy các lệnh migrations và seeders cho database:
docker-compose exec api php artisan migrate --seed

6. Quản Lý Các Container
Để xem log của một dịch vụ cụ thể, sử dụng lệnh:
docker-compose logs <service_name>
Để dừng và xóa các container, sử dụng lệnh:
docker-compose down

Thông Tin Bổ Sung
Supervisor: Container supervisor quản lý các hàng đợi Laravel và chạy các worker hàng đợi.
MariaDB: Container MariaDB hoạt động như cơ sở dữ liệu MySQL cho ứng dụng Laravel.
Nginx: Container Nginx hoạt động như máy chủ web cho ứng dụng Laravel.
Redis: Container Redis hoạt động như một kho lưu trữ dữ liệu trong bộ nhớ, được Laravel sử dụng cho caching và hàng đợi.
Khắc Phục Sự Cố
Đảm bảo Docker và Docker Compose được cài đặt và chạy đúng cách.
Kiểm tra tệp .env để đảm bảo cấu hình chính xác.
Xác minh rằng mạng Docker laravel đã được tạo và khả dụng.
Nếu bạn gặp bất kỳ vấn đề nào, hãy mở một issue trên repository hoặc liên hệ với người quản lý.

Kết Luận
Bạn đã có một API Laravel hoạt động hoàn chỉnh bên trong các container Docker. Hãy tận hưởng việc xây dựng ứng dụng của bạn!

Hãy đảm bảo rằng bạn điều chỉnh các URL, tên kho lưu trữ và các phần cụ thể của dự án cho phù hợp với dự án của bạn.

