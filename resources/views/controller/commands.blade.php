@extends('layouts.app')

@push('head')

<script>
	var items = [
		@if(isset($current_items))
			@foreach($current_items as $key => $item)
				<?php echo $key == 0 ? '' : ','; ?>{ id: {{ $item->id }}, amount: {{ $item->amount }} }
			@endforeach
		@endif
	];
	$(document).ready(function() {
		$('.item_add_button').click(function() {
			var panel = $('div.tabs-panel.is-active').children().children().children('input');
			var supplierName = panel[0].value;
			var itemName = panel[1].value;
			var quantity = panel[2].value;
			var itemId = $(this).data().itemid;
			if (quantity < 0) return;
			var removing = (quantity == 0);

			if ($("#" + itemId + "_table_tr").length > 0) {
				var index = items.findIndex(function(a) {
				    return a.id==itemId;
				});
				if (removing) {
					items.splice(index, 1);
					$('#'+itemId+"_table_tr").remove();
					$("#" + itemId + "_pnl-label").parent().removeClass('is_within');
				} else {
					items[index].amount = quantity;
					$('#'+itemId+"_table_tr :nth-child(2)").html(quantity);
				}
			} else {
    			items.push({
    				id: itemId,
    				amount: quantity,
    			});
    
    			var addTo = $('#selected_items_body');
    			addTo.append(
                    "<tr id=\"" + itemId + "_table_tr\" data-id=\"" + itemId + "\">"
                    	+"<th>"+itemName+"</th>"
                    	+"<th>"+quantity+"</th>"
                    +"</tr>"
    			);
				$("#" + itemId + "_pnl-label").parent().addClass('is_within');
			}
		});

		$('#submit_button').click(function() {
			$.ajax({
				url: "{{ route('postCommand') }}",
				type: 'POST',
				data: {
					clientId: $('#client').val(),
					id: $('#id').val(),
					items: items,
					_token: '{{ csrf_token() }}'
				},
				success: function() {
					window.location.href = "/controller/commands";
				}
			});
		});
	});
</script>

@endpush

@section('content')
<div class="container">
	<div class="grid-container">
		<div class="grid-x grid-padding-x">
            <div class="medium-9 cell">
            	<fieldset class="fieldset">
            		<legend>Command editor</legend>
                	<form method="POST" action="{{ route('postCommand') }}">
            			@csrf
                    	<div class="grid-x grid-padding-x">
                            <div class="medium-6 cell">
                                <label for="client">Client</label>
                                <div>
                                    <select name="clientId" id="client">
                                    	@foreach($clients as $client)
                                    		<option value="{{ $client->id }}" 
                                    		  <?php if (isset($current) && $current->id == $client->id) echo "selected"; ?> >
                                    			{{ $client->name }}
                                    		</option>
                                    	@endforeach
                                    </select>
                                    
                                    @if ($errors->has('clientId'))
                                        <p class="help-text alert">{{ $errors->first('clientId') }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="medium-12 cell" style="margin: 0 0 1rem;">
                            	<div class="grid-x grid-padding-x">
                            		<!-- Tab contents -->
                            		<div class="medium-6 cell">
                            			<div class="tabs-content" style="border: 2px solid #e6e6e6" data-tabs-content="items">
                                        	@foreach($items as $key => $item)
                                				<div class="tabs-panel <?php if ($key == 0) echo 'is-active'; ?>" id="{{ $item->id }}_pnl">
                                					<div class="grid-x grid-padding-x">
                                						<div class="cell medium-12 supplier_div">
                                        					<label for="{{ $item->id }}_supplier_name_textbox">Supplier : </label>
                                        					<input type="text" class="text" id="{{ $item->id }}_supplier_name_textbox" readonly
                                        						value="{{ $item->supplierName }}">
                                    					</div>
                                    						
                                						<div class="cell medium-12 name_div">
                                        					<label for="{{ $item->id }}_name_textbox">Item : </label>
                                        					<input type="text" class="text" id="{{ $item->id }}_name_textbox" readonly
                                        						value="{{ $item->name }}">
                                    					</div>
                                    						
                                						<div class="cell medium-12 quantity_div">
                                        					<label for="{{ $item->id }}_quantity">Quantity : </label>
                                        					<input type="number" value=<?php echo isset($item->amount) ? $item->amount : 1 ?> 
                                        						min=0 class="number" id="{{ $item->id }}_quantity"><span>({{ $item->amountPerPackaging }} per packaging)</span>
                                    					</div>
                                						<div class="cell medium-12">
                                							<div class="grid-x align-right">
                                            					<button 
                                            						type="button" 
                                            						class="button cell medium-3 item_add_button" 
                                            						id="{{ $item->id }}_add_button" 
                                            						data-itemid="{{ $item->id }}">
                                            						Set
                                            					</button>
                                        					</div>
                                    					</div>
                                					</div>
                                                </div>
                                            @endforeach
                            			</div>
                            		    <!-- End tab contents -->
                                        <!-- Selected items -->
                            			<table id="selected_items">
                            				<thead>
                            					<tr>
                            						<th>Name</th>
                            						<th>Quantity</th>
                            					</tr>
                            				</thead>
                            				<tbody id="selected_items_body">
                            					@if(isset($current_items))
                            						@foreach($current_items as $item)
                            							<tr id="{{ $item->id }}_table_tr" data-id={{ $item->id }}>
                            								<th>{{ $item->name }}</th>
                            								<th>{{ $item->amount }}</th>
                            							</tr>
                            						@endforeach
                            					@endif
                            				</tbody>
                            			</table>
                                        <!-- End selected items -->
                            		</div>
                            		<div class="medium-6 cell">
                            			<!-- Tabs -->
                            			<ul class="tab_alternate_colors vertical tabs" data-tabs id="items">
                                        	@foreach($items as $key => $item)
                                          		<li class="tabs-title 
                                          		    <?php if ($key == 0) echo 'is-active'; 
                                              		      if (isset($current_items)) {
                                              		          foreach($current_items as $i) {
                                              		              if ($i->id == $item->id) {
                                              		                  echo ' is_within';
                                              		                  break;
                                              		              }
                                              		          }
                                              		      } ?>
                                          		"><a href="#{{ $item->id }}_pnl">{{ $item->name }}</a></li>
                                        	@endforeach
                                        </ul>
                                        <!-- End tabs -->
                            		</div>
                            	</div>
                            </div>
                            
                            <div class="medium-12 cell grid-x grid-padding-x">
                            	<div class="medium-3 cell input-group">
                                    <div class="input-group-button">
                                        <button type="button" class="button" id="submit_button">
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
                	<legend>Existing commands</legend>
                		<div class="grid grid-y">
                        	@foreach($commands as $command)
                        		<div class="cell medium-3">
                            		<a href="/controller/commands/{{ $command->id }}" class="button">
                            			{{ $command->name." - ".$command->date }}
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
