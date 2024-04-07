#!/bin/bash

# Dừng tất cả các tiến trình Laravel đang chạy
pkill -f "php artisan" 
wait

#Tạo bảng trong database
php artisan migrate:reset && \
php artisan migrate --path=/database/migrations/2024_03_20_015330_create_email_opens_table.php



