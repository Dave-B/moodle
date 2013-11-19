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
 * Map specific functions
 *
 * @package    local_map
 * @copyright  2013 David Balch, University of Oxford <david.balch@conted.ox.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_map_map {
	private $center;
	private $zoom;
	private $width;
	private $height;

	/**
	 * Construct map object with basic map layout settings
	 *
	 * @param array opts Map size, zoom, and center settings
	 * @return void
	 **/
	public function __construct($opts = null) {
		global $PAGE;

		$this->center = isset($opts['center']) ? $opts['center']: '[46.073, 8.437]';
		$this->zoom = isset($opts['zoom']) ? $opts['zoom']: 1;
		$this->width = isset($opts['width']) ? $opts['width']: '520px';
		$this->height = isset($opts['height']) ? $opts['height']: '350px';

		// Add YUI lib to page
		$PAGE->requires->yui_module('moodle-local_map-map', 'M.local_map.init');
	}

	/**
	 * Instantiate the map on the page
	 *
	 * @param array opts Map size, zoom, and center settings
	 * @return string HTML map container div
	 * @todo Probably ought to be a renderer (http://docs.moodle.org/dev/Output_renderers)
	 **/
	public function load($id) {
		global $PAGE;
		// Write map container HTML
		$html = '<div id="'.$id.'"';
		$html .= ' style="width: '.$this->width.'; height: '.$this->height.';"';
		$html .= '> </div>';

		// Add map load JS
		// TODO: Maybe move map loading into external JS, with init data inline
		$opts= '{center: '.$this->center.', zoom: '.$this->zoom.'}';
		$js = 'M.local_map.maps["'.$id.'"] = M.local_map.addmap("'.$id.'", '.$opts.');';
		$PAGE->requires->js_init_code($js, true);

		return $html;
	}

	/**
	 * Add single point (with popup) to a map specified by dom id
	 *
	 * @return void
	 * @todo Finish documenting this function
	 **/
	public function add_point($mapid, $point, $name) {
		global $PAGE;
		// Add map point
		// TODO: Maybe move map loading into external JS, with init data inline
		//  Ref: $PAGE->requires->js_init_call()
		$js = 'var '.$name.'; Y.on("domready", function () {
			'.$name.' = L.marker(['.$point["lat"].', '.$point["long"].']).addTo(M.local_map.maps["'.$mapid.'"]);
		});';
		$PAGE->requires->js_init_code($js);
		return;
	}

	/**
	 * Add geoJson data to a map specified by dom id
	 *
	 * @return void
	 * @todo Finish documenting this function
	 **/
	public function add_geojson($mapid, $geoJson) {
		global $PAGE;
		// Add map geojson
		// TODO: Maybe move map loading into external JS, with init data inline
		//  Ref: $PAGE->requires->js_init_call()
		$js = 'Y.on("domready", function () {
			L.geoJson('.$geoJson.', {
			onEachFeature: function (feature, layer) {
				layer.bindPopup(feature.properties.name);
			}}).addTo(M.local_map.maps["'.$mapid.'"]);
		});';
		$PAGE->requires->js_init_code($js);
	}

	/**
	 * Make a map receive a marker via a user clicking on the map, put latlong into fields
	 *
	 * @return void
	 * @todo Finish documenting this function
	 **/
	public function receive_marker($mapid, $existingmarker) {
		global $PAGE;
		// Add map geojson
		// TODO: Maybe move map loading into external JS, with init data inline
		//  Ref: $PAGE->requires->js_init_call()
		$removeexisting = $existingmarker ? 'if(typeof '.$existingmarker.' !== "undefined"){M.local_map.maps["'.$mapid.'"].removeLayer('.$existingmarker.');}' : '';
		$js = 'var editmarker = null;
			Y.on("domready", function () {
			M.local_map.maps["'.$mapid.'"].on("click", function(e) {
				if (editmarker) {
					M.local_map.maps["'.$mapid.'"].removeLayer(editmarker);
				} else {'.
				$removeexisting.
				'}
				editmarker = L.marker(e.latlng).addTo(M.local_map.maps["'.$mapid.'"]);
				Y.one("input.field_lat").set("value", e.latlng.lat);
				Y.one("input.field_long").set("value", e.latlng.lng);
				M.local_map.reversegeocode(e.latlng.lat, e.latlng.lng, function(geo) {
					loc = geo.address.country
					if (geo.address.county) {
						loc = geo.address.county + ", " + loc;
					}
					if (geo.address.city) {
						loc = geo.address.city + ", " + loc;
					}
					Y.one("input.field_Area").set("value", loc);
				});
			});
		});';
		$PAGE->requires->js_init_code($js);
	}
}
