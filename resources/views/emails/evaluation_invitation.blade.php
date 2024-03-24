<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
</head>
<body>
    <p>Xin chào bạn, </p>
    <p>Cảm ơn bạn đã tham gia vào tomatch.me</p>
    <p>Bạn vui lòng truy cập vào đường link sau để tham gia bài đánh giá:</p>
    <!-- Liên kết -->
    <a href="{{ $evaluationLink }}">{{ $content }}</a>
    <p>Thư được gửi lúc {{ $expirationTime }}</p>
    <p>Trân trọng.</p>
</body>
</html>
