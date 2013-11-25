<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Map instantiation class.
 *
 * @package    local_map
 * @copyright  2013 David Balch, University of Oxford <david.balch@conted.ox.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
class local_map_tile_provider {
    public $name;
    public $title;
    public $url;
    public $attribution;
    public $apikey = null;

    public function __construct($name, $title, $url, $attribution, $apikey = null) {
        $this->name = $name;
        $this->title = $title;
        $this->url = $url;
        $this->attribution = $attribution;
        $this->apikey = $apikey;
    }
}

// OSM: http://wiki.openstreetmap.org/wiki/Tiles
$alltileproviders['osm'] = new local_map_tile_provider(
    'osm', 'Road map',
    'http://{s}.tile.osm.org/{z}/{x}/{y}.png',
    '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors');

// Mapquest: http://developer.mapquest.com/web/products/open/map
$alltileproviders['mapquest_arial'] = new local_map_tile_provider(
    'mapquest_arial', 'Satellite',
    'http://otile1.mqcdn.com/tiles/1.0.0/sat/{z}/{x}/{y}.png',
    'Portions Courtesy NASA/JPL-Caltech and U.S. Depart. of Agriculture, Farm Service Agencys');

class local_map_map {
    private $domid;
    private $tileproviders;
    private $view;
    private $markerlayers;
    private $geojsonlayers;
    private $receivemarker;

    /**
     * Construct map object
     *
     * @param string domid for the map container.
     * @param array layers optional layer objects to add to the map. Layer types: 'marker' (pins and popups), 'geojson'.
     * @param array view optional setting for map: width, height, center, zoom.
     * @param array tileproviders names of tileproviders to use.
     * @return void
     **/
    public function __construct($domid, $layers = null, $view = null, $tileproviders = ['osm']) {
        global $PAGE;

        $this->domid = $domid;
        if ($view) {
            $this->view = $view;
        } else {
            $this->view = new local_map_view();
        }

        foreach ($tileproviders as $provider) {
            $this->add_tilelayer($provider);
        }

        if ($layers) {
            // Add layers to map
            foreach ($layers as $layer) {
                $add_layer = 'add_layer_'.$layer->type;
                $this->$add_layer($layer->data);
            }
        }
    }

    /**
     * Add geojson layer to map object
     *
     * @param string geojson data
     * @return void
     **/
    public function add_layer_geojson($data) {
        $this->geojsonlayers[] = $data;
    }

    /**
     * Add geojson layer to map object
     *
     * @param array marker data
     * @return void
     **/
    public function add_layer_marker($data) {
        $this->markerlayers[] = $data;
    }

    /**
     * Add tileproviders to map object
     *
     * @param array view optional settings for map: width, height, center, zoom.
     **/
    public function add_tilelayer($provider) {
        global $alltileproviders;
        $this->tileproviders[$provider] = $alltileproviders[$provider];
    }

    /**
     * Enable a map to receive a marker via a user clicking on the map.
     * Put the lat long values into CSS selector spcified fields
     *
     * @param string markerid Id for new marker
     * @param string latdest CSS selector of desitnation element for latitude value
     * @param string lngdest CSS selector of desitnation element for longitude value
     * @param string existingmarker Id of an existing marker to remove
     * @param array reversegeocode CSS selector of desitnation element for reverse geocode, array of address components to return
     * @return void
     * @todo Finish documenting this function
     **/
    public function receive_marker($markerid, $latdest = 'input.field_lat', $lngdest = 'input.field_long', $existingid = null, $reversegeocode = null) {
		$rm = new stdClass();
		$rm->markerid = $markerid;
		$rm->latdest = $latdest;
		$rm->lngdest = $lngdest;
		$rm->existingid = $existingid;
		$rm->reversegeocode = $reversegeocode;

		$this->receivemarker = $rm;
    }

    /**
     * Render map
     * @return string HTML fragment
     **/
    public function render() {
        global $PAGE;
		//print_object($this);

        $extramodules = '';
        if ($this->receivemarker) {
			$extramodules = ", 'event', 'node', 'io'";
		}
        $js_map_init_start = "YUI({delayUntil: 'domready'}).use('moodle-local_map-map'".$extramodules.", function(Y) {M.local_map.init(function() {";
        $js_map_init_end = '});});';
        $js_map_view = '{center: ['.$this->view->lat.','.$this->view->lng.'], zoom: '.$this->view->zoom.'}';
        //$js_map = 'M.local_map.maps["'.$this->domid.'"] = M.local_map.addmap("'.$this->domid.'", '.$js_map_view.');';
        $js_map = 'M.local_map.maps["'.$this->domid.'"] = L.map("'.$this->domid.'", '.$js_map_view.');';
        $js_tiles = '';
        foreach ($this->tileproviders as $provider) {
            $js_tiles .= $provider->name." = L.tileLayer('".$provider->url."', {attribution: '".$provider->attribution."'}).addTo(M.local_map.maps['".$this->domid."']);";
        }

        $js_geojsonlayers = '';
        if ($this->geojsonlayers) {
            foreach ($this->geojsonlayers as $geojson) {
                $js_geojsonlayers .= 'L.geoJson('.$geojson.', {
onEachFeature: function (feature, layer) {
    layer.bindPopup(feature.properties.description);
}}).addTo(M.local_map.maps["'.$this->domid.'"]);';
                // TODO: tooltip titles ~ layer.title = feature.properties.name
            }
        }

        $js_markerlayers = '';
        if ($this->markerlayers) {
            foreach ($this->markerlayers as $markers) {
                // TODO: Put layers in groups
                //$js_add_markerlayers .= 'L.marker([50.5, 30.5]).addTo(M.local_map.maps["'.$this->domid.'"]);';
                foreach ($markers as $marker) {
                    $attribs = $marker->title ? 'title: "'.$marker->title.'",' : '';
                    $attribs = $attribs != '' ? ', {'.$attribs.'}' : '';
                    if ($marker->content && $marker->contentmode == 'popup') {
						$content = $marker->content ? '.bindPopup(\''.$marker->content.'\')' : '';
					} else {
						$content = '';
					}
                    $js_markerlayers .= $marker->id.' = L.marker(['.$marker->lat.','.$marker->lng.']'.$attribs.').addTo(M.local_map.maps["'.$this->domid.'"])'.$content.';';
                }
            }
        }

        $js_receivemarker = '';
        if ($this->receivemarker) {
			if (isset($this->receivemarker->existingid)) {
				$usermarker = $this->receivemarker->existingid;
			} else {
				$usermarker = 'editmarker';
			}
			$js_receivemarker = 'M.local_map.maps["'.$this->domid.'"].on("click", function(e) {
				if (typeof '.$usermarker.' !== "undefined") {
					M.local_map.maps["'.$this->domid.'"].removeLayer('.$usermarker.');
				};
                '.$usermarker.' = L.marker(e.latlng).addTo(M.local_map.maps["'.$this->domid.'"]);
                Y.one("'.$this->receivemarker->latdest.'").set("value", e.latlng.lat);
                Y.one("'.$this->receivemarker->lngdest.'").set("value", e.latlng.lng);';

			if (true || isset($this->receivemarker->reversegeocode)) {
				$js_receivemarker .= 'M.local_map.reversegeocode(e.latlng.lat, e.latlng.lng, function(geo) {
                    loc = geo.address.country
                    if (geo.address.county) {
                        loc = geo.address.county + ", " + loc;
                    }
                    if (geo.address.city) {
                        loc = geo.address.city + ", " + loc;
                    }
                    Y.one("input.field_Area").set("value", loc);
                });';
			}
			$js_receivemarker .= '});';
		}

        $js_controls = '';
        if (count($this->tileproviders) > 1) {
            $js_controls = 'var basemaps = {';
            foreach ($this->tileproviders as $provider) {
                $js_controls .= '"'.$provider->title.'": '.$provider->name.',';
            }
            $js_controls .= '};L.control.layers(basemaps).addTo(M.local_map.maps["'.$this->domid.'"]);';
        }

		// Output JS
        // TODO: Prepare settings for inclusion in M.cfg (or similar), to be picked up in M.local_map.init(), instead of using js_init_code().
        $js = $js_map_init_start . $js_map . $js_tiles . $js_geojsonlayers . $js_markerlayers . $js_receivemarker . $js_controls . $js_map_init_end;
        $PAGE->requires->js_init_code($js);

        // Write map container HTML
        $html = '<div id="'.$this->domid.'"';
        $html .= ' style="width: '.$this->view->width.'; height: '.$this->view->height.';"';
        $html .= '> </div>';

        return $html;
    }
}

class local_map_layer {
	public $type;
	public $data;

    /**
     * Construct layer object
     *
     * @param string type of layer, i.e. marker, geojson
     * @param array|string markers, or geojson string
     * @return void
     **/
    public function __construct($type, $data) {
        $this->type = $type;
        $this->data = $data;
    }
}

class local_map_marker {
    public $id;
    public $lat;
    public $lng;
    public $title;
    public $content;
    public $contentmode;

    /**
     * Construct marker object
     *
     * @param string id Marker id, enabling manipulation via javascript
     * @param float lat Marker latitude
     * @param float lng Marker longitude
     * @param string title Tooltip text
     * @param string content to be shown, e.g. in a popup
     * @param string contentmode to show content in, e.g. in a popup, or a separate div element.
     * @return void
     **/
    public function __construct($id, $lat, $lng, $title = null, $content = null, $contentmode = 'popup') {
        $this->id = $id;
        $this->lat = $lat;
        $this->lng = $lng;
		$this->title = $title;
		$this->content = $content;
		$this->contentmode = $contentmode;
    }
}

class local_map_view {
    public $lat;
    public $lng;
    public $zoom;
    public $width;
    public $height;

    /**
     * Construct view object, to define the map size on page, and area shown.
     *
     * @param float lat Map center latitude
     * @param float lng Map center longitude
     * @param int zoom map zoom value
     * @param string width HTML map container width
     * @param string heightHTML map container height
     * @return void
     **/
    public function __construct($lat = null, $lng = null, $zoom = null, $width = null, $height = null) {
        // TODO: Get default values from settings in database.
        $this->lat = $lat ? $lat : 46.073;
        $this->lng = $lng ? $lng : 8.437;
        $this->zoom = $zoom ? $zoom : 1;
        $this->width = $width ? $width : '520px';
        $this->height = $height ? $height : '350px';
    }
}
