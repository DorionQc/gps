@extends('layouts.app')

@section('content')
<div class="container">
	<div class="grid-container">
		<div class="grid-x grid-padding-x">
            <div class="medium-9 cell">
            	<fieldset class="fieldset">
            		<legend>Vehicle editor</legend>
                	<form method="POST" action="{{ route('postTruck') }}">
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
                                <label for="capacity">Capacity</label>
                                <div>
                                    <input 
                                    	id="capacity" 
                                    	type="number" 
                                    	name="capacity"
                                    	class="number" 
                                    	min=0
                                    	value="{{ isset($current) ? $current->capacity : old('capacity') }}" 
                                    	autofocus <?php echo (!isset($current) || $current->isOnTheRoad == 0) ? 'required' : 'readonly'; ?>>
                        
                                    @if ($errors->has('capacity'))
                                        <p class="help-text alert">{{ $errors->first('capacity') }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="medium-6 cell">
                                <label for="licence">Licence</label>
                                <div>
                                    <select name="licence" id="licence" 
                                    	autofocus <?php echo (!isset($current) || $current->isOnTheRoad == 0) ? 'required' : 'readonly'; ?>>
                                    	
                                    	<option value="1" {{ isset($current) && $current->licence == 1 ? 'selected' : '' }}>1</option>
                                    	<option value="5" {{ isset($current) && $current->licence == 5 ? 'selected' : '' }}>5</option>
                                    </select>
                        
                                    @if ($errors->has('licence'))
                                        <p class="help-text alert">{{ $errors->first('licence') }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="medium-6 cell">
                                <label for="conditioning">Conditioning</label>
                                <div>
                                    <input 
                                    	id="conditioning" 
                                    	name="conditioning"
                                    	type="checkbox" 
                                    	class="checkbox" 
                                    	min=0
                                    	{{ isset($current) && $current->conditioning != 0 ? 'checked' : old('conditioning') }}
                                    	autofocus <?php echo (!isset($current) || $current->isOnTheRoad == 0) ? '' : 'readonly'; ?>>
                                    	
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
    			@if(isset($inventory))
				<fieldset class="fieldset">
					<legend>Inventory</legend>
					<table>
						<thead>
							<tr>
								<th>Name</th>
								<th>Quantity</th>
								<th>Packaging</th>
								<th>Virtual Amount</th>
							</tr>
						</thead>
						<tbody>
							@foreach($inventory as $item)
							<tr>
								<td>{{ $item->name }}</td>
								<td>{{ $item->amount }}</td>
								<td>{{ $item->packaging }}</td>
								<td>{{ $item->virtualAmount }}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</fieldset>
				@endif
            </div>
            <div class="medium-3 cell">
                <fieldset class="fieldset">
                	<legend>Existing vehicles</legend>
                		<div class="grid grid-y">
                        	@foreach($vehicles as $truck)
                        		<div class="cell medium-3">
                            		<a href="/controller/trucks/{{ $truck->id }}" class="button">
                            			{{ $truck->name }}
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
