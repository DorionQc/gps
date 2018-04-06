@extends('layouts.app')

@section('content')
<div class="container">
	<div class="grid-container">
		<div class="grid-x grid-padding-x">
            <div class="medium-9 cell">
            	<fieldset class="fieldset">
            		<legend>Item editor</legend>
                	<form method="POST" action="{{ route('postItem') }}">
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
                            
                        	<div class="medium-6 cell">
                                <label for="amountPerPackaging">Amount per Package</label>
                                <div>
                                    <input 
                                    	id="amountPerPackaging" 
                                    	type="number" 
                                    	name="amountPerPackaging"
                                    	class="number" 
                                    	min=0
                                    	value="{{ isset($current) ? $current->amountPerPackaging : old('amountPerPackaging') }}" 
                                    	required autofocus>
                        
                                    @if ($errors->has('amountPerPackaging'))
                                        <p class="help-text alert">{{ $errors->first('amountPerPackaging') }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="medium-6 cell">
                                <label for="supplier">Supplier</label>
                                <div>
                                    <select name="supplierId" id="supplier">
                                    	@foreach($suppliers as $supplier)
                                    		<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    	@endforeach
                                    </select>
                        
                                    @if ($errors->has('supplierId'))
                                        <p class="help-text alert">{{ $errors->first('supplierId') }}</p>
                                    @endif
                                </div>
                            </div>
                            
                        	<div class="medium-6 cell">
                                <label for="cost">Cost</label>
                                <div>
                                    <input 
                                    	id="cost" 
                                    	type="number" 
                                    	name="cost"
                                    	class="number" 
                                    	min=0
                                    	step="any"
                                    	value="{{ isset($current) ? $current->cost : old('cost') }}" 
                                    	required autofocus>
                        
                                    @if ($errors->has('cost'))
                                        <p class="help-text alert">{{ $errors->first('cost') }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="medium-6 cell">
                                <label for="conditioning">Requires conditioning</label>
                                <div>
                                    <input 
                                    	id="conditioning" 
                                    	name="conditioning"
                                    	type="checkbox" 
                                    	class="checkbox" 
                                    	min=0
                                    	{{ isset($current) && $current->conditioning != 0 ? 'checked' : old('conditioning') }}
                                    	autofocus>
                                    	
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
                	<legend>Existing items</legend>
                		<div class="grid grid-y">
                        	@foreach($items as $item)
                        		<div class="cell medium-3">
                            		<a href="/controller/items/{{ $item->id }}" class="button">
                            			{{ $item->name }}
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
