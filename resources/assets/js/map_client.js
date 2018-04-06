var marker = null;

function setPosition(pos, map) {
	map.panTo(pos);
	$("#lat").val(pos.lat);
	$("#lng").val(pos.lng);
}

function makeMarker(pos, map) {
	var m = new google.maps.Marker({
		position: pos,
		map: map,
		draggable: true
	});
	m.addListener('dragend', function(e) {
		setPosition(e.latLng, map);
	});
	return m;
}

function sleep(time) {
	  return new Promise((resolve) => setTimeout(resolve, time));
}

function mapReadyCallback() {
	map.addListener('click', function (e) {
		if (marker === null) {
			marker = makeMarker(e.latLng, map);
		} else {
			marker.setPosition(e.latLng);
		}
		setPosition(e.latLng, map);
	});

	if ($('#lat').val() && $('#lng').val()) {
		map_init_position = { lat: parseFloat($("#lat").val()), lng: parseFloat($("#lng").val()) };
		marker = makeMarker(map_init_position, map);
		map.panTo(map_init_position);
		console.log(map_init_position);
	}
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


