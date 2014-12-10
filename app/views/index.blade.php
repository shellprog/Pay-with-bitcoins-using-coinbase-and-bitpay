<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <style type="text/css">@-ms-viewport{width: device-width;}</style>
    <title>KodeInfo - Bitcoin Payment Integration</title>
    {{HTML::style('//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css')}}
</head>

<body>

<div class="container">
    <div class='row'>
        <div class='col-md-4'></div>
        <div class='col-md-4'>
          {{Form::open(['url'=>'/process','method'=>'POST'])}}
            <div class='form-row'>

              @include('notify')

              <div class='col-xs-12 form-group'>
                <label class='control-label'>Amount</label>
                <input class='form-control' name="amount" value="{{Input::old('amount')}}" type='text'>
              </div>

            </div>

            <div class='form-row'>
              <div class='col-xs-12 form-group'>
                <label class='control-label'>Billing Name</label>
                <input class='form-control' name="name" value="{{Input::old('name')}}" type='text'>
              </div>
            </div>

            <div class='form-row'>
              <div class='form-row'>
                <div class='col-xs-6 form-group'>
                    <label class='control-label'>Billing Address</label>
                    <input class='form-control' name="address" value="{{Input::old('address')}}" type='text'>
                </div>
              </div>
              <div class='form-row'>
                <div class='col-xs-6 form-group'>
                    <label class='control-label'>Billing City</label>
                    <input class='form-control' name="city" value="{{Input::old('city')}}" type='text'>
                </div>
              </div>
            </div>

            <div class='form-row'>
                <div class='form-row'>
                    <div class='col-xs-6 form-group'>
                        <label class='control-label'>Billing State</label>
                        <input class='form-control' name="state" value="{{Input::old('state')}}" type='text'>
                    </div>
                </div>

                <div class='form-row'>
                    <div class='col-xs-6 form-group'>
                        <label class='control-label'>Billing Zip</label>
                        <input class='form-control' name="zip" value="{{Input::old('zip')}}" type='text'>
                  </div>
               </div>
            </div>

            <div class='form-row'>
              <div class='col-xs-12 form-group'>
                <label class='control-label'>Billing Email</label>
                <input class='form-control' name="email" value="{{Input::old('email')}}" type='text'>
              </div>
            </div>
            <div class='form-row'>
              <div class='col-xs-12 form-group'>
                <label class='control-label'>Billing Phone</label>
                <input class='form-control' name="phone" value="{{Input::old('phone')}}" type='text'>
              </div>
            </div>

            <div class='form-row'>
              <div class='col-xs-12 form-group'>
                <label class='control-label'>Pay with</label>
                <select class='form-control' name="type" >
                    <option value="coinbase">Coinbase</option>
                    <option value="bitpay">Bitpay</option>
                </select>
              </div>
            </div>

            <div class='form-row'>
              <div class='col-md-12 form-group'>
                <button class='form-control btn btn-primary submit-button' type='submit'>Submit »</button>
              </div>
            </div>

          {{Form::close()}}
        </div>
        <div class='col-md-4'></div>
    </div>
</div>

{{HTML::script('//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js')}}

</body>



</html>
