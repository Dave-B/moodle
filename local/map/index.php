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
require_once('locallib.php'); // Maps lib

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/mod/map/index.php');
$PAGE->set_title(get_string('maptest', 'local_map'));
$PAGE->set_heading(get_string('maptest', 'local_map'));

echo $OUTPUT->header();

echo '<h1>Map module examples</h1>';
echo '<p>Copy of <a href="iplookup.php">iplookup using local_map</a>.</p>';

if (get_config('local_map', 'usemaps')) {
    // Define markers/layers for later
    // Single markers
    $greenwich = new local_map_marker('greenwich', 51.48, 0, 'Greenwich', '<p>Greenwich is notable for its maritime history and for giving its name to the Greenwich Meridian (0° longitude) and Greenwich Mean Time.<br/> -- <a href="http://en.wikipedia.org/wiki/Greenwich">Wikipedia</a></p>');
    $stornoway = new local_map_marker('stornoway', 58.209890518505084, -6.390060422709212, 'Stornoway', '<p>Stornoway (/ˈstɔrnəweɪ/; Scottish Gaelic: Steòrnabhagh) is a town on the Isle of Lewis, in the Outer Hebrides of Scotland.<br/> -- <a href="http://en.wikipedia.org/wiki/Stornoway">Wikipedia</a></p>');

    // Group of markers
    $markers = new local_map_layer('marker', 'ukloc', 'UK locations', [$greenwich, $stornoway]);

    // geoJSON
    $geo = <<<EOT
[{"type":"Feature Collection","features":[
{"type":"Feature","id":"1","geometry":{"type":"Point","coordinates":[-1.588354,52.346163]},"properties":{"name":"Bishop’s Gate","description":"Elizabeth arrived on horseback on the evening of the 9 July 1575 at the Bishop’s Gate, where she was first admitted to the castle."}},
{"type":"Feature","id":"2","geometry":{"type":"Point","coordinates":[-1.590379,52.346254]},"properties":{"name":"Tiltyard Gate","description":"Here, at the Tiltyard Gate, Elizabeth was offered the keys to the castle. Beyond the gate, she moved on to the tiltyard, where jousts between mounted knights would have taken place later in the visit, watched by spectators in the towers at either end."}},
{"type":"Feature","id":"3","geometry":{"type":"Point","coordinates":[-1.591447,52.346415]},"properties":{"name":"The Mere","description":"Out on the Mere (an artificially flooded lake surrounding the castle), what appeared to be a ‘moving island’ came into view, carrying the Lady of the Lake, attended by two scantily clad nymphs."}},
{"type":"Feature","id":"4","geometry":{"type":"Point","coordinates":[-1.590674,52.346546]},"properties":{"name":"The tiltyard","description":"Waiting on the tiltyard, Elizabeth heard a speech from the Lady of the Lake: ‘Pass on Madame, you neede no longer stand, the Lake, the Lodge, the Lord, are yours for to commande’. Elizabeth’s response was short and to the point. She replied ‘We thought indeed the lake had been ours, and do you call it yours now? Well, we will herein commune [speak] more with you more hereafter’. The Lady and the nymphs, now revealed to be played by young men, dispersed in understandable panic."}},
{"type":"Feature","id":"5","geometry":{"type":"Point","coordinates":[-1.591114,52.346975]},"properties":{"name":"Bridge","description":"Next, Elizabeth rode across a bridge, railed in on both sides. Fixed in the railings were a range of gifts and provisions, indicative of the hospitality that she would receive during her visit. An actor ‘clad like a Poet’ came out and gave a speech expounding on the theme, whereupon the queen was admitted to the castle, to the sound of ‘sweet music’."}},
{"type":"Feature","id":"6","geometry":{"type":"Point","coordinates":[-1.592707,52.347745]},"properties":{"name":"Inner court","description":"Arriving at the inner court, Elizabeth alighted from her horse, to the sound of drums, fifes and trumpets."}},
{"type":"Feature","id":"7","geometry":{"type":"Point","coordinates":[-1.592471,52.347453]},"properties":{"name":"Leicester’s Building","description":"Finally, the queen climbed the stairs to her lodgings, in ‘Leicester’s Building’, a suite of private rooms specially constructed for her visit. Besides the queen’s bedchamber, the rooms included a dancing chamber and rooms to house the queen’s extensive travelling wardrobe. But the visit wasn’t just devoted to pleasure. Every day, some 20 horses arrived and departed the queen’s lodgings, carrying paperwork to and from her secretariat."}},
{"type":"Feature","id":"8","geometry":{"type":"Point","coordinates":[-1.592761,52.348391]},"properties":{"name":"Gardens","description":"An elaborate temporary garden was designed and installed for Elizabeth’s visit, and was described in great detail in Robert Laneham’s contemporary account of the entertainments. These gardens have now been recreated by English Heritage and can be seen by visitors to the castle."}},
{"type":"Feature","id":"9","geometry":{"type":"Point","coordinates":[-1.592573,52.348004]},"properties":{"name":"Great Chamber","description":"The hall of the medieval castle was transformed by Leicester into a Great Chamber, where he housed his collection of around 50 portraits, many commissioned specially for Elizabeth’s visit in 1575, including the twin portraits by Zuccharo."}},
{"type":"Feature","id":"10","geometry":{"type":"Point","coordinates":[-1.593276,52.347745]},"properties":{"name":"Great Hall","description":"The impressive great hall of the castle, dominated by huge deep-set windows and hung with tapestries was left unaltered by Leicester."}},
{"type":"Feature","id":"11","geometry":{"type":"Point","coordinates":[-1.592807,52.346919]},"properties":{"name":"Mere pageants","description":"During the queen’s visit, a series of water pageants took place, including elaborate firework displays."}}]}]
EOT;
    $markers_geo = new local_map_layer('geojson', 'liz', 'Elizabeth I at Kenilworth', $geo);

    echo '<div class="generalbox" style="float: left;">';
    echo '<h2>Map with single marker</h2>';
    $map1 = new local_map_map('map_marker', $greenwich);
    echo $map1->render();
    echo '</div>';

    echo '<div class="generalbox" style="float: left;">';
    echo '<h2>Map with multiple markers</h2>';
    $map2 = new local_map_map('map_markers', [$markers]);
    echo $map2->render();
    echo '</div>';

    echo '<div class="generalbox" style="float: right;">';
    echo '<h2>Map with specified view and size, and geoJSON markers</h2>';
    $view = new local_map_view(52.346919, -1.5915, 16, '100%', '600px');
    $map3 = new local_map_map('map_view', [$markers_geo], $view);
    echo $map3->render();
    echo '</div>';

    echo '<div class="generalbox" style="float: left;">';
    echo '<h2>Map with multiple tilesets, multiple layers</h2>';
    $view = new local_map_view(54, -3, 4);
    $map4 = new local_map_map('map_controls', [$markers, $markers_geo], $view, ['osm', 'mapquest_osm', 'mapquest_arial']);
    echo $map4->render();
    echo '</div>';


} else {
    echo '<p>Maps not enabled. Enable in <a href="http://m2/admin/settings.php?section=mapsettings">settings</a>.</p>';
}

echo $OUTPUT->footer();
