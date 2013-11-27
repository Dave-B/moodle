= local_map module for Moodle =

Thanks for downloading! Please send feedback to:
 david.balch@conted.ox.ac.uk
 http://www.tall.ox.ac.uk/

== Quick start ==

1. Place the unzipped "map" directory in [site_root]/local/

2. Visit [site_root]/admin and install it.

3. Visit [site_root]/local/map/ to see some maps examples, and view index.php to see the code.

4. You may also be interested in these plugins:
 * "latlongmap" database field (a copy of latlong, with added maps)
 * "infomap" database preset (provides javascript plumbing to show maps from the "view list")

Have fun!

== Documentation ==

=== Overview ===

This is a local module to provide a Map API in Moodle. Hopfully it'll be useful enough to be added to core...

1. A YUI module to load and init the Leflet map library (http://leafletjs.com/):
 * Initialises Leaflet via Moodle/YUI module loading, creating M.local_map
   * M.local_map.addmap() - easily add maps
   * M.local_map.reversegeocode() - look up location names from lat/long values (via nominatim.openstreetmap.org)
 * Use the Leaflet.js "L" object directly in your own JS, via M.local_map.L

2. A PHP module to set up Leaftlet maps server-side.
 * Create map(s) with default/specified view
 * Set tile provider(s)
 * Add markers & popups
 * Add geoJSON
 * Reverse geocode
 * iplookup page using local_map ([site_root]/local/map/iplookup.php)

3. Via separate Database activity field and preset plugins
 * Use maps for input and display for latlong values


=== Basic usage ===

See index.php for examples in action.

The defaults produce a 520x350px OSM map showing the whole world:
    $map = new local_map_map('mymap');
    echo $map->render();


=== Custom view ===
    $view = new local_map_view(52.346919, -1.592807, 15, '100%', '5000px');
    $map = new local_map_map('map_view', null, $view);
    echo $map->render();


=== Markers ===

1. Add a single marker:
    $greenwich = new local_map_marker('greenwich', 51.48, 0, 'Greenwich', '<p>Greenwich is notable for its maritime history and for giving its name to the Greenwich Meridian (0° longitude) and Greenwich Mean Time.<br/> -- <a href="http://en.wikipedia.org/wiki/Greenwich">Wikipedia</a></p>');
    $map = new local_map_map('map_marker', $greenwich);
    echo $map->render();

2. Add markers via an array of marker objects:
    $stornoway = new local_map_marker('stornoway', 58.209890518505084, -6.390060422709212, 'Stornoway', '<p>Stornoway (/ˈstɔrnəweɪ/; Scottish Gaelic: Steòrnabhagh) is a town on the Isle of Lewis, in the Outer Hebrides of Scotland.<br/> -- <a href="http://en.wikipedia.org/wiki/Stornoway">Wikipedia</a></p>');
    $markers = new local_map_layer('marker', 'ukloc', 'UK locations', [$greenwich, $stornoway]);
    echo $map->render();

3. geoJSON
    $geo = <<<EOT
[{"type":"Feature Collection","features":[
{"type":"Feature","id":"11","geometry":{"type":"Point","coordinates":[-1.592807,52.346919]},"properties":{"name":"Mere pageants","description":"During the queen’s visit, a series of water pageants took place, including elaborate firework displays."}}]}]
EOT;
    $markers_geo = new local_map_layer('geojson', 'liz', 'Elizabeth I at Kenilworth', $geo);
    $map = new local_map_map('map_view', [$markers_geo]);
    echo $map->render();

4. Multiple tilesets (map styles)
    $map = new local_map_map('map_controls', null, null, ['osm', 'mapquest_osm', 'mapquest_arial']);
    echo $map->render();

Adding tile providers and markers/geojson can also be done after the map object is created with new local_map_map() - see [site_root]/local/map/locallib.php for functions.


== TODO ==
 * Put JS data in M.cfg (or similar), and move all JS functionality from locallib.php to map.js
 * Additional module settings, for site-level customisable:
    * default view
    * tile providers (and default)
 * Add more tile providers?
 * Move render() to render API
    * Maybe move some of the render() code into layer and tileprovider objects
 * Maybe move examples out of /local/map/index.php?
 * More flexible handling of geoJSON properties
 * Add Unit Tests
 * Add Acceptance Tests
 * Map module in core, not local
    * Use for iplookup

 * Database activity: Disable pagination
