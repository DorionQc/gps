var directionsService;
var directionsDisplay;
var placeService;
var currentInfoWindow = null;
var infoWindows = [];
var trace;
var waypoint_order = [];
var markers = [];
var truckMarker = null;

function handleWatch(pos) {
	if (state.started !== 1) return;
	
	lastPosition = new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude);
	if (!truckMarker) {
		truckMarker = new google.maps.Marker({
			position: lastPosition,
			icon: window.location.origin + "/img/icons/truck.png",
			map: map
		});
	} else {
		truckMarker.setPosition(lastPosition);
	}
	$.ajax({
		url: "/truck/coords",
		type: 'POST',
		data: {
			lat: pos.coords.latitude,
			lng: pos.coords.longitude,
			moment: Math.ceil(new Date().getTime() / 1000),
			_token: $('meta[name="csrf-token"]').attr('content')
		},
		success: function(stuff) {
			// DO WHAT WEBSOCKETS WOULD DO HERE.
			stuff = JSON.parse(stuff);
			if (stuff.hasNotifies) {
				location.reload(true);
			}
			if (map) {
				trace.getPath().push(lastPosition)
			}
		},
		error: function(stuff) {
			console.log(stuff);
		}
	});
}

function mapReadyCallback() {
	directionsService = new google.maps.DirectionsService();
	directionsDisplay = new google.maps.DirectionsRenderer({suppressMarkers: true});
	directionsDisplay.setMap(map);
	directionsDisplay.setPanel(document.getElementById('directions_panel'));
	directionsDisplay.setOptions({polylineOptions: {strokeColor: '#00ffff'}});
	placeService = new google.maps.places.PlacesService(map);
	showDirections();
	
	var googlePath = [];
	for (var i = 0; i < path.length; i++) {
		googlePath.push(new google.maps.LatLng(path[i].lat, path[i].lng));
	}
	
	trace = new google.maps.Polyline({
		path: googlePath,
		geodesic: true,
		strokeColor: '#ff0000',
		strokeOpacity: 1.0,
		strokeWeight: 2
	});
	trace.setMap(map);

    navigator.geolocation.watchPosition(handleWatch, ()=>{}, {enableHighAccuracy: true});
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

function makeInfoWindowContent(name, address, phone, img) {
	return '<h2>' + name + '</h2>'+
		'<div class="grid-x grid-overlow-x">'+
			'<div class="cell medium-6">'+
				'<img width=150 height=150 src=' + img + ' alt="' + name +'" />'+
			'</div>'+
			'<div class="cell medium-6" style="padding-left: 5px; max-width: 150px; word-wrap: break-word;">'+
				'Phone: ' + phone +
				'<br>Address: ' + address +
			'</div>'+
		'</div>';
}

function eraseMarkers() {
	for (var i = 0; i < markers.length; i++) {
		markers[i].setMap(null);
		markers[i] = null;
	}
	for (var i = 0; i < infoWindows.length; i++) {
		infoWindows[i].setMap(null);
		infoWindows[i] = null;
	}
	infoWindows = [];
	markers = [];
	currentInfoWindow = null;
}

function makeMarkers(route) {
	function makeMarker(position, url) {
		var m = new google.maps.Marker({
			position: position,
			icon: url,
			map: map
		});
		placeService.nearbySearch({
			location: position,
			radius: 100,
			type: ['point_of_interest']
		}, function (data) {
			if (data.length > 0) {
				placeService.getDetails({
					placeId: data[0].place_id
				}, function(placeData) {
					var img = "";
					if (placeData.photos && placeData.photos.length > 0) {
						img = placeData.photos[0].getUrl({'maxWidth': 150, 'maxHeight': 150});
					}
					var infoWindow = new google.maps.InfoWindow({
						content: makeInfoWindowContent(
							placeData.name, 
							placeData.formatted_address, 
							placeData.formatted_phone_number, 
							img
						)
					});
					infoWindows.push(infoWindow);
					m.addListener('click', function() {
						if (currentInfoWindow) currentInfoWindow.close();
						infoWindow.open(map, m);
						currentInfoWindow = infoWindow;
					});
				});
			} else {
				var infoWindow = new google.maps.InfoWindow({
					content: "No data available."
				});
				m.addListener('click', function() {
					infoWindow.open(map, m);
				})
				infoWindows.push(infoWindow);
			}
		});
		markers.push(m);
	}
	
	eraseMarkers();
	var endPoint = route.legs[route.legs.length - 1].end_location;
	var waypointLocations = [];
	if (route.legs.length > 1) {
		for (var i = 1; i < route.legs.length; i++) {
			waypointLocations.push(route.legs[i].start_location);
		}
	}
	
	var iconURLs = {
		end: window.location.origin + "/img/icons/end.png",
		waypoint: window.location.origin + "/img/icons/waypoint.png",
	}
	
	makeMarker(endPoint, iconURLs.end);
	for (var i = 0; i < waypointLocations.length; i++) {
		makeMarker(waypointLocations[i], iconURLs.waypoint);
	}
}

function showDirections() {
	if (map && directionsService) {
		var cegepPos = new google.maps.LatLng(46.816695, -71.1516221);
		if (!lastPosition) {
			lastPosition = cegepPos;
		}
		var waypoints = [];
		for (var i = 0; i < clients.length; i++) {
			if (clients[i].complete == 0) {
				waypoints.push({location: new google.maps.LatLng(clients[i].position.lat, clients[i].position.lng)});
			}
		}
		var request = {
			origin: lastPosition,
			destination: cegepPos,
			waypoints: waypoints,
			optimizeWaypoints: true,
			travelMode: 'DRIVING'
		};
		directionsService.route(request, function(response, status) {
			if (status == 'OK') {
				directionsDisplay.setDirections(response);
				console.log(response);
				var waypoint_order = response.routes[0].waypoint_order;
				for (var i = 0; i < waypoint_order.length; i++) {
					clients[i].order = waypoint_order[i];
					clients[i].letter = String.fromCharCode(66 + waypoint_order[i]);
				}
				makeMarkers(response.routes[0]);
				
			}
			else {
				console.log(response);
			}
		});
	}
}

