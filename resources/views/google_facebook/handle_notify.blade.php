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
     <p class="mb-0 text-center">Bạn có thể tắt thông báo hoặc trang web sẽ tự chuyển hướng sau 5s.</p>
</div>
@else
<title>Lỗi!</title>
<div class="alert alert-danger" role="alert" id="custom-alert">
    <a type="button" class="close" aria-label="Close" href="http://127.0.0.1:3000/login">
        <span aria-hidden="true">&times;</span>
    </a>
     <h4 class="alert-heading text-center">Lỗi!</h4>
     <p class="mb-0 text-center">Bạn vui lòng truy cập vào <a href="http://127.0.0.1:3000/login" class="alert-link">đây</a> để tiếp tục.</p>
</div>
@endif

<script>
    @if(session('state'))
        @php
            $url = session('url');
            $sessionId = session('sessionId');
            $userId = session('userId');
        @endphp
        @if($sessionId && $userId)
            document.cookie = "sessionId={{ $sessionId }}; path=/";
            document.cookie = "userId={{ $userId }}; path=/";
        @endif
        setTimeout(function() {
            window.location.href = "{{ $url }}";//"{{ $url }}?{{ $sessionId }}&{{ $userId }}";
        }, 5000);
    @else
        setTimeout(function() {
            window.location.href = "http://127.0.0.1:3000/login";
        }, 5000);
    @endif
</script>