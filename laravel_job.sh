#!/bin/bash

# Dừng tất cả các tiến trình Laravel đang chạy
pkill -f "php artisan"
sudo service beanstalkd stop
sleep 1

# Khởi động Beanstalkd
sudo service beanstalkd start
sleep 1

# Khởi động máy chủ Laravel
php artisan serve &
sleep 1

# Khởi động các worker queue

php artisan queue:work --queue=emails &
sleep 1

php artisan queue:work --queue=login_google &
sleep 1

php artisan queue:work --queue=reser_passwd_google &
sleep 1

php artisan queue:work --queue=reset_passwd_app &




