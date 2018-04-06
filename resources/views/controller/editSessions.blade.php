@extends('layouts.app')

@push('head')

<script>
	var commands = [
		@if(isset($current_commands))
			@foreach($current_commands as $key => $command)
				<?php echo $key == 0 ? '' : ','; ?> {{ $command->id }} 
			@endforeach
		@endif
	];
	var positions = [];
    @foreach(array_merge($commands, $current_commands) as $key => $command)
    	<?php echo 
        	'positions['. $command->id .'] = '.
                '{ name: "'. $command->name .'", position : { lat: '. $command->lat .', lng: '. $command->lng .'}};'  
    	?>
    @endforeach
	$(document).ready(function() {
		$('.command_add_button').click(function() {

			var panel = $('div.tabs-panel.is-active').children().children().children('input');
			var name = panel[0].value;
			var date = panel[1].value;
			var item_count = panel[2].value;
			var commandId = $(this).data().commandid;
			
			if ($("#" + commandId + "_table_tr").length > 0) {
				// Remove
				commands.splice(commands.indexOf(commandId), 1);
				var entry = $('#' + commandId + '_table_tr').remove();
				$(this).html('Add');
				$("#" + commandId + "_pnl-label").parent().removeClass('is_within');
			} else {
				// Add
				commands.push(commandId);
	        	var addTo = $('#selected_commands_body');
            	addTo.append(
                        "<tr id=\"" + commandId + "_table_tr\" data-id=\"" + commandId + "\">"
                        	+"<th>"+name+"</th>"
                        	+"<th>"+date+"</th>"
                        +"</tr>"
            	);
				$(this).html('Remove');
				$("#" + commandId + "_pnl-label").parent().addClass('is_within');
			}
			if(showDirections) {
				showDirections();
			}
		});

		$('#submit_button').click(function() {
			console.log('edit');
			$.ajax({
				url: "{{ route('postEditSession') }}",
				type: 'POST',
				data: {
					id: $('#id').val(),
					commands: commands,
					_token: '{{ csrf_token() }}'
				},
				success: function() {
					window.location.href = "/controller/sessions";
				},
				error: function(dat) {
					console.log(dat);
				}
			});
		});
	});
</script>
<script src="{{ asset('js/map_sessions.js') }}"></script>
@endpush

@section('content')
<div class="container">
	<div class="grid-container">
		<div class="grid-x grid-padding-x">
            <div class="medium-9 cell">
            	<fieldset class="fieldset">
            		<legend>Session editor</legend>
            		<div class="cell medium-12" style="padding-bottom: 20px">
                        @include('layouts.partials.map')
                    </div>
                	<form method="POST" action="{{ route('postEditSession') }}">
            			@csrf
                    	<div class="grid-x grid-padding-x">
                            <div class="medium-12 cell">
                            	<div class="grid-x grid-padding-x">
                            		<!-- Tab contents -->
                            		<div class="medium-6 cell">
                            			<div class="tabs-content" style="border: 2px solid #e6e6e6" data-tabs-content="commands">
                                        	@foreach(array_merge($commands, $current_commands) as $key => $command)
                                				<div class="tabs-panel <?php if ($key == 0) echo 'is-active'; ?>" id="{{ $command->id }}_pnl">
                                					<div class="grid-x grid-padding-x">
                                						<div class="cell medium-12">
                                        					<label for="{{ $command->id }}_client_name_textbox">Client : </label>
                                        					<input type="text" class="text" id="{{ $command->id }}_client_name_textbox" readonly
                                        						value="{{ $command->name }}">
                                    					</div>
                                    					
                                						<div class="cell medium-12">
                                        					<label for="{{ $command->id }}_client_date_textbox">Date : </label>
                                        					<input type="text" class="text" id="{{ $command->id }}_client_date_textbox" readonly
                                        						value="{{ $command->date }}">
                                    					</div>
                                    						
                                						<div class="cell medium-12">
                                        					<label for="{{ $command->id }}_item_count_textbox">Item count : </label>
                                        					<input type="text" class="text" id="{{ $command->id }}_item_count_textbox" readonly
                                        						value="{{ $command->item_count }}">
                                    					</div>
                                    						
                                						<div class="cell medium-12">
                                							<div class="grid-x align-right">
                                            					<button 
                                            						type="button" 
                                            						class="button cell medium-4 command_add_button" 
                                            						id="{{ $command->id }}_add_button" 
                                            						data-commandid="{{ $command->id }}">
                                            						<?php echo is_null($command->sessionId) ? 'Add' : 'Remove'; ?>
                                            					</button>
                                        					</div>
                                    					</div>
                                					</div>
                                                </div>
                                            @endforeach
                            			</div>
                            		    <!-- End tab contents -->
                                        <!-- Selected commands -->
                            			<table id="selected_commands">
                            				<thead>
                            					<tr>
                            						<th>Client</th>
                            						<th>Date</th>
                            					</tr>
                            				</thead>
                            				<tbody id="selected_commands_body">
                            					@if(isset($current_commands))
                            						@foreach($current_commands as $comm)
                            							<tr id="{{ $comm->id }}_table_tr" data-id={{ $comm->id }}>
                            								<th>{{ $comm->name }}</th>
                            								<th>{{ $comm->date }}</th>
                            							</tr>
                            						@endforeach
                            					@endif
                            				</tbody>
                            			</table>
                                        <!-- End selected commands -->
                            		</div>
                            		<div class="medium-6 cell">
                            			<!-- Tabs -->
                            			<ul class="tab_alternate_colors vertical tabs" data-tabs id="commands">
                                        	@foreach(array_merge($commands, $current_commands) as $key => $comm)
                                          		<li class="tabs-title 
                                          		<?php if ($key == 0) echo 'is-active'; if(!is_null($comm->sessionId)) echo ' is_within'; ?>
                                          		"><a href="#{{ $comm->id }}_pnl">{{ $comm->name }} | {{ $comm->date }}</a></li>
                                        	@endforeach
                                        </ul>
                                        <!-- End tabs -->
                            		</div>
                            	</div>
                            </div>
                            
                            <div class="medium-12 cell grid-x grid-padding-x">
                            	<div class="medium-3 cell input-group">
                                    <div class="input-group-button">
                                        <button type="button" class="button" id="submit_button">Confirm</button>
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
