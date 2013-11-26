Map module for Moodle.

A YUI module to load and init the Leflet map JS and CSS, and a PHP module to easily access it in Moodle. Features:
 * Adds Leaflet.js as child object of "M"
 * Create map(s) with default/specified view
 * Set tile provider
 * Add markers & popups
 * Add geoJSON
 * Add markers
 * Reverse geocode via nominatim.openstreetmap.org

 * Database activity: modifications & preset for maps on latlong field

See index.php for example usage.


TODO:
 * Check access rights for map settings page.
 * Put JS data in M.cfg (or similar), and move all JS functionality from locallib.php to map.js
 * Additional module settings, for site-level customisable:
    * default view
    * tile providers (and default)
 * Add more tile providers?
 * Move render() to render API
 * Clean up /local/map/index.php
 * Add Unit Tests
 * Add Acceptance Tests
 * Map module in core, not local

 * Database activity: Disable pagination
