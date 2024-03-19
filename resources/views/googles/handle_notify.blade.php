<link href="/css/app.css" rel="stylesheet">
@if(session('state'))
<title>{{session('title')}}</title>
<div class="alert {{session('modifier')}}" role="alert" id="custom-alert">
    <a type="button" class="close" aria-label="Close" href="{{session('url')}}">
        <span aria-hidden="true">&times;</span>
    </a>
     <h4 class="alert-heading text-center">{{session('title')}}</h4>
     <p class="mb-0 text-center">{{session('message')}}</p>
     <hr>
     <p class="mb-0 text-center">Whenever you need to, be sure to use margin utilities to keep things nice and tidy.</p>
</div>
@else
<title>Lỗi!</title>
<div class="alert alert-danger" role="alert" id="custom-alert">
    <a type="button" class="close" aria-label="Close" href="http://localhost:3000">
        <span aria-hidden="true">&times;</span>
    </a>
     <h4 class="alert-heading text-center">Lỗi!</h4>
     <p class="mb-0 text-center">Bạn vui lòng truy cập vào <a href="http://localhost:3000" class="alert-link">tomatch.me</a> để tiếp tục.</p>
</div>
@endif

<script>
// Hàm để chuyển hướng đến 1 trang cần thiết
    @if(session('state'))
        @php
            $url = session('url');
            $sessionId = session('sessionId');
        @endphp
        
        @if($sessionId)
            document.cookie = "sessionId={{ $sessionId }}; path=/";
        @endif
        setTimeout(function() {
            window.location.href = "{{ $url }}";
        }, 5000);
    @else
        setTimeout(function() {
            window.location.href = "http://localhost:3000";
        }, 5000);
    @endif
</script>
