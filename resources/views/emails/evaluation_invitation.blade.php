<!DOCTYPE html>
<html>
<head>
    <title>Tham gia bài test</title>
</head>
<body>
    <p>Xin chào, <strong>{{ $name }}</strong></p>
    <p>Cảm ơn bạn đã tham gia vào tomatch.me</p>
    <p>Bạn vui lòng truy cập vào đường link sau để tham gia bài đánh giá:</p>
    <!-- Liên kết -->
    <a href="{{ $evaluationLink }}">{{ $content }}</a>
    <p>Bạn nên truy cập vào đường link trước {{ $expirationTime }}</p>
    <p>Trân trọng.</p>
</body>
</html>
