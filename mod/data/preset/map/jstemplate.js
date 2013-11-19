YUI().use('event', function(Y) {
  Y.on("domready", function () {
    if (Y.one("#datamap")) {
	  Y.use("moodle-local_map-map", function() {
        M.local_map.init(loaddata);
	  });
    }
  });
});

function loaddata() {
	M.local_map.maps["datamap"] = M.local_map.addmap("datamap", {center: [46.073, 8.437], zoom: 1});
	// Add entries
	Y.all("#mod-data-map-template .data-entry").each(function (taskNode) {
		var area = taskNode.getElementsByTagName('td').item(1).get('innerHTML');
		var coords = taskNode.getElementsByTagName('td').item(2).get('innerHTML').split(',');
		var desc = taskNode.getElementsByTagName('td').item(3).get('innerHTML');
		var popuptext = '<h3>'+area+'</h3><div>'+desc+'</div>';
		var closebox = '<div><a href="#" id="mapentryremove" title="Hide information">x</a></div>';
		var boxtext = '<div>'+closebox+popuptext+'</div>';
		L.marker([coords[0], coords[1]], {title:area}).addTo(M.local_map.maps["datamap"]).on('click', function(e) {
			Y.one("#datamapinfo").set('innerHTML', boxtext);
			Y.one("#mapentryremove").on('click', function(e) {
				e.preventDefault();
				Y.one("#datamapinfo").set('innerHTML', '')
			});
		});
	});
	// Hide table and buttons
	Y.one("#mod-data-map-template").insertBefore('<div id="datapanelvis"><input id="showdata" type="button" value="Show data table" /><input id="hidedata" type="button" value="Hide data table" />', 'after');
	var dataelements = Y.all('#mod-data-map-template, #hidedata, #checkall, #checknone, input.form-submit');
	var notdataelements = Y.one('#showdata');
	Y.one('#hidedata').on('click', function() {dataelements.hide();notdataelements.show()});
	Y.one('#showdata').on('click', function() {dataelements.show();notdataelements.hide()});
	notdataelements.hide();dataelements.show()
};
