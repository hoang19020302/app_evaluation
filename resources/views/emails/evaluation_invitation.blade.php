<!DOCTYPE html>
<html>
<head>
    <title>Tham gia bài đánh giá</title>
</head>
<body>
    <p>Xin chào,</p>
    <p>Bạn vui lòng truy cập vào đường link sau để tham gia bài đánh giá:</p>
    @if ($expirationTime->isFuture())
        <!-- Liên kết còn hiệu lực -->
        <a href="{{ $evaluationLink }}">{{ $content }}</a>
    @else
        <!-- Liên kết hỏng -->
        <a href="{{ $brokenLink }}">{{ $content }}</a>
    @endif
    <p>Trân trọng.</p>
</body>
</html>
