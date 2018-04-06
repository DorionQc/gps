@extends('layouts.app')

@push('head')
<script>
	var lastPosition;
	var state = {
		started: {{ $state->started }},
		in_progress: {{ $state->in_progress }},
		waiting_finish: {{ $state->waiting_finish }},
		finished: {{ $state->finished }}
	};
    var clients = [
        @foreach($clients as $key => $client)
            <?php if ($key != 0) echo ','; ?>
        	<?php echo 
            	'{ id: ' . $client->id . 
            	', command: ' . $client->commandId . 
            	', date: "'. $client->date . 
            	'", name: "'. $client->name .
            	'", complete: ' . $client->complete .
            	', position : { lat: '. $client->lat .', lng: '. $client->lng .
            	'}}';
        	?>
        @endforeach
    ]
    var path = [
    	@foreach($trace as $key => $pos)
            <?php if ($key != 0) echo ','; ?>
        	<?php echo '{ lat: ' . $pos->lat . ', lng: ' . $pos->lng . '}'; ?>
    	@endforeach
    ];

    $(document).ready(function() {
        $(".view_client_button").click(function () {
            var commandId = $(this).data().id;
			$.ajax({
				url: "{{ route('getCommandItems') }}",
				type: 'POST',
				data: {
					id: commandId,
					_token: '{{ csrf_token() }}'
				},
				success: function(data) {
					console.log(data);
					data = JSON.parse(data);
					var command = null;
					for (var i = 0; i < clients.length; i++) {
						if (clients[i].command == commandId) {
							command = clients[i];
							break;
						}
					}
					if (command === null) return;
					
					if (command.letter) {
						$("#name_textbox").val(command.name + " (" + command.letter + ")");
						map.panTo(new google.maps.LatLng(command.position.lat, command.position.lng));
					} else {
						$("#name_textbox").val(command.name);
					}
					$("#date_textbox").val(command.date);
					if (state.in_progress != 0 && command.complete != 1) {
						$("#finish_button").prop('disabled', false);
					} else {
						$("#finish_button").prop('disabled', true);
					}
					console.log(commandId);
					$("#id").val(commandId);
					console.log($("#id").val());

					var items_body_content = "";
					for (var i = 0; i < data.length; i++) {
						items_body_content += 
    						'<tr id="' + data[i]['id'] + '_table_tr" data-id=' + data[i]['id'] + '>' +
    							'<th>' + data[i]['name'] + '</th>' +
    							'<th>' + data[i]['amount'] + '</th>' +
    						'</tr>';
					}
					$("#items_body").html(items_body_content);
				},
				error: function(data) {
					console.log(data);
				}
			});
        });
        $("#start_button").click(function() {
            var commandId = $("#id").val();
            var theButton = this;
            if (state.started == 0) {
                $.ajax({
    				url: "{{ route('startSession') }}",
    				type: 'POST',
    				data: {
        				moment: Math.ceil(new Date().getTime() / 1000),
    					_token: '{{ csrf_token() }}'
    				},
    				success: function(stuff) {
        				stuff = JSON.parse(stuff);
        				console.log(stuff);
        				if (stuff.success == 0) {
                    		var i = 0;
                    		while (i < clients.length && clients[i].command != commandId) i++;
                    		state = stuff.state;
        	                $(theButton).prop('disabled', true);
                    		if (i != clients.length) {
        	                	if ($("#name_textbox").val() != "" && clients[i].complete == 0) {
        							$("#finish_button").prop('disabled', false);
        	                	}
                    		}
        				}
    				},
    				error: function(stuff) {
    					console.log(stuff);
    				}
    			});
            }
        });
        $("#finish_button").click(function() {
            if (state.started == 1 && state.in_progress == 1) {
                var commandId = $("#id").val();
                if (commandId != "") {
                	$.ajax({
        				url: "{{ route('reachCheckpoint') }}",
        				type: 'POST',
        				data: {
            				id: commandId,
        					_token: '{{ csrf_token() }}'
        				},
        				success: function(stuff) {
            				stuff = JSON.parse(stuff);
            				console.log(stuff);
            				state = stuff.state;
                    		var i = 0;
                    		while (i < clients.length && clients[i].command != commandId) i++;
                    		if (i == clients.length) return;
                    		clients[i].complete = 1;
                    		$("#finish_button").prop('disabled', true);
                    		if (state.waiting_finish == 1) {
                        		$("#finish_session_button").prop('disabled', false);
                    		}
        				},
        				error: function(stuff) {
        					console.log(stuff);
        				}
        			});
                }
            }
        })
        $("#finish_session_button").click(function() {
            if (state.started == 1 && state.waiting_finish == 1) {
                for (var i = 0; i < clients.length; i++) {
                    if (clients[i].complete != 1) return;
                }
                $.ajax({
    				url: "{{ route('finishSession') }}",
    				type: 'POST',
    				data: {
        				moment: Math.ceil(new Date().getTime() / 1000),
    					_token: '{{ csrf_token() }}'
    				},
    				success: function(stuff) {
        				stuff = JSON.parse(stuff);
        				console.log(stuff);
        				if (stuff.success == 0) {
            				state = stuff.state;
        				}
    					window.location.href = "/truck/pickSession";
    				},
    				error: function(stuff) {
    					console.log(stuff);
    				}
    			});
            }
        });
        $("#update_directions_button").click(function() {
            if (map) {
                showDirections();
            }
        });
    });
</script>

<script src="{{ asset('js/map_truck_sessions.js') }}"></script>
@endpush

@section('content')
<div class="container">
	<div class="grid-container">
		<div class="grid-x grid-padding-x">
            <div class="medium-9 cell">
            	<fieldset class="fieldset">
            		<legend id="session_info">Session Info</legend>
            		<div class="cell medium-12" style="padding-bottom: 20px">
                        @include('layouts.partials.map')
                    </div>
                    <div class="grid-x grid-padding-x">
                        <div class="medium-12 cell">
                        	<div class="grid-x grid-padding-x" id="client_info_container">
                        		
								<div class="medium-6 cell">
                        			<div style="border: 2px solid #e6e6e6">
                                    	<div class="grid-x grid-padding-x">
                                    		<div class="cell medium-12 supplier_div">
                                           		<label for="name_textbox">Client : </label>
                                           		<input type="text" class="text" id="name_textbox" readonly value="">
                                        	</div>
                                        		
                                    		<div class="cell medium-12 name_div">
                                           		<label for="date_textbox">Date : </label>
                                           		<input type="text" class="text" id="date_textbox" readonly value="">
                                        	</div>
                                        	
                                        	
                                        	<div class="medium-6 cell input-group">
                                                <div class="input-group-button">
                                           			<button type="button" class="button" id="finish_button" disabled value="">
                                                    	Finish
                                                    </button>
                                                </div>
                                                <input 
                                                	id="id" 
                                                	class="input-group-field" 
                                                	type="text" 
                                                	name="id" 
                                                	placeholder="Auto"
                                                	readonly />
                                            </div>
                                    	</div>
                                    </div>
                                </div>
                                <div class="medium-6 cell">
                                	<table id="items">
                                		<thead>
                                			<tr>
                                				<th>Name</th>
                                				<th>Quantity</th>
                                			</tr>
                                		</thead>
                                		<tbody id="items_body">
                                		</tbody>
                                	</table>
                                </div>
                        	</div>
                        </div>
                    </div>
    			</fieldset>
                <fieldset class="fieldset">
                	<legend>Directions</legend>
                	<div id="directions_panel"></div>
                </fieldset>
            </div>
            <div class="medium-3 cell">
                <fieldset class="fieldset">
                	<legend>Controls</legend>
                    <div class="grid grid-y">
                        <div class="cell medium-3">
                         	<button type="button" id="start_button" class="button" data-id={{ $client->commandId }} 
                         		<?php if ($state->started) echo 'disabled'; ?>>
                         		Start
                         	</button>
                        </div>
                        <div class="cell medium-3">
                         	<button type="button" id="finish_session_button" class="button" data-id={{ $client->commandId }} 
                         		<?php if ($state->waiting_finish == 0) echo 'disabled'; ?>>
                         		Finish
                         	</button>
                        </div>
                        <div class="cell medium-3">
                         	<button type="button" id="update_directions_button" class="button">
                         		Update Directions
                         	</button>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="fieldset">
                	<legend>Commands</legend>
                    <div class="grid grid-y">
                        @foreach($clients as $client)
                        	<div class="cell medium-3">
                         		<button type="button" class="button view_client_button" data-id={{ $client->commandId }}>
                            		{{ $client->name }}
                         		</button>
                        	</div>
                        @endforeach
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
</div>
@endsection
