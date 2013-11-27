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
    public $zoommin;
    public $zoommax;
    public $apikey;

    public function __construct($name, $title, $url, $attribution, $zoommin = null, $zoommax = null, $apikey = null) {
        $this->name = $name;
        $this->title = $title;
        $this->url = $url;
        $this->attribution = $attribution;
        $this->zoommin = $zoommin;
        $this->zoommax = $zoommax;
        $this->apikey = $apikey;
    }
}

// OSM: http://wiki.openstreetmap.org/wiki/Tiles.
$alltileproviders['osm'] = new local_map_tile_provider(
    'osm', 'Road map',
    'http://{s}.tile.osm.org/{z}/{x}/{y}.png',
    '&copy; <a href=\"http://osm.org/copyright\">OpenStreetMap</a> contributors');

// Mapquest OSM: http://developer.mapquest.com/web/products/open/map.
$alltileproviders['mapquest_osm'] = new local_map_tile_provider(
    'mapquest_osm', 'Mapquest road map',
    'http://otile1.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.png',
    '&copy; <a href=\"http://osm.org/copyright\">OpenStreetMap</a> contributors');

// Mapquest satellite: http://developer.mapquest.com/web/products/open/map.
$alltileproviders['mapquest_arial'] = new local_map_tile_provider(
    'mapquest_arial', 'Satellite',
    'http://otile1.mqcdn.com/tiles/1.0.0/sat/{z}/{x}/{y}.png',
    'Portions Courtesy NASA/JPL-Caltech and U.S. Depart. of Agriculture, Farm Service Agencys',
    0, 11);

class local_map_map {
    private $domid;
    private $tileproviders;
    private $view;
    private $layers;
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

        if (gettype($layers) == 'object' && get_class($layers) == 'local_map_marker') {
            // Add single marker.
            $name = 'n'.clean_param($layers->title, PARAM_ALPHANUM);
            $this->add_layer(new local_map_layer('marker', $name, $layers->title, [$layers]));
        } else if (gettype($layers) == 'array') {
            // Add layers to map.
            foreach ($layers as $layer) {
                $this->add_layer($layer);
            }
        }
    }

    /**
     * Add layer to map object.
     *
     * @param object layer - local_map_layer
     * @return void
     **/
    public function add_layer($layer) {
        $this->layers[] = $layer;
    }

    /**
     * Add tileproviders to map object.
     *
     * @param array view optional settings for map: width, height, center, zoom.
     **/
    public function add_tilelayer($provider) {
        global $alltileproviders;
        $this->tileproviders[$provider] = $alltileproviders[$provider];
    }

    /**
     * Enable a map to receive a marker via a user clicking on the map.
     * Puts the lat long values into CSS selector spcified fields.
     * Puts the geolocation value into CSS selector spcified field.
     *
     * @param string markerid Id for new marker
     * @param string latdest CSS selector of desitnation element for latitude value
     * @param string lngdest CSS selector of desitnation element for longitude value
     * @param string existingmarker Id of an existing marker to remove
     * @param array reversegeocode CSS selector of desitnation element for reverse geocode, array of address components to return
     * @return void
     * @todo Finish documenting this function
     **/
    public function receive_marker($markerid,
                                   $latdest = 'input.field_lat', $lngdest = 'input.field_long',
                                   $existingid = null, $reversegeocode = null) {
        $rm = new stdClass();
        $rm->markerid = $markerid;
        $rm->latdest = $latdest;
        $rm->lngdest = $lngdest;
        $rm->existingid = $existingid;
        $rm->reversegeocode = $reversegeocode;

        $this->receivemarker = $rm;
    }

    /**
     * Render map - Adds javascript via $PAGE->requires->js_init_code(),
     * and returns HTML for the caller to add to the page output.
     * @return string HTML fragment
     **/
    public function render() {
        global $PAGE;

        $extramodules = '';
        if ($this->receivemarker) {
            $extramodules = ", 'event', 'node', 'io'";
        }
        $jsmapinitstart = "YUI({delayUntil: 'domready'}).use('moodle-local_map-map'".
                          $extramodules.", function(Y) {M.local_map.init(function() {";
        $jsmapinitend = '});});';
        $jsmapview = '{center: ['.$this->view->lat.','.$this->view->lng.'], zoom: '.$this->view->zoom.'}';
        $jsmap = 'M.local_map.maps["'.$this->domid.'"] = L.map("'.$this->domid.'", '.$jsmapview.');';
        $jstiles = '';
        foreach ($this->tileproviders as $provider) {
            $tileopts = 'attribution: "'.$provider->attribution.'"';
            if ($provider->zoommin) {
                $tileopts .= ', minZoom: "'.$provider->zoommin.'"';
            }
            if ($provider->zoommax) {
                $tileopts .= ', maxZoom: "'.$provider->zoommax.'"';
            }
            $jstiles .= $provider->name." = L.tileLayer('".$provider->url."', {".$tileopts."})";
            $jstiles .= ".addTo(M.local_map.maps['".$this->domid."']);";
        }

        $jsmarkerlayers = '';
        $jsgeojsonlayers = '';
        if ($this->layers) {
            foreach ($this->layers as $layer) {
                if ($layer->type == 'marker') {
                    // Add markers (in layer groups).
                    $jslayergroup = 'var '.$layer->name.' = L.layerGroup([';
                    foreach ($layer->data as $marker) {
                        $attribs = $marker->title ? 'title: "'.$marker->title.'",' : '';
                        $attribs = $attribs != '' ? ', {'.$attribs.'}' : '';
                        if ($marker->content && $marker->contentmode == 'popup') {
                            $content = $marker->content ? '.bindPopup(\''.$marker->content.'\')' : '';
                        } else {
                            $content = '';
                        }
                        $jsmarkerlayers .= $marker->id.' = L.marker(['.$marker->lat.','.$marker->lng.']'.$attribs.')';
                        $jsmarkerlayers .= '.addTo(M.local_map.maps["'.$this->domid.'"])'.$content.';';
                        $jslayergroup .= $marker->id.', ';
                    }
                    $jslayergroup .= ']);';
                    $jsmarkerlayers .= $jslayergroup;
                } else if ($layer->type == 'geojson') {
                    // Add geoJSON layer.
                    // TODO: More flexible handling to expose geoJSON properties on the map.
                    $jsgeojsonlayers .= $layer->name.' = L.geoJson('.$layer->data.', {
onEachFeature: function (feature, layer) {
    layer.bindPopup(feature.properties.description);
}}).addTo(M.local_map.maps["'.$this->domid.'"]);';
                }
            }
        }

        $jsreceivemarker = '';
        if ($this->receivemarker) {
            if (isset($this->receivemarker->existingid)) {
                $usermarker = $this->receivemarker->existingid;
            } else {
                $usermarker = 'editmarker';
            }
            $jsreceivemarker = 'M.local_map.maps["'.$this->domid.'"].on("click", function(e) {
                if (typeof '.$usermarker.' !== "undefined") {
                    M.local_map.maps["'.$this->domid.'"].removeLayer('.$usermarker.');
                };
                '.$usermarker.' = L.marker(e.latlng).addTo(M.local_map.maps["'.$this->domid.'"]);
                Y.one("'.$this->receivemarker->latdest.'").set("value", e.latlng.lat);
                Y.one("'.$this->receivemarker->lngdest.'").set("value", e.latlng.lng);';

            if (true || isset($this->receivemarker->reversegeocode)) {
                $jsreceivemarker .= 'M.local_map.reversegeocode(e.latlng.lat, e.latlng.lng, function(geo) {
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
            $jsreceivemarker .= '});';
        }

        $activecontrols = '';
        $jscontrols = '';
        if (count($this->tileproviders) > 1) {
            // Multiple map styles.
            $activecontrols = 'basemaps';
            $jscontrols = 'var basemaps = {';
            foreach ($this->tileproviders as $provider) {
                $jscontrols .= '"'.$provider->title.'": '.$provider->name.',';
            }
            $jscontrols .= '};';
        }
        if (count($this->layers) > 1) {
            // Multiple marker layers.
            $layerlist = '';
            foreach ($this->layers as $layer) {
                if ($layer->showcontrols) {
                    $layerlist .= '"'.$layer->title.'": '.$layer->name.',';
                }
            }
            if ($layerlist != '') {
                if ($activecontrols = '') {
                    $activecontrols = 'null, overlaymaps';
                } else {
                    $activecontrols = 'basemaps, overlaymaps';
                }
                $jscontrols .= 'var overlaymaps = {'.$layerlist.'};';
            }
        }
        if ($activecontrols) {
            $jscontrols .= 'L.control.layers('.$activecontrols.').addTo(M.local_map.maps["'.$this->domid.'"]);';
        }

        // Output JS.
        // TODO: Prepare settings for inclusion in M.cfg (or similar),
        // to be picked up in M.local_map.init(), instead of using js_init_code().
        $js = $jsmapinitstart . $jsmap . $jstiles .
              $jsgeojsonlayers . $jsmarkerlayers .
              $jsreceivemarker . $jscontrols . $jsmapinitend;
        $PAGE->requires->js_init_code($js);

        // Write map container HTML.
        $html = '<div id="'.$this->domid.'"';
        $html .= ' style="width: '.$this->view->width.'; height: '.$this->view->height.';"';
        $html .= '> </div>';

        return $html;
    }
}

class local_map_layer {
    public $type;
    public $name;
    public $title;
    public $data;
    public $showcontrols;

    /**
     * Construct layer object
     *
     * @param string type of layer, i.e. marker, geojson
     * @param string name of layer, used in JS
     * @param string title of layer for display
     * @param array|string data markers, or geojson string
     * @param boolean showcontrols Show UI for layer visibility
     * @return void
     **/
    public function __construct($type, $name, $title, $data, $showcontrols = true) {
        $this->type = $type;
        $this->name = $name;
        $this->title = $title;
        $this->data = $data;
        $this->showcontrols = $showcontrols;
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
