<!DOCTYPE html>
<html>
<head>
    <title>Tham gia bài đánh giá</title>
</head>
<body>
    <p>Xin chào, <strong>{{ $name }}</strong></p>
    <p>Cảm ơn bạn đã tham gia vào tomatch.me</p>
    <p>Bạn thuộc <strong>{{ $group }}</strong></p>
    <p>Bạn vui lòng truy cập vào đường link sau để tham gia bài đánh giá:</p>
    @if ($expirationTime->isFuture())
        <!-- Liên kết còn hiệu lực -->
        <a href="{{ $evaluationLink }}">{{ $content }}</a>
    @else
        <!-- Liên kết hỏng -->
        <a href="{{ $brokenLink }}">{{ $content }}</a>
    @endif
    <p>Link hết hạn vào lúc {{ $expirationTime }}</p>
    <p>Trân trọng.</p>
</body>
</html>