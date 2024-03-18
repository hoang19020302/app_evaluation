<link href="/css/app.css" rel="stylesheet">
<div class="alert alert-danger" role="alert" id="custom-alert">
     <h2 class="alert-heading text-center">{{ $status }}</h2>
     <p class="mb-0 text-center">{{ $message }}</p>
</div>
<script>
     setTimeout(function() {
        window.location.href = "{{ route('welcome') }}";
     }, 5000);
</script>