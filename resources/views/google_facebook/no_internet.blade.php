<link href="/css/app.css" rel="stylesheet">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-12">
            @if(session('message'))
                <div class="alert alert-danger">
                    <p class="mb-0 text-center text-xl text-danger">{{ session('message') }}</p>
                </div>
            @else
                <h3 class="alert-heading text-center text-xl text-dark">Bạn không có quyền truy cập vào trang này.</h3>
            @endif
        </div>
    </div>
</div>
<script>
// Hàm để chuyển hướng đến 1 trang cần thiết
    setTimeout(function() {
        window.history.back();
    }, 3000);
</script>