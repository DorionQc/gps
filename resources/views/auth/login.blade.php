@extends('layouts.app')

@section('content')
<div class="container">
    <form class="fieldset" method="POST" action="{{ route('login') }}">
        @csrf
    
		<div class="grid-container">
			<div class="grid-x grid-padding-x">
                <div class="medium-6 cell">
                    <label for="email" class="col-sm-4 col-form-label text-md-right">E-Mail Address</label>
            
                    <div class="col-md-6">
                        <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>
            
                        @if ($errors->has('email'))
                            <span class="invalid-feedback">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
            
                <div class="medium-6 cell">
                    <label for="password" class="col-md-4 col-form-label text-md-right">Password</label>
            
                    <div class="col-md-6">
                        <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
            
                        @if ($errors->has('password'))
                            <span class="invalid-feedback">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
            
                <div class="medium-6 cell ">
                    <div class="col-md-8 offset-md-4">
                        <button type="submit" class="button">
                            Login
                        </button>
                    </div>
                </div>
        	</div>
        </div>
    </form>
</div>
@endsection
