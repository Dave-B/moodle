= latlongmap database activity field =

Thanks for downloading! Please send feedback to:
 david.balch@conted.ox.ac.uk
 http://www.tall.ox.ac.uk/

== Quick start ==

1. You also need these plugins:
 * local_map (provides a map API)
 * infomap_latlongmap database preset (provides javascript plumbing to show maps from the "view list")

2. Place the unzipped "latlongmap" directory in [site_root]/mod/data/field/

3. (Not essential) Currently database plugin strings aren't fully modularised; add these to [site_root]/mod/data/lang/en/data.php:
$string['latlongmap'] = 'Latitude/longitude (with map)';
$string['namelatlongmap'] = 'Latitude/longitude field (with map)';

4. Visit [site_root]/admin and install the three plugins (local/maps, latlongmap field, infomap_latlongmap preset).

5. Create a database activity from the "Infomap" preset; customise and enjoy!

Have fun!

== Documentation ==

See local_map README.txt
