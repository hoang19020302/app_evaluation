<link href="/css/app.css" rel="stylesheet">
<link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-12">
            @if( $state )
                <title>{{ $title }}</title>
                <div class="alert alert-{{$modifier}} mt-3" role="alert" id="custom-alert">
                    <a type="button" class="close" aria-label="Close" href="{{ $url }}">
                        <span aria-hidden="true">&times;</span>
                    </a>
                    <h4 class="alert-heading text-center text-xl text-{{$modifier}}">{{$title}}</h4>
                    <p class="mb-0 text-center text-xl text-{{$modifier}}">{{$message}}</p>
                    <hr>
                    <p class="mb-0 text-center text-xl text-secondary">Bạn có thể tắt thông báo hoặc trang web sẽ tự chuyển hướng sau 3s.</p>
                </div>
            @else
                <title>Lỗi!</title>
                    <div class="alert alert-danger" role="alert" id="custom-alert">
                    <a type="button" class="close" aria-label="Close" href="http://127.0.0.1:3000/login">
                        <span aria-hidden="true">&times;</span>
                    </a>
                    <h4 class="alert-heading text-center text-xl text-danger">404</h4>
                    <p class="mb-0 text-center text-xl text-danger">Bạn vui lòng truy cập vào <a href="http://127.0.0.1:3000/login" class="alert-link">đây</a> để tiếp tục.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    @if( $state )
        
        @if($sessionId && $userId)
            document.cookie = "sessionId={{ $sessionId }}; path=/";
            document.cookie = "userId={{ $userId }}; path=/";
        @endif
        //setTimeout(function() {
            //window.location.href = "{{ $url }}";//"{{ $url }}?{{ $sessionId }}&{{ $userId }}";
        //}, 3000);
    @else
        setTimeout(function() {
            //window.location.href = "http://127.0.0.1:3000/login";
        }, 3000);
    @endif

</script>