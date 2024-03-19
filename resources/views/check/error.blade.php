<link href="/css/app.css" rel="stylesheet">
@if(session('message'))
<title>404</title>
<div class="alert alert-danger" role="alert" id="custom-alert">
     <h2 class="alert-heading text-center">404</h2>
     <p class="mb-0 text-center">{{ session('message') }}</p>
</div>
@else
<h3>Bạn không có quyền truy cập vào trang này.</h3>
@endif
<script>
     setTimeout(function() {
        window.location.href = "{{ route('welcome') }}";
     }, 5000);
</script>