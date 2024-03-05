<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
</head>
<body>

@if(session('state'))
    <p>State: {{ session('state') }}</p>
@else
    <p>Không có giá trị state.</p>
@endif

</body>
</html>
