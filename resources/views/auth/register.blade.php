@extends('layouts.app') 
@section('content')
<div class="container">
	<form class="fieldset" method="POST" action="{{ route('register') }}">
		@csrf
		<div class="grid-container">
			<div class="grid-x grid-padding-x">
        		<div class="medium-6 cell">
        			<label for="name" class="col-md-4 col-form-label text-md-right">Name</label>
        
        			<div class="col-md-6">
        				<input id="name" type="text"
        					class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
        					name="name" value="{{ old('name') }}" required autofocus> 
        				@if ($errors->has('name')) 
        					<span class="invalid-feedback"> 
        						<strong>{{ $errors->first('name') }}</strong>
        					</span> 
        				@endif
        			</div>
        		</div>
        
        		<div class="medium-6 cell">
        			<label for="lastName" class="col-md-4 col-form-label text-md-right">Last
        				Name</label>
        
        			<div class="col-md-6">
        				<input id="lastName" type="text"
        					class="form-control{{ $errors->has('lastName') ? ' is-invalid' : '' }}"
        					name="lastName" value="{{ old('lastName') }}" required autofocus>
        
        				@if ($errors->has('lastName')) 
        					<span class="invalid-feedback"> 
        						<strong>{{ $errors->first('lastName') }}</strong>
        					</span> 
        				@endif
        			</div>
        		</div>
        
        		<div class="medium-6 cell">
        			<label for="email" class="col-md-4 col-form-label text-md-right">E-Mail
        				Address</label>
        
        			<div class="col-md-6">
        				<input id="email" type="email"
        					class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
        					name="email" value="{{ old('email') }}" required> 
        				@if ($errors->has('email')) 
        					<span class="invalid-feedback"> 
        						<strong>{{ $errors->first('email') }}</strong>
        					</span> 
        				@endif
        			</div>
        		</div>
        		
        		<div class="medium-6 cell">
        			<label for="licence" class="col-md-4 col-form-label text-md-right">Licence
        				type</label>
        
        			<div class="col-md-6">
        				<input id="licence" type="number"
        					class="form-control{{ $errors->has('licence') ? ' is-invalid' : '' }}"
        					name="licence" required> 
        				@if ($errors->has('licence')) 
        					<span class="invalid-feedback"> 
        						<strong>{{ $errors->first('licence') }}</strong>
        					</span> 
        				@endif
        			</div>
        		</div>
        
        		<div class="medium-6 cell">
        			<label for="phoneNumber">Phone Number</label>
        
        			<div class="col-md-6">
        				<input id="phoneNumber" type="number"
        					class="form-control{{ $errors->has('phoneNumber') ? ' is-invalid' : '' }}"
        					name="phoneNumber" required> 
        				@if ($errors->has('password')) 
        					<span class="invalid-feedback"> 
        						<strong>{{ $errors->first('phoneNumber') }}</strong>
        					</span> 
        				@endif
        			</div>
        		</div>
        
        		<div class="medium-6 cell">
        		</div>
        		
        		<div class="medium-6 cell">
        			<label for="password" class="col-md-4 col-form-label text-md-right">Password</label>
        
        			<div class="col-md-6">
        				<input id="password" type="password"
        					class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
        					name="password" required> 
        				@if ($errors->has('password')) 
        					<span class="invalid-feedback"> 
        						<strong>{{ $errors->first('password') }}</strong>
        					</span> 
        				@endif
        			</div>
        		</div>
        
        		<div class="medium-6 cell">
        			<label for="password-confirm"
        				class="col-md-4 col-form-label text-md-right">Confirm Password</label>
        
        			<div class="col-md-6">
        				<input id="password-confirm" type="password" class="form-control"
        					name="password_confirmation" required>
        			</div>
        		</div>
        
        
        		<div class="medium-12 cell mb-0">
        			<div class="col-md-6 offset-md-4">
        				<button type="submit" class="button">Register</button>
        			</div>
        		</div>
		
			</div>
		</div>
	</form>
</div>
@endsection
