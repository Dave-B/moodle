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
// Add YUI lib to page
$PAGE->requires->yui_module('moodle-local_map-map', 'M.local_map.map.init');

/**
 * Add a map to the page
 *
 * @return string HTML map container div
 * @todo Finish documenting this function
 **/
function map_create ($htmlattribs, $mapoptions = null) {
    global $PAGE;
    // Write map container HTML
    $html = '<div';
    foreach ($htmlattribs as $key => $val) {
        $html .= ' '.$key.'="'.$val.'"';
    }
    $html .= '> </div>';

    $mapoptions = $mapoptions ? ', '.$mapoptions : '';

    // Add map load JS
    // TODO: Maybe move map loading into external JS, with init data inline
    $js = 'Y.on("domready", function () { M.local_map.map.maps["'.$htmlattribs['id'].'"] = M.local_map.map.addmap("'.$htmlattribs['id'].'"'.$mapoptions.');});';
    $PAGE->requires->js_init_code($js);

    return $html;
}

/**
 * Add geoJson data to a map specified by dom id
 *
 * @return void
 * @todo Finish documenting this function
 **/
function map_add_geojson ($mapid, $geoJson) {
    global $PAGE;
    // Add map geojson
    // TODO: Maybe move map loading into external JS, with init data inline
    $js = 'Y.on("domready", function () {
        L.geoJson('.$geoJson.', {
        onEachFeature: function (feature, layer) {
            layer.bindPopup(feature.properties.name);
        }}).addTo(M.local_map.map.maps["'.$mapid.'"]);
    });';
    $PAGE->requires->js_init_code($js);
    /*
    L.geoJson(data, {
        style: function (feature) {
            return {color: feature.properties.color};
        },
        onEachFeature: function (feature, layer) {
            layer.bindPopup(feature.properties.description);
        }
    }).addTo(map);

    */
}
