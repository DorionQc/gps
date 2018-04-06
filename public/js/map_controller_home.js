var directionsService;
var placeService;
var lastId = 0;
var vehicleData = [];
var followedSessionId = null;
var directionsService;
var directionsDisplay;
var placeService;
var currentInfoWindow;

function decode(encoded){

    // array that holds the points
    var points=[];
    var index = 0, len = encoded.length;
    var lat = 0, lng = 0;
    while (index < len) {
        var b, shift = 0, result = 0;
        
	    do {
	        b = encoded.charAt(index++).charCodeAt(0) - 63;//finds ascii                                                                                    //and substract it by 63
	        result |= (b & 0x1f) << shift;
	        shift += 5;
	    } while (b >= 0x20);
	
	
	    var dlat = ((result & 1) != 0 ? ~(result >> 1) : (result >> 1));
	    lat += dlat;
	    shift = 0;
	    result = 0;
	    do {
	        b = encoded.charAt(index++).charCodeAt(0) - 63;
	        result |= (b & 0x1f) << shift;
	        shift += 5;
	     } while (b >= 0x20);
	     var dlng = ((result & 1) != 0 ? ~(result >> 1) : (result >> 1));
	     lng += dlng;
	     
	     points.push(new google.maps.LatLng(lat / 1E5, lng / 1E5));
    }
    return points
}

function routeToPolyline(route) {
	var points = [];
	for (var i = 0; i < route.legs.length; i++) {
		var leg = route.legs[i];
		for (var j = 0; j < leg.steps.length; j++) {
			var step = leg.steps[j];
			points.push.apply(points, decode(step.encoded_lat_lngs));
		}
	}
	return points;
}

function isVisible(key) {
	return $("#" + key + "_visible_cb").prop("checked") || $("#show_all_vehicles_checkbox").prop("checked");
}


function eraseMarkers(key) {
	if (vehicleData[key].directionMarkers) {
		for (var i = 0; i < vehicleData[key].directionMarkers.length; i++) {
			vehicleData[key].directionMarkers[i].setMap(null);
			vehicleData[key].directionMarkers[i] = null;
		}
	}
	if (vehicleData[key].infoWindows) {
		for (var i = 0; i < vehicleData[key].infoWindows.length; i++) {
			vehicleData[key].infoWindows[i].setMap(null);
			vehicleData[key].infoWindows[i] = null;
		}
	}
	vehicleData[key].directionMarkers = [];
	vehicleData[key].infoWindows = [];
}

function makeMarker(key, position, url) {
	var m = new google.maps.Marker({
		position: position,
		icon: url,
		map: isVisible(key) ? map : null
	});

	m.addListener('click', function() {
		
	
	placeService.nearbySearch({
		location: position,
		radius: 50,
		type: ['point_of_interest']
	}, function (data, a) {
		if (data && data.length > 0) {
			placeService.getDetails({
				placeId: data[0].place_id
			}, function(placeData, success) {
				if (!placeData) return;
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
				
				vehicleData[key].infoWindows.push(infoWindow);
				google.maps.event.clearListeners(m, 'click');
				m.addListener('click', function() {
					if (currentInfoWindow) 
						currentInfoWindow.close();
					infoWindow.open(map, m);
					currentInfoWindow = infoWindow;
				});
				if (currentInfoWindow) currentInfoWindow.close();
				infoWindow.open(map, m);
				currentInfoWindow = infoWindow;
			});
		} else {
			var infoWindow = new google.maps.InfoWindow({
				content: "No data available."
			});
			m.addListener('click', function() {
				infoWindow.open(map, m);
			})
			vehicleData[key].infoWindows.push(infoWindow);
		}
	});
	});
	vehicleData[key].directionMarkers.push(m);
}

function makeMarkers(key, route) {
	
	eraseMarkers(key);
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
	
	makeMarker(key, endPoint, iconURLs.end);
	for (var i = 0; i < waypointLocations.length; i++) {
		makeMarker(key, waypointLocations[i], iconURLs.waypoint);
	}
}


function updateDirections(key) {
	if (map && directionsService && vehicleData[key].commands) {
		var cegepPos = new google.maps.LatLng(46.816695, -71.1516221);
		var waypoints = [];
		var clients = vehicleData[key].commands;
		for (var i = 0; i < clients.length; i++) {
			waypoints.push({location: new google.maps.LatLng(clients[i].lat, clients[i].lng)});
		}
		var request = {
			origin: cegepPos,
			destination: cegepPos,
			waypoints: waypoints,
			optimizeWaypoints: true,
			travelMode: 'DRIVING'
		};
		directionsService.route(request, function(response, status) {
			if (status == 'OK') {
				if (vehicleData[key].directionsPolyline) {
					vehicleData[key].directionsPolyline.setMap(null);
					vehicleData[key].directionsPolyline = null;
					eraseMarkers(key);
				}
				var color = getColor(key);
				color[1] = 0.4;
				vehicleData[key].directionsPolyline = new google.maps.Polyline({
			        strokeColor: hslToRgb(color),
			        strokeOpacity: 1.0,
			        strokeWeight: 3,
			        path: routeToPolyline(response.routes[0]),
			        map: isVisible(key) ? map : null
			    });
				makeMarkers(key, response.routes[0]);
			}
			else {
				console.log(response);
			}
		});
	}
}

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

function showVehicleInfo(id) {

    $("#vehicle_textbox").val(vehicleData[id].name);
    $("#name_textbox").val(vehicleData[id].driverName);
    $("#phone_textbox").val(vehicleData[id].driverPhone);

    var commandsHtml = "";
    if (vehicleData[id].commands != null) {
        for (var i = 0; i < vehicleData[id].commands.length; i++) {
            var command = vehicleData[id].commands[i];
            commandsHtml += "<tr>" +
            		"<td>" + command.name + "</td>" +
            		"<td>" + command.date.split(" ")[0] + "</td>" +
            		"<td>" + command.itemCount + "</td>" +
            	"</tr>";
        }
    }
    $("#commands_body").html(commandsHtml);
    var pos;
    if (map && vehicleData[id].positions.length > 0) {
        pos = vehicleData[id].positions[vehicleData[id].positions.length - 1];
    }
    else 
    {
        // cegep pos
    	var pos = new google.maps.LatLng(46.816695, -71.1516221);
    }
    map.panTo(pos);
}

function hslToRgb(color){
    var r, g, b;
    var h = color[0], s = color[1], l = color[2];

    if(s == 0){
        r = g = b = l; // achromatic
    }else{
        var hue2rgb = function hue2rgb(p, q, t){
            if(t < 0) t += 1;
            if(t > 1) t -= 1;
            if(t < 1/6) return p + (q - p) * 6 * t;
            if(t < 1/2) return q;
            if(t < 2/3) return p + (q - p) * (2/3 - t) * 6;
            return p;
        }

        var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        var p = 2 * l - q;
        r = hue2rgb(p, q, h + 1/3);
        g = hue2rgb(p, q, h);
        b = hue2rgb(p, q, h - 1/3);
    }

    var c = [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
    return "#" + toHex(c[0]) + toHex(c[1]) + toHex(c[2]);
}

var col_div = 2;
var col_ind = -1;

function toHex(d) {
    return  ("0"+(Number(d).toString(16))).slice(-2).toUpperCase()
}

function makeColor() {
    col_ind += 2;
    if (col_ind > col_div) {
        col_div *= 2;
        col_ind = 1;
    }
    return [col_ind / col_div, 0.80, 0.5];
}

function getColor(key) {
	if (!vehicleData[key].color) {
		vehicleData[key].color = makeColor();
	}
	return vehicleData[key].color;
}

function makePolyline(key) {
    return new google.maps.Polyline({
        strokeColor: hslToRgb(getColor(key)),
        strokeOpacity: 1.0,
        strokeWeight: 3,
        path: vehicleData[key].positions,
        map: isVisible(key) ? map : null
    });
}

function updateMarker(key, pos) {
    if (!vehicleData[key].marker) {
    	vehicleData[key].marker = new google.maps.Marker({
			position: pos,
			icon: window.location.origin + "/img/icons/truck.png",
			map: isVisible(key) ? map : null
		});
		vehicleData[key].marker.addListener('click', function() {
			followedSessionId = key;
            showVehicleInfo(key);
		});
	} else {
		vehicleData[key].marker.setPosition(pos);
	}
	if (key == followedSessionId) {
    	map.panTo(vehicleData[key].marker.position);
	}
}

function toGoogleLatLng(positions) {
    var latlngs = [];
    positions.forEach(function(a,b,c) {
        latlngs.push(new google.maps.LatLng(a.lat, a.lng));
    });
    return latlngs;
}

function makeVehiclesContent(key) {
    var htmlString = "";
    htmlString += "<div class=\"input-group\">"
        + "    <div class=\"input-group-button\"><input type=\"checkbox\" class=\"checkbox show_vehicle_checkbox\" id=\"" + key + "_visible_cb\" data-id=\"" + key + "\" /></div>"
        + "    <div style=\"padding-left:8px; padding-right: 8px;\" class=\"input-group-button\">"
        + "        <button type=\"button\" class=\"button pick_vehicle_button\" data-id=" + key + " > " + vehicleData[key].name + "</button></div>"
        + "    <div class=\"input-group-button\"><img src=\"" + window.location.origin + "/img/lights/" + (vehicleData[key].isOnTheRoad == 0 ? "red_light.png" : "green_light.png") + "\" ></div>"
        + "</div>";
    
    $("#vehiclesDiv").append(htmlString);

    $(".pick_vehicle_button").click(function() {
        var id = $(this).data().id;
        followedSessionId = id;
        showVehicleInfo(id);
    });
    
    $(".show_vehicle_checkbox").click(function() {
    	var visible = $(this).prop("checked");
    	var id = $(this).data().id;
    	if (vehicleData[id].directionMarkers) {
    		vehicleData[id].directionMarkers.forEach(function(a) {
    			a.setMap(visible || isVisible(key) ? map : null);
    		});
    	}
    	if (vehicleData[id].directionsPolyline) {
    		vehicleData[id].directionsPolyline.setMap(visible || isVisible(key) ? map : null);
    	}
    	if (vehicleData[id].marker) {
    		vehicleData[id].marker.setMap(visible || isVisible(key) ? map : null);
    	}
    	if (vehicleData[id].polyline) {
    		vehicleData[id].polyline.setMap(visible || isVisible(key) ? map : null);
    	}
    });
}

function compareCommands(com1, com2) {
    if (com1 == com2) 
        return true;

    if (com1.length != com2.length) 
        return false;

    var i = 0;
    while (i < com1.length && com1[i].clientId == com2[i].clientId && com1[i].lat == com2[i].lat && com1[i].lng == com2[i].lng)
        i++;

    return (i == com1.length) 
    
}

function updateStuff () {
	$.ajax({
    	url: "/controller/homeData",
    	type: 'GET',
    	data: {
    		lastId: lastId,
			_token: $('meta[name="csrf-token"]').attr('content')
    	},
    	success: function(dat) {
    		var data = JSON.parse(dat);
    		Object.keys(data).forEach(
            	function(key) {
                	if (data[key].positions[data[key].positions.length - 1].id > lastId)
                    	lastId = data[key].positions[data[key].positions.length - 1].id;
            	}
           	)
    		Object.keys(data).forEach(
            	function(key) {
            		data[key].positions = toGoogleLatLng(data[key].positions);
                	if (!vehicleData[key]) {
                    	vehicleData[key] = data[key];
                    	vehicleData[key].polyline = makePolyline(key); 
                    	updateDirections(key);
                    	makeVehiclesContent(key);
                	}
                    else
                    {
                		vehicleData[key].positions.push.apply(vehicleData[key].positions, data[key].positions);
                		vehicleData[key].polyline.setPath(vehicleData[key].positions);
                		updateMarker(key, vehicleData[key].positions[vehicleData[key].positions.length - 1]);
                		if (compareCommands(data[key].commands, vehicleData[key].commands) == false) {
                    		vehicleData[key].commands = data[key].commands;
                    		updateDirections(key);
                		}
                    }
    		    }
    		);
    	},
    	error: function(dat) {
    		console.log(dat);
    	}
    });
}

function mapReadyCallback() {
	directionsService = new google.maps.DirectionsService();
	placeService = new google.maps.places.PlacesService(map);
    setInterval(updateStuff, 4000);
    map.addListener('dragstart', function() {
    	followedSessionId = -1;
    });
    $.ajax({
		url: "/controller/homeData",
    	type: 'GET',
    	data: {
    		lastId: lastId,
			_token: $('meta[name="csrf-token"]').attr('content')
    	},
    	success: function(data) {
    		vehicleData = JSON.parse(data);
    		Object.keys(vehicleData).forEach(
                function(key) {
            		vehicleData[key].positions = toGoogleLatLng(vehicleData[key].positions);
                	vehicleData[key].polyline = makePolyline(key); 
            		updateMarker(key, vehicleData[key].positions[vehicleData[key].positions.length - 1]);
                    updateDirections(key);
            		makeVehiclesContent(key);
        		}
        	);
    	},
    	error: function(dat) {
    		console.log(dat);
    	}
    });
    
    $("#show_all_vehicles_checkbox").click(function() {
     	var visible = $(this).prop("checked");
    	Object.keys(vehicleData).forEach(
             function(key) {
             	if (vehicleData[key].directionMarkers) {
             		vehicleData[key].directionMarkers.forEach(function(a) {
             			a.setMap(visible || isVisible(key) ? map : null);
             		});
             	}
             	if (vehicleData[key].directionsPolyline) {
             		vehicleData[key].directionsPolyline.setMap(visible || isVisible(key) ? map : null);
             	}
             	if (vehicleData[key].marker) {
             		vehicleData[key].marker.setMap(visible || isVisible(key) ? map : null);
             	}
             	if (vehicleData[key].polyline) {
             		vehicleData[key].polyline.setMap(visible || isVisible(key) ? map : null);
             	}
        	}
        );
    });
}

function waitForMap() {
	if (!map) {
		setTimeout(waitForMap, 100);
	}
	else {
		mapReadyCallback();
	}
}

$(document).ready(function () {
	waitForMap();
});

