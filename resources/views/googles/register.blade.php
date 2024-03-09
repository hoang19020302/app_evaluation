<link href="/css/app.css" rel="stylesheet">
@if(session('state'))
<div class="alert {{session('success')}}" role="alert" id="custom-alert">
    <a type="button" class="close" aria-label="Close" href="{{session('url')}}">
        <span aria-hidden="true">&times;</span>
    </a>
     <h4 class="alert-heading text-center">{{session('title')}}</h4>
     <p class="mb-0 text-center">{{session('message')}}</p>
     <hr>
     <p class="mb-0 text-center">Whenever you need to, be sure to use margin utilities to keep things nice and tidy.</p>
</div>
@else
<div class="alert alert-danger" role="alert" id="custom-alert">
    <a type="button" class="close" aria-label="Close" href="{{route('welcome')}}">
        <span aria-hidden="true">&times;</span>
    </a>
     <h4 class="alert-heading text-center">Lỗi!</h4>
     <p class="mb-0 text-center">Bạn vui lòng truy cập vào <a href="{{route('welcome')}}" class="alert-link">tomatch.me</a> để tiếp tục.</p>
</div>
@endif

<script>
// Hàm để chuyển hướng đến 1 trang cần thiết
    setTimeout(function() {
        window.location.href = "{{session('url')}}";
    }, 5000);
</script>
