<link rel="icon" href="/favicon.ico" type="image/x-icon" />
<a href="{{ route('facebook.login') }}" class="btn btn-danger">Login with facebook</a> 
<a href="{{ route('facebook.forgot.password') }}" class="btn btn-danger">Forgot password with facebook</a>

<br>
<br>
<br>

<a href="{{ route('google.login') }}" class="btn btn-danger">Login with google</a> 
<a href="{{ route('google.forgot.password') }}" class="btn btn-danger">Forgot password with google</a> 

<div id="customAlert" class="alert" style="display: none;">
    <span class="closebtn" onclick="closeAlert()">&times;</span>  
    <strong>Hello</strong> hello
</div>

<script>
    // Hiển thị hộp thoại cảnh báo nếu có session state
    //@if(session('state'))
        document.getElementById('customAlert').style.display = 'block';
    //@endif

    // Đóng hộp thoại cảnh báo khi nhấp vào nút đóng
    function closeAlert() {
        document.getElementById('customAlert').style.display = 'none';
    }
</script>

<style>
    /* Tùy chỉnh kiểu dáng của cửa sổ cảnh báo */
    .alert {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #f44336; /* Màu nền của cảnh báo */
        color: white; /* Màu văn bản của cảnh báo */
        padding: 20px; /* Khoảng cách đệm */
        z-index: 1000; /* Layer trên cùng */
    }

    /* Tùy chỉnh kiểu dáng của nút đóng */
    .closebtn {
        float: right;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
    }
</style>


