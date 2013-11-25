Map module for Moodle.

A YUI module to load and init the Leflet map JS and CSS, and a PHP module to easily access it in Moodle. Features:
 * Adds Leaflet as child object of "M"
 * Create map(s) with default/specified view
 * Set tile provider
 * Add markers & popups
 * Add geoJSON
 * Add markers
 * Reverse geocode via nominatim.openstreetmap.org

 * Database activity hack & preset for maps on latlong field

TODO:
 * Put JS data in M.cfg (or similar), and move all JS functionality from locallib.php to map.js
 * Map module settings
 * Map option for database latlong field
 * Map module in core, not local
 * Move render() to render API
 * Restric zoom levels for limited tile providers
 * Add more tile providers

 * Database activity modifications for maps on latlong field
