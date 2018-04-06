@extends('layouts.app')

@push('head')
<style>
    .container_map {
        padding: 10px;
    }
</style>
<script src="{{ asset('js/map_controller_home.js') }}"></script>
@endpush

@section('content')
<div class="container">
	<div class="grid-container">
		<div class="grid-x grid-padding-x">
            <div class="medium-8 cell">
            	<fieldset class="fieldset">
            		<legend id="session_info">Control Center</legend>
            		<div class="cell medium-12" style="padding-bottom: 20px">
                        @include('layouts.partials.map')
                    </div>
                    <div class="grid-x grid-padding-x">
                        <div class="medium-12 cell">
                        	<div class="grid-x grid-padding-x" id="client_info_container">
                        		
								<div class="medium-6 cell">
                        			<div style="border: 2px solid #e6e6e6">
                                    	<div class="grid-x grid-padding-x">
                                    		<div class="cell medium-12 vehicle_div">
                                           		<label for="vehicle_textbox">Vehicle : </label>
                                           		<input type="text" class="text" id="vehicle_textbox" readonly value="">
                                        	</div>
                                        		
                                    		<div class="cell medium-12 name_div">
                                           		<label for="name_textbox">Driver Name : </label>
                                           		<input type="text" class="text" id="name_textbox" readonly value="">
                                        	</div>
                                        	
                                        	<div class="cell medium-12 phone_div">
                                           		<label for="phone_textbox">Driver Phone : </label>
                                           		<input type="text" class="text" id="phone_textbox" readonly value="">
                                        	</div>
                                    	</div>
                                    </div>
                                </div>
                                <div class="medium-6 cell">
                                	<table id="commands">
                                		<thead>
                                			<tr>
                                				<th>Client</th>
                                				<th>Date</th>
                                				<th>Item Count</th>
                                			</tr>
                                		</thead>
                                		<tbody id="commands_body">
                                		</tbody>
                                	</table>
                                </div>
                        	</div>
                        </div>
                    </div>
    			</fieldset>
            </div>
            <div class="medium-4 cell">
                <fieldset class="fieldset">
                	<legend>Vehicles</legend>
                    <div class="grid grid-y">
                        <div class="cell medium-3">
                        	<input id="show_all_vehicles_checkbox" type="checkbox" class="checkbox" />
                        	<label for="show_all_vehicles_checkbox">Show all</label>
                            <div id="vehiclesDiv">
                        	</div>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
</div>
@endsection