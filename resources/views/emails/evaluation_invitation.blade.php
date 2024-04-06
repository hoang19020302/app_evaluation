<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
</head>
<body>
    <p>Xin chào bạn, </p>
    <p>
        Cảm ơn bạn đã tham gia vào 
        <strong><a href="#" style="color: inherit; text-decoration: none">tomatch.me</a></strong>
    </p>
    <p>Bạn vui lòng truy cập vào những đường link sau để tham gia các bài đánh giá:</p>
    <!-- Liên kết -->
    @foreach($linkArray as $link => $content)
        <p>
            <strong>{{ key($content) }}: </strong>
            <a href="{{ $link }}">{{ reset($content) }}</a>
            <br>
        </p>
    @endforeach
    <p>Thư được gửi lúc <strong>{{ $expirationTime }}</strong></p>
    <p>Trân trọng.</p>
</body>
</html>
