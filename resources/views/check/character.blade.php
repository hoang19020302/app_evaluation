<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

<form style="margin-left: 10px" method="POST" action="/check-info" id="loginForm" class="mt-4">
    @csrf
    <h3>Kiểm tra thông tin</h3>
    <div class="form-group mt-4">
        <label for="exampleInputEmail1">Email address</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email">
    </div>
    <div class="form-group mt-4">
        <label for="exampleInputPassword1">Password</label>
        <input type="password" class="form-control" name="password" id="password" placeholder="Password">
    </div>
    <div class="form-group mt-4">
        <label for="exampleInputClassify1">Classify</label>
        <input type="text" class="form-control" name="classify" id="classify" placeholder="Classify" value="character" readonly>
    </div>
    <button type="submit" id="submitBtn" class="btn btn-primary">Kiểm tra</button>
</form>
