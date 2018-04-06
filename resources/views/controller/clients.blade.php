@extends('layouts.app')

@push('head')
<style>
    .container_map {
        padding: 10px;
    }
</style>
<script src="{{ asset('js/map_client.js') }}"></script>
@endpush

@section('content')
<div class="container">
	<div class="grid-container">
		<div class="grid-x grid-padding-x">
            <div class="medium-9 cell">
            	<fieldset class="fieldset">
            		<legend>Client editor</legend>
                	<form method="POST" action="{{ route('postClient') }}">
            			@csrf
                    	<div class="grid-x grid-padding-x">
                            <div class="medium-6 cell">
                                <label for="name">Name</label>
                                <div>
                                    <input 
                                    	id="name" 
                                    	type="text" 
                                    	name="name" 
                                    	value="{{ isset($current) ? $current->name : old('name') }}" 
                                    	required autofocus>
                        
                                    @if ($errors->has('name'))
                                        <p class="help-text alert">{{ $errors->first('name') }}</p>
                                    @endif
                                </div>
                            </div>
                            
                        	<div class="medium-3 cell">
                                <label for="lat">Latitude</label>
                                <div>
                                    <input 
                                    	id="lat" 
                                    	type="number" 
                                    	name="lat"
                                    	class="number"
                                    	step="any" 
                                    	value="{{ isset($current) ? $current->lat : old('lat') }}" 
                                    	required autofocus>
                        
                                    @if ($errors->has('lat'))
                                        <p class="help-text alert">{{ $errors->first('lat') }}</p>
                                    @endif
                                </div>
                            </div>
                            
                        	<div class="medium-3 cell">
                                <label for="lng">Longitude</label>
                                <div>
                                    <input 
                                    	id="lng" 
                                    	type="number" 
                                    	name="lng"
                                    	class="number"
                                    	step="any" 
                                    	value="{{ isset($current) ? $current->lng : old('lng') }}" 
                                    	required autofocus>
                        
                                    @if ($errors->has('lng'))
                                        <p class="help-text alert">{{ $errors->first('lng') }}</p>
                                    @endif
                                </div>
                            </div>
                            
                        	<div class="cell medium-12 container_map">
                        		@include('layouts.partials.map')
                        	</div>
                            
                            <div class="medium-12 cell grid-x grid-padding-x">
                            	<div class="medium-3 cell input-group">
                                    <div class="input-group-button">
                                        <button type="submit" class="button">
                                            {{ isset($current) ? 'Edit' : 'Create' }}
                                        </button>
                                    </div>
                                    <input 
                                    	id="id" 
                                    	class="input-group-field" 
                                    	type="text" 
                                    	name="id" 
                                    	value="{{ isset($current) ? $current->id : old('id') }}" 
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
                	<legend>Existing clients</legend>
                		<div class="grid grid-y">
                        	@foreach($clients as $client)
                        		<div class="cell medium-3">
                            		<a href="/controller/clients/{{ $client->id }}" class="button">
                            			{{ $client->name }}
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
