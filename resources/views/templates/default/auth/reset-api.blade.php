@extends($templatePath.'.api_layout')

@section('main')
<div class="container">
    <p class="row"></p>
    <h2 class="title text-center">Forgot password</h2>
    <div class="row justify-content-center">
        <div class="col-md-2">
        </div>
        <div class="col-md-8">
            <div class="card">

                <div class="card-body">
                    @if($valide_msg||$errors->has('valide_msg') )

                    <div class="alert alert-{{ $valide ? 'success' : 'danger' }}">
                        @if ($errors->has('valide_msg'))
                        {{$errors->first('valide_msg')}}
                        @else 
                        {{$valide_msg}}
                        @endif
                    </div>
                    @else
                   
                    <form method="POST" action="{{ route('reset') }}" aria-label="{{ __('Reset Password') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="role" value="{{ $role }}">

                        <div class="form-group row">
                            <label for="email"  class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email"  type="email" readonly="" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}" required autofocus>

                                @if ($errors->has('email'))
                            <p><span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span></p>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('New Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
                                @if ($errors->has('password'))
                                <p><span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span></p>
                                @endif
                                
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm New Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Reset Password') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    @endif
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


