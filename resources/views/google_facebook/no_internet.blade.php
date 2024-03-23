<link href="/css/app.css" rel="stylesheet">
@if(session('message'))
<div class="alert alert-danger">
        <p class="mb-0 text-center">{{ session('message') }}</p>
</div>
@endif
<script>
// Hàm để chuyển hướng đến 1 trang cần thiết
    setTimeout(function() {
        window.history.back();
    }, 5000);
</script>