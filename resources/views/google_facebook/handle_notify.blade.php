<link href="/css/app.css" rel="stylesheet">
<link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-12">
            @if(session('state'))
                <title>{{session('title')}}</title>
                <div id="alertNotify" class="d-flex justify-content-center align-items-center""> 
                    <h3 id="alertMessage" class="text-center">{{session('title')}} | {{session('modifier')}}</h3>
                </div>
            @else
                <title>400</title>
                <div class="d-flex justify-content-center align-items-center" id="alertNotify"> 
                    <h3 class="text-center" id="alertMessage">404 | Page Not Found</h3>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
        var alertVisible = true;
        @if(session('state') && session('message') && session('title'))
            @php
                $url = session('url');
                $sessionId = session('sessionId');
                $userId = session('userId');
                $userName = session('userName');
                $fullName = session('fullName');
                $secretKey = session('secretKey');
                $message = session('message');
                $title = session('title');
            @endphp

            @if($sessionId && $userId && $url && $secretKey)
                document.cookie = "sessionId={{ $sessionId }}; domain=127.0.0.1; path=/;";
                document.cookie = "userId={{ $userId }}; domain=127.0.0.1; path=/;";
                document.cookie = "userName={{ $userName }}; domain=127.0.0.1; path=/;";
                document.cookie = "fullName={{ $fullName }}; domain=127.0.0.1; path=/;";
                document.cookie = "secretKey={{ $secretKey }}; domain=127.0.0.1; path=/;";
            @endif

            setTimeout(function() {
                if (alertVisible) {
                    alert("{{ $title }} - {{ $message }}");
                }
                alertVisible = false;
                window.location.href = "{{ $url }}";
            }, 1);
            window.addEventListener("click", function() {
                alertVisible = false;
                window.location.href = "{{ $url }}";
            });
        @else
            setTimeout(function() {
                if (alertVisible) {
                    alert("404 - Page Not Found");
                }
                alertVisible = false;
                window.location.href = "http://localhost:8000";
            }, 1);
            window.addEventListener("click", function() {
                alertVisible = false;
                window.location.href = "http://localhost:8000";
            });
        @endif
</script>