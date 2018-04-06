@extends('layouts.app')

@push('head')
<script src="{{ asset('js/map_picksessions.js') }}"></script>
<script>
	$(document).ready(function() {
    	$(".view_session_button").click(function() {
    		var sessionId = $(this).data().id;
    		console.log(sessionId);
    		$.ajax({
				url: "{{ route('getSessionPath') }}",
				type: 'POST',
				data: {
					id: sessionId,
					_token: '{{ csrf_token() }}'
				},
				success: function(stuff) {
					var stuffObj = JSON.parse(stuff);
					$("#pick_button").html(
                            "<a href=\"/truck/pickSession/" + sessionId + "\" class=\"button\" style=\"width: 100%\" id=\"submit_button\">Pick</a>"
                    );
					showDirections(stuffObj);
				},
				error: function(stuff) {
					console.log(stuff);
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
            		<legend id="session_info">Session Info</legend>
            		<div class="cell medium-12" style="padding-bottom: 20px">
                        @include('layouts.partials.map')
                    </div>
                    <div class="grid-x grid-padding-x">
                        <div class="medium-12 cell">
                        	<div class="grid-x grid-padding-x">
                        		<div class="medium-12 cell">
                        			<div style="border: 2px solid #e6e6e6; padding: 5px;">
                                        <div class="medium-12 cell">
                                        	<div class="grid-x grid-padding-x">
                                            	<div class="medium-3 cell"></div>
                                            	<div class="medium-6 cell" id="pick_button">
                                                </div>
                                            	<div class="medium-3 cell"></div>
                                        	</div>
                                        </div>
                                    </div>
                        		</div>
                        	</div>
                        </div>
                    </div>
    			</fieldset>
            </div>
            <div class="medium-3 cell">
                <fieldset class="fieldset">
                	<legend>Available sessions</legend>
                    <div class="grid grid-y">
                        @foreach($sessions as $session)
                        	<div class="cell medium-3">
                         		<button type="button" class="button view_session_button" data-id={{ $session->id }}>
                         			{{ $session->id }} - {{ $session->vehicleName }}
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