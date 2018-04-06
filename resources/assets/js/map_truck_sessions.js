var marker = null;
var directionsService;
var directionsDisplay;

function mapReadyCallback() {
	directionsService = new google.maps.DirectionsService();
	directionsDisplay = new google.maps.DirectionsRenderer();
	directionsDisplay.setMap(map);
	directionsDisplay.setOptions({polylineOptions: {strokeColor: '#00ffff'}});
	showDirections();
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

function showDirections() {
	if (map && directionsService) {
		var cegepPos = new google.maps.LatLng(46.816695, -71.1516221);
		var waypoints = [];
		for (var i = 0; i < positions.length; i++) {
			waypoints.push({location: new google.maps.LatLng(positions[i].lat, positions[i].lng)});
		}
		console.log(positions);
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
				for (var i = 0; i < response.routes[0].waypoints_order.length; i++) {
					positions[i].order = waypoints_order[i];
					positions[i].letter = String.fromCharCode(66 + waypoints_order[i]);
				}
				console.log(positions);
			}
			else {
				console.log(response);
			}
		});
	}
}

