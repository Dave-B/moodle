//moodle-local_map-map

M.local_map = M.local_map || {};
var NS = M.local_map.map = {};
//var L = M.local_map.leaflet.L;

NS.init = function() {
    Y.Get.css('http://cdn.leafletjs.com/leaflet-0.6.4/leaflet.css', function (err) {
        if (err) {
            Y.log(err, 'error', 'leaflet-for-yui');
            return;
        } else {
            Y.Get.js('http://cdn.leafletjs.com/leaflet-0.6.4/leaflet.js', function (err) {
                if (err) {
                    Y.log(error, 'error', 'leaflet-for-yui');
                    return;
                }
                Y.log('moodle-local-map-leaflet: Leaflet initialised');
                NS.L = L;
                NS.maps = {};
                // On page, use `map = M.local_map.map.addmap(id, opts);`
            });
        }
    });
},
NS.addmap = function(targetid, opts) {
    // Create a map in the "map" div, set the view to a given place and zoom
    opts = opts || {center: [0, 0], zoom: 0};
    var map = L.map(targetid, opts);

    // Add an OpenStreetMap tile layer
    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    return map;
},
NS.addGeoJsonMarkers = function(map, geoJson) {
    console.log("hi");
};
