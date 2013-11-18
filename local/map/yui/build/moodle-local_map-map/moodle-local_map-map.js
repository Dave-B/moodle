YUI.add('moodle-local_map-map', function (Y, NAME) {

// Module moodle-local_map-map
M.local_map = M.local_map || {};
var NS = M.local_map;

NS.init = function(callback) {
    Y.Get.css('http://cdn.leafletjs.com/leaflet-0.6.4/leaflet.css', function (err) {
        if (err) {
        } else {
            Y.Get.js('http://cdn.leafletjs.com/leaflet-0.6.4/leaflet.js', function (err) {
                if (err) {
                } else {
                    NS.L = L;
                    NS.maps = {};
                    // In page, create callback containing `map = M.local_map.addmap(id, opts);`
                    if (callback) {
                        callback();
                    }
                }
            });
        }
    });
},
NS.addmap = function(targetid, opts) {
    L = NS.L;
    // Create a map in the "map" div, set the view to a given place and zoom
    opts = opts || {center: [0, 0], zoom: 0};
    var map = L.map(targetid, opts);

    // Add an OpenStreetMap tile layer
    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    return map;
},
NS.reversegeocode = function(lat, lon, callback_apply) {
    Y.io('http://nominatim.openstreetmap.org/reverse?format=json&lat='+lat+'&lon='+lon, {
        on : {
            success : function (tx, r) {
                var parsedResponse;
                try {
                    parsedResponse = Y.JSON.parse(r.responseText);
                    callback_apply(parsedResponse);
                }
                catch (e) {
                    return;
                }
            },
            failure : function () {
            }
        }
    });
};


}, '@VERSION@', {"requires": ["get"]});
