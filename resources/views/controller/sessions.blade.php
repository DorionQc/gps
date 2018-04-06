@extends('layouts.app')

@section('content')
<div class="container">
	<div class="grid-container">
		<div class="grid-x grid-padding-x">
            <div class="medium-9 cell">
            	<fieldset class="fieldset">
            		<legend>Session editor</legend>
                	<form method="POST" action="{{ route('postSession') }}">
            			@csrf
                    	<div class="grid-x grid-padding-x">
                            
                            <div class="medium-6 cell">
                                <label for="vehicle">Vehicle</label>
                                <div>
                                    <select name="vehicleId" id="vehicle">
                                    	@foreach($vehicles as $vehicle)
                                    		<option value="{{ $vehicle->id }}">{{ $vehicle->name }}</option>
                                    	@endforeach
                                    </select>
                        
                                    @if ($errors->has('vehicleId'))
                                        <p class="help-text alert">{{ $errors->first('vehicleId') }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="medium-12 cell grid-x grid-padding-x">
                            	<div class="medium-3 cell input-group">
                                    <div class="input-group-button">
                                        <button type="submit" class="button">Create</button>
                                    </div>
                                    <input 
                                    	id="id" 
                                    	class="input-group-field" 
                                    	type="text" 
                                    	name="id" 
                                    	value="{{ old('id') }}" 
                                    	placeholder="Auto"
                                    	readonly >
                                </div>
                            </div>
                        </div>
        			</form>
    			</fieldset>
            </div>
            <div class="medium-3 cell">
                <fieldset class="fieldset">
                	<legend>Existing sessions</legend>
                		<div class="grid grid-y">
                        	@foreach($sessions as $session)
                        		<div class="cell medium-3">
                            		<a href="/controller/sessions/{{ $session->id }}" class="button">
                            			{{ $session->id }} - {{ $session->vehicleName }}
                            		</a>
                        		</div>
                        	@endforeach
                		</div>
                </fieldset>
            </div>
        </div>
    </div>
</div>
@endsection
