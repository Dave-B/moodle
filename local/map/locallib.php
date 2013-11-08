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

    if ($mapoptions == null) {
        $mapoptions = '';
        $mapchain = '.fitWorld()';
    } else {
        $mapoptions = ', '.$mapoptions;
        $mapchain = '';
    }

    // Add map load JS
    // TODO: Maybe move map loading into external JS, with init data inline
    $js = 'Y.on("domready", function () { M.local_map.map.maps["'.$htmlattribs['id'].'"] = M.local_map.map.addmap("'.$htmlattribs['id'].'"'.$mapoptions.')'.$mapchain.';});';
    $PAGE->requires->js_init_code($js);

    return $html;
}

/**
 * Add single point (with popup) to a map specified by dom id
 *
 * @return void
 * @todo Finish documenting this function
 **/
function map_add_point ($mapid, $point) {
    global $PAGE;
    // Add map point
    // TODO: Maybe move map loading into external JS, with init data inline
    //  Ref: $PAGE->requires->js_init_call()
    $js = 'Y.on("domready", function () {
        L.marker(['.$point["lat"].', '.$point["long"].']).addTo(M.local_map.map.maps["'.$mapid.'"]).bindPopup("'.$point["name"].'");
    });';
    $PAGE->requires->js_init_code($js);
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
    //  Ref: $PAGE->requires->js_init_call()
    $js = 'Y.on("domready", function () {
        L.geoJson('.$geoJson.', {
        onEachFeature: function (feature, layer) {
            layer.bindPopup(feature.properties.name);
        }}).addTo(M.local_map.map.maps["'.$mapid.'"]);
    });';
    $PAGE->requires->js_init_code($js);
}

/**
 * Make a map receive markers, put latlong into fields
 *
 * @return void
 * @todo Finish documenting this function
 **/
function map_receive_markers ($mapid) {
    global $PAGE;
    // Add map geojson
    // TODO: Maybe move map loading into external JS, with init data inline
    //  Ref: $PAGE->requires->js_init_call()
    $js = 'var editmarker = null;Y.on("domready", function () {
        M.local_map.map.maps["'.$mapid.'"].on("click", function(e) {
            if (editmarker) {
                M.local_map.map.maps["'.$mapid.'"].removeLayer(editmarker);
            }
            editmarker = L.marker(e.latlng).addTo(M.local_map.map.maps["'.$mapid.'"]);
            Y.one(".field_lat").set("value", e.latlng.lat);
            Y.one(".field_long").set("value", e.latlng.lng);
        });
    });';
    $PAGE->requires->js_init_code($js);
}

/* = Database templates =

== List header ==
<div id="datamap" style="width: 640px; height: 320px;">Loading map...</div>
<table id="mod-data-map-template"><thead><tr>
<th>Delete</th>
<th>Title</th>
<th>Location</th>
<th>Description</th>
<th>Controls</th>
</tr></thead><tbody>


== List repeated entry ==
<tr class="data-entry">
<td class="template-field cell c0 firstcol">##delcheck##</td>
<td class="template-token cell c1">[[Title]]</td>
<td class="template-token cell c2">[[Location]]</td>
<td class="template-token cell c3 lastcol">[[Description]]</td>
<td class="controls template-field cell c4 lastcol">##edit##  ##more##  ##delete##  ##approve##  ##disapprove##  ##export##</td>
</tr>

== List footer ==
</tbody></table>


== Single template ==
<div class="defaulttemplate">
<h3>[[Title]]</h3>
<table class="mod-data-default-template">
<tbody>
<tr class="data-entry">
<td class="template-token cell c0 firstcol"[[Location]]</td>
<td class="template-token cell c1">[[Description]]</td>
<td class="controls template-field cell c2 lastcol">##edit##  ##more##  ##delete##  ##approve##  ##disapprove##  ##export##</td>
</tr>
</tbody>
</table>
</div>



== javascript ==

Y.on("domready", function () {
    if (Y.one("#datamap")) {
        // Init map
        M.local_map.map.maps["datamap"] = M.local_map.map.addmap("datamap", {center: [58.14288114185, -7.2773426771164], zoom: 1});
        // Add entries
        Y.all("#mod-data-map-template .data-entry").each(function (taskNode) {
            var title = taskNode.getElementsByTagName('td').item(1).get('innerHTML');
            var coords = taskNode.getElementsByTagName('td').item(2).get('innerHTML').split(',');
            var desc = taskNode.getElementsByTagName('td').item(3).get('innerHTML');
            var popuptext = '<h3>'+title+'</h3><div>'+desc+'</div>';
            var boxtext = '<div class ="box generalbox">'+popuptext+'</div>';
            L.marker([coords[0], coords[1]], {title:title}).addTo(M.local_map.map.maps["datamap"]).on('click', function(e) {
                Y.one("#datapanel").set('innerHTML', boxtext);
            });
        });
        // Hide table and buttons
        Y.one("#datapanel").insert('<div id="datapanelvis"><input id="showdata" type="button" value="Show data table" /><input id="hidedata" type="button" value="Hide data table" />', 'after');
        var dataelements = Y.all('#mod-data-map-template, #hidedata, #checkall, #checknone, input.form-submit');
        var notdataelements = Y.one('#showdata');
        Y.one('#hidedata').on('click', function() {dataelements.hide();notdataelements.show()});
        Y.one('#showdata').on('click', function() {dataelements.show();notdataelements.hide()});
        dataelements.hide();notdataelements.show()
    }
});


*/
