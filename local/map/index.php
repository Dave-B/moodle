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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    local_map
 * @copyright  2013 David Balch, University of Oxford <david.balch@conted.ox.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php'); // Maps lib

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/mod/map/index.php');
$PAGE->set_title(get_string('maptest', 'local_map'));
$PAGE->set_heading(get_string('maptest', 'local_map'));

echo $OUTPUT->header();

// TODO: Draw map - php + HTML, or JS + HTML?
// TODO: Put map drawing into locallib.php function
// TODO: Create Database Tag using function
// TODO: Add Layer control settings

echo map_create(['id' => 'geomap', 'style' => 'width: 300px; height: 200px;']);
$geo = <<<EOT
[{
    "type": "Feature",
    "properties": {"party": "Republican"},
    "geometry": {
        "type": "Polygon",
        "coordinates": [[
            [-104.05, 48.99],
            [-97.22,  48.98],
            [-96.58,  45.94],
            [-104.03, 45.94],
            [-104.05, 48.99]
        ]]
    }
}, {
    "type": "Feature",
    "properties": {"party": "Democrat"},
    "geometry": {
        "type": "Polygon",
        "coordinates": [[
            [-109.05, 41.00],
            [-102.06, 40.99],
            [-102.03, 36.99],
            [-109.04, 36.99],
            [-109.05, 41.00]
        ]]
    }
}]
EOT;
map_add_geojson('geomap', $geo);

/*
    L.geoJson(states, {
        style: function(feature) {
            switch (feature.properties.party) {
                case 'Republican': return {color: "#ff0000"};
                case 'Democrat':   return {color: "#0000ff"};
            }
        }
    }).addTo(map);
};
*/


$mapopts = '{center: [51.5, -0.09], zoom: 3}';
echo map_create(['id' =>'mymap', 'style' => 'width: 300px; height: 200px;'], $mapopts);



echo $OUTPUT->footer();
