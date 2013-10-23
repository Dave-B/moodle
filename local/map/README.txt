Map module for Moodle.

Use cases:
 1. Display a map set at a particular location and zoom
 2. Display a map set at a particular location and zoom, with data plotted:
   a. One marker
   b. Multiple markers
   c. Marker(s) with popup data
 3. Display a map, with facility to add markers.
 4. Add multiple maps to a page.


Components
 * Leaflet map library (http://leafletjs.com/) as a YUI module.
 * JSON written via $PAGE->requires
 * Ajax data loading?


----

Database integration
 * Map template (maybe could generalise to a "collation" template for JS processing - maps, stats, etc.)
  * Cannot use existing "List template" to generate JSON, as <script> in header is automatically closed by visual editor.
