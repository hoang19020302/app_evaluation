<link href="/css/app.css" rel="stylesheet">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-12">
               @if(session('message'))
               <title>404</title>
               <div class="alert alert-danger" role="alert" id="custom-alert">
                    <h2 class="alert-heading text-center text-xl text-danger">404</h2>
                    <p class="mb-0 text-center text-xl text-danger">{{ session('message') }}</p>
               </div>
               @else
               <h3 class="alert-heading text-center text-xl text-dark">Bạn không có quyền truy cập vào trang này.</h3>
               @endif
          </div>
     </div>
</div>
<script>
     setTimeout(function() {
        window.location.href = "{{ route('welcome') }}";
     }, 3000);
</script>