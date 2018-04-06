@extends('layouts.app')

@section('content')
<div class="container">
	<div class="grid-container">
		<div class="grid-x grid-padding-x">
            <div class="medium-9 cell">
            	<fieldset class="fieldset">
            		<legend>Supplier editor</legend>
                	<form method="POST" action="{{ route('postSupplier') }}">
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
                	<legend>Existing suppliers</legend>
                		<div class="grid grid-y">
                        	@foreach($suppliers as $supplier)
                        		<div class="cell medium-3">
                            		<a href="/controller/suppliers/{{ $supplier->id }}" class="button">
                            			{{ $supplier->name }}
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
