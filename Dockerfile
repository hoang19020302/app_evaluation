FROM php:7.4-fpm

# Cài đặt các extension PHP cần thiết
RUN docker-php-ext-install pdo_mysql

# Cài đặt Nginx và các gói cần thiết
RUN apt-get update && \
    apt-get install -y nginx && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Sao chép các tệp Laravel vào container
COPY . .

# Cài đặt Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Cài đặt các gói composer
RUN composer install

# Cấu hình Nginx
COPY nginx.conf /etc/nginx/sites-available/default

# Tạo symlink để kích hoạt cấu hình Nginx
RUN rm /etc/nginx/sites-enabled/default || true  
RUN ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Mở cổng 8000 để có thể truy cập từ ngoài container
EXPOSE 8000

# Khởi động Nginx và PHP-FPM