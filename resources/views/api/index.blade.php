<!-- resources/views/api/index.blade.php -->

<h1>Trạng Thái Của Các API</h1>

<ul>
    <li>API 1: {{ $api1Status ? 'Hoạt Động' : 'Lỗi' }}</li>
    <li>API 2: {{ $api2Status ? 'Hoạt Động' : 'Lỗi' }}</li>
</ul>
