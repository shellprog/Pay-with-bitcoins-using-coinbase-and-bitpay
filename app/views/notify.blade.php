@if(Session::has('success_msg'))
<div class="alert alert-success">
    <strong>Success!</strong> {{Session::get('success_msg')}}
</div>
@endif

@if(Session::has('error_msg'))
<div class="alert alert-danger">
    <strong>Error!</strong> {{Session::get('error_msg')}}
</div>
@endif
