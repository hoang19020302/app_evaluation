# Hướng Dẫn Cài Đặt Laravel API

Hướng dẫn này giúp cài đặt và chạy dự án Laravel với các dịch vụ kèm theo sử dụng Docker Compose. Làm theo các bước dưới đây để thiết lập mọi thứ.

## Yêu Cầu Bắt Buộc

Trước khi bắt đầu, hãy đảm bảo rằng đã cài đặt các phần mềm sau trên hệ thống của mình:

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

## Cấu Trúc Dự Án

Dưới đây là cấu trúc của dự án:
```bash
├── docker-compose
    ├── mysql
    ├── nginx
    ├── redis
    └── ... (các cấu hình dịch vụ khác)   
├── docker-compose.yml
├── Dockerfile
├── .env
├── apache.conf
└── ... (các tệp dự án Laravel và các file khác)
```

## Các Bước Thiết Lập
### 1. Clone Repository
Clone repository về máy (hoặc sử dụng FileZila để copy toàn bộ mã sang):

```bash
git clone -b laravel/7.4 git@github.com:duchieu1279/tomatch.me.git
mv tomatch.me/ speci.me
cd speci.me
```
2. Tạo file cấu hình .env
Tạo tệp .env trong thư mục gốc của dự án. Bạn có thể sử dụng tệp .env.example làm mẫu:
```bash
cp .env.example .env
```
Chỉnh sửa tệp .env để phù hợp với cấu hình của bạn (hoặc giữ nguyên mọi thứ)

3. Build và Khởi Động Các Container
Đầu tiên chạy lệnh sau để build image speci/backend:
```bash
docker-compose build api
```
```bash
Output
Building api
[+] Building 14.2s (16/16) FINISHED                                                                      docker:default
 => [internal] load build definition from Dockerfile                                                               0.0s
 => => transferring dockerfile: 1.10kB                                                                             0.0s
 => [internal] load metadata for docker.io/library/php:7.4-fpm                                                     2.0s
 => [internal] load metadata for docker.io/library/composer:latest                                                 3.1s
 => [auth] library/composer:pull token for registry-1.docker.io                                                    0.0s
 => [auth] library/php:pull token for registry-1.docker.io                                                         0.0s
 => [internal] load .dockerignore                                                                                  0.0s
 => => transferring context: 305B                                                                                  0.0s
 => [stage-0 1/8] FROM docker.io/library/php:7.4-fpm@sha256:3ac7c8c74b2b047c7cb273469d74fc0d59b857aa44043e6ea6a00  0.0s
 => FROM docker.io/library/composer:latest@sha256:4c01e0b94ce1cf09a1f0850187231f48942dab2ab25f5bb0a34dfd6271c289  10.9s
.......................................................................................................................
=> CACHED [stage-0 8/8] WORKDIR /var/www                                                                           0.0s
 => exporting to image                                                                                             0.0s
 => => exporting layers                                                                                            0.0s
 => => writing image sha256:22f830af35cffceb04add31b025135889458f9028dcd3d391880b0f79ae049de                       0.0s
 => => naming to docker.io/speci/backend
```
Sau đó khởi động các container ở chế độ nền (detached mode)
```bash
docker-compose up -d
```

4. Cài đặt các phụ thụ thuộc của dự án và tối ưu ứng dụng
Để hiển thị thông tin về trạng thái dịch vụ đang hoạt động của bạn, hãy chạy:
```bash
docker-compose ps
```
Chạy lệnh sau để tạo ra thư mục vendor cho dự án:
```bash
docker-compose exec api composer install --optimize-autoloader --no-dev
```
Tạo 1 khoá duy nhất cho ứng dụng:
```bash
docker-compose exec api php artisan key:generate
```
Chạy lần lượt các lệnh sau để tối ưu hiệu năng ứng dụng(lênh php artisan storage:link chỉ chạy duy nhất 1 lần):
```bash
docker-compose exec api php artisan config:cache
docker-compose exec api php artisan event:cache
docker-compose exec api php artisan route:cache
docker-compose exec api php artisan view:cache
docker-compose exec api php artisan optimize
docker-compose exec api php artisan storage:link
```

5. Truy Cập Các Dịch Vụ
- Laravel API chạy trên cổng 8000 (truy cập: http://<ip_server>:8000)
- PhpMyAdmin chạy trên cổng 8080 (truy cập: http://<ip_server>:8080)
- Portainer chạy trên cổng 9001 (truy cập: http://<ip_server>:9001)
- Redis chạy trên cổng 6379 
 - Có thể sử dụng lênh sau để truy cập: 
 ```bash
 docker-compose exec <container_name> redis-cli
 ```
Nếu muốn tạm dừng các container trong khi vẫn giữ nguyên trạng thái, dùng lệnh:
```bash
docker-compose pause
```
Sau đó có thể tiếp tục với:
```bash
docker-compose unpause
```
Để tắt môi trường docker-compose chạy lệnh:
```bash
docker-compose down
```
6. Quản Lý Các Container
Quản lý thông qua giao diện của Portainer
Để xem đc nhật ký của dich vụ, hãy chạy:
```bash
docker-compose logs <service_name>
```

7. Thông Tin Bổ Sung
- Supervisor: Container supervisor quản lý các hàng đợi Laravel, tự động chạy các worker và ghi thông tin vào file worker.log.
- MariaDB: Container MariaDB hoạt động như cơ sở dữ liệu MySQL cho ứng dụng Laravel.
- Nginx: Container Nginx hoạt động như máy chủ web cho ứng dụng Laravel để phân tích mã PHP.
- Redis: Container Redis hoạt động như một kho lưu trữ dữ liệu trong bộ nhớ, được Laravel sử dụng cho caching, session và hàng đợi.
- PhpMyAdmin: Container PhpMyAdmin cung cấp giao diện để tương tác đc với database MySQL.
- Portainer: Container Portainer cung cấp giao diện để quản lý các container đang chạy.
8. Khắc Phục Sự Cố
Đảm bảo Docker và Docker Compose được cài đặt và chạy đúng cách.
Kiểm tra tệp .env để đảm bảo cấu hình chính xác.
Xác minh rằng mạng Docker laravel đã được tạo và khả dụng.
Nếu bạn gặp bất kỳ vấn đề nào, hãy mở một issue trên repository hoặc liên hệ với người quản lý.

## Kết Luận

