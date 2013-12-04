YUI({
  delayUntil: 'domready'
}).use('event', 'node', 'moodle-local_map-map', function(Y) {
  if (Y.one("#datamap")) {
    var info = 'Click on a marker to view an entry, or add your own entry via the "Add entry" tab, above.';
    M.local_map.init(function() {
      Y.one("#datamapinfo").set('innerHTML', info);
      M.local_map.maps["datamap"] = M.local_map.addmap("datamap", {center: [46.073, 8.437], zoom: 1});
        // Remove del col if no rights
        if (!Y.one("#mod-data-map-template .delcell input")) {
          Y.all("#delhead, #mod-data-map-template .delcell").remove();
        }
        // Add entries
        Y.all("#mod-data-map-template .data-entry").each(function (taskNode) {
          var area = taskNode.getElementsByTagName('td').item(0).get('innerHTML');
          var coords = taskNode.getElementsByTagName('td').item(1).get('innerHTML').split(',');
          var desc = taskNode.getElementsByTagName('td').item(2).get('innerHTML');
          var popuptext = '<h3>'+area+'</h3><div>'+desc+'</div>';
          var closebox = '<div><a href="#" id="mapentryremove" title="Hide information">x</a></div>';
          var boxtext = '<div>'+popuptext+closebox+'</div>';
          L.marker([coords[0], coords[1]], {title:area}).addTo(M.local_map.maps["datamap"]).on('click', function(e) {
            Y.one("#datamapinfo").set('innerHTML', boxtext);
            Y.one("#mapentryremove").on('click', function(e) {
              e.preventDefault();
              Y.one("#datamapinfo").set('innerHTML', info);
            });
          });
        });
        // Hide table and buttons
        Y.one("#mod-data-map-template").insertBefore('<div id="datapanelvis"><input id="showdata" type="button" value="Show data table" /><input id="hidedata" type="button" value="Hide data table" />', 'after');
        var dataelements = Y.all('#mod-data-map-template, #hidedata, #checkall, #checknone, input.form-submit');
        var notdataelements = Y.one('#showdata');
        Y.one('#hidedata').on('click', function() {dataelements.hide();notdataelements.show()});
        Y.one('#showdata').on('click', function() {dataelements.show();notdataelements.hide()});
        dataelements.hide();notdataelements.show()
     });
  }
});
