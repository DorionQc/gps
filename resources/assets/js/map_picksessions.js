var marker = null;
var directionsService;
var directionsDisplay;

function mapReadyCallback() {
	directionsService = new google.maps.DirectionsService();
	directionsDisplay = new google.maps.DirectionsRenderer();
	directionsDisplay.setMap(map);
	directionsDisplay.setOptions({polylineOptions: {strokeColor: '#00ffff'}});
}

function waitForMap() {
	if (!map) {
		setTimeout(waitForMap, 500);
	}
	else {
		mapReadyCallback();
	}
}

$(document).ready(function () {
	waitForMap();
});

function showDirections(destinations) {
	if (map && directionsService) {
		var cegepPos = new google.maps.LatLng(46.816695, -71.1516221);
		var waypoints = [];
		destinations.forEach(function(a, b) {
			waypoints.push({location: new google.maps.LatLng(b.lat, b.lng)});
		});
		var request = {
			origin: cegepPos,
			destination: cegepPos,
			waypoints: waypoints,
			optimizeWaypoints: true,
			travelMode: 'DRIVING'
		};
		directionsService.route(request, function(response, status) {
			if (status == 'OK') {
				directionsDisplay.setDirections(response);
				console.log(response);
			}
			else {
				console.log(response);
			}
		});
	}
}

