<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
</head>
<body>

@if(session('state'))
    <p>State: {{ session('state') }}</p>
    <p>Email: {{ session('email') }}</p>
    <p>Name: {{ session('name') }}</p>
    <p>Birthday: {{ session('birthday') }}</p>
@else
    <p>Không có giá trị state.</p>
@endif

</body>
</html>
