#!/bin/bash

# Dừng tất cả các tiến trình Laravel đang chạy
pkill -f "php artisan" 
wait

# Khởi động Beanstalkd
sudo service beanstalkd restart 
wait

# Khởi động server laravel và các worker queue
php artisan serve &
php artisan queue:work --queue=emails_1 & 
php artisan queue:work --queue=emails_2 




