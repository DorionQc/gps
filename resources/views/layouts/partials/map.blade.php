@push('head_end')
    <script>
    var map;
    var map_init_position = map_init_position || {lat: 46.816695, lng: -71.1516221};
    function initMap() {
    	map = new google.maps.Map(document.getElementById('map'), {
    		zoom: 20,
    		center: map_init_position,
    		mapTypeId: "hybrid"
    	});
    }
    </script>
	<script async defer 
		src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_API_KEY') }}&callback=initMap&language=fr&libraries=places">
    </script>
@endpush

<div id="map"></div>
