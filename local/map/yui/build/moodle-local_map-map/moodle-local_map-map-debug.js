YUI.add('moodle-local_map-map', function (Y, NAME) {

// Module moodle-local_map-map
M.local_map = M.local_map || {};
var NS = M.local_map;

NS.init = function(callback) {
    Y.log('moodle-local_map-map: start init');
    Y.Get.css('http://cdn.leafletjs.com/leaflet-0.6.4/leaflet.css', function (err) {
        if (err) {
            Y.log('Could not load leaflet.css', 'error', 'moodle-local_map-map');
        } else {
            Y.Get.js('http://cdn.leafletjs.com/leaflet-0.6.4/leaflet.js', function (err) {
                if (err) {
                    Y.log('Could not load leaflet.js', 'error', 'moodle-local_map-map');
                } else {
                    NS.L = L;
                    NS.maps = {};
                    Y.log('moodle-local_map-map: Leaflet initialised', 'info', 'moodle-local_map-map');
                    // In page, create callback containing `map = M.local_map.addmap(id, opts);`
                    if (callback) {
                        callback();
                    }
                }
            });
        }
    });
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
                    Y.log('reversegeocode: JSON Parse failed', 'error', 'moodle-local_map-map');
                    return;
                }
            },
            failure : function () {
                Y.log('reversegeocode: Lookup failed', 'error', 'moodle-local_map-map');
            }
        }
    });
};


}, '@VERSION@', {"requires": ["get", "io", "json-parse"]});
