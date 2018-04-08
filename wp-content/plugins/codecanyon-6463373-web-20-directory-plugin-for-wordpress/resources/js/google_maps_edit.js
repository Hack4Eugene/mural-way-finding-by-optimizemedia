var w2dc_map = null;
var w2dc_allow_map_zoom = true; // allow/disallow map zoom in listener, this option needs because w2dc_map.setZoom() also calls this listener
var w2dc_geocoder = null;
var w2dc_infoWindow = null;
var w2dc_markersArray = [];
var w2dc_glocation_backend = (function(index, point, location, address_line_1, address_line_2, zip_or_postal_index, map_icon_file) {
	this.index = index;
	this.point = point;
	this.location = location;
	this.address_line_1 = address_line_1;
	this.address_line_2 = address_line_2;
	this.zip_or_postal_index = zip_or_postal_index;
	this.map_icon_file = map_icon_file;
	this.w2dc_placeMarker = function() {
		return w2dc_placeMarker_backend(this);
	};
	this.compileAddress = function() {
		var address = this.address_line_1;
		if (this.address_line_2)
			address += ", "+this.address_line_2;
		if (this.location) {
			if (address)
				address += " ";
			address += this.location;
		}
		if (w2dc_google_maps_objects.default_geocoding_location) {
			if (address)
				address += " ";
			address += w2dc_google_maps_objects.default_geocoding_location;
		}
		if (this.zip_or_postal_index) {
			if (address)
				address += " ";
			address += this.zip_or_postal_index;
		}
		return address;
	};
	this.compileHtmlAddress = function() {
		var address = this.address_line_1;
		if (this.address_line_2)
			address += ", "+this.address_line_2;
		if (this.location) {
			if (this.address_line_1 || this.address_line_2)
				address += "<br />";
			address += this.location;
		}
		if (this.zip_or_postal_index)
			address += " "+this.zip_or_postal_index;
		return address;
	};
	this.setPoint = function(point) {
		this.point = point;
	};
});

(function($) {
	"use strict";

	var w2dc_load_maps_backend = function() {
		if (document.getElementById("w2dc-maps-canvas")) {
			var mapOptions = {
					zoom: 1,
					scrollwheel: true,
					disableDoubleClickZoom: true,
					mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			if (w2dc_google_maps_objects.map_style_name != 'default' && w2dc_google_maps_objects.map_styles)
				mapOptions.styles = eval(w2dc_google_maps_objects.map_styles[w2dc_google_maps_objects.map_style_name]);
			w2dc_map = new google.maps.Map(document.getElementById("w2dc-maps-canvas"), mapOptions);

			w2dc_geocoder = new google.maps.Geocoder();

			var w2dc_coords_array_1 = new Array();
			var w2dc_coords_array_2 = new Array();

			if (w2dc_isAnyLocation_backend())
				w2dc_generateMap_backend();
			else
				w2dc_map.setCenter(new google.maps.LatLng(34, 0));

			google.maps.event.addListener(w2dc_map, 'zoom_changed', function() {
				if (w2dc_allow_map_zoom)
					jQuery(".w2dc-map-zoom").val(w2dc_map.getZoom());
			});
		}
	}

	window.w2dc_load_maps_api_backend = function() {
		$(document).trigger('w2dc_google_maps_api_loaded');

		google.maps.event.addDomListener(window, 'load', w2dc_load_maps_backend());
		
		w2dc_load_maps_api(); // Load frontend maps
		
		w2dc_setupAutocomplete();
	}
	
	window.w2dc_setupAutocomplete = function() {
		$(".w2dc-field-autocomplete").each( function() {
			if (google.maps && google.maps.places) {
				if (w2dc_google_maps_objects.address_autocomplete_code != '0')
					var options = { componentRestrictions: {country: w2dc_google_maps_objects.address_autocomplete_code}};
				else
					var options = { };
				var searchBox = new google.maps.places.Autocomplete(this, options);
				
				google.maps.event.addListener(searchBox, 'place_changed', function () {
					w2dc_generateMap_backend();
				});
			}
		});
	}

	function w2dc_setMapCenter_backend(w2dc_coords_array_1, w2dc_coords_array_2) {
		var count = 0;
		var bounds = new google.maps.LatLngBounds();
		for (count == 0; count<w2dc_coords_array_1.length; count++)  {
			bounds.extend(new google.maps.LatLng(w2dc_coords_array_1[count], w2dc_coords_array_2[count]));
		}
		if (count == 1) {
			if (jQuery(".w2dc-map-zoom").val() == '' || jQuery(".w2dc-map-zoom").val() == 0)
				var zoom_level = 1;
			else
				var zoom_level = parseInt(jQuery(".w2dc-map-zoom").val());
		} else {
			w2dc_map.fitBounds(bounds);
			var zoom_level = w2dc_map.getZoom();
		}
		w2dc_map.setCenter(bounds.getCenter());
	
		// allow/disallow map zoom in listener, this option needs because w2dc_map.setZoom() also calls this listener
		w2dc_allow_map_zoom = false;
		w2dc_map.setZoom(zoom_level);
		w2dc_allow_map_zoom = true;
	}
	
	var w2dc_coords_array_1 = new Array();
	var w2dc_coords_array_2 = new Array();
	var w2dc_attempts = 0;
	window.w2dc_generateMap_backend = function() {
		w2dc_ajax_loader_show(w2dc_google_maps_objects.locations_targeting_text);
		w2dc_coords_array_1 = new Array();
		w2dc_coords_array_2 = new Array();
		w2dc_attempts = 0;
		w2dc_clearOverlays_backend();
		w2dc_geocodeAddress_backend(0);
		w2dc_setupAutocomplete();
	}
	
	function w2dc_setFoundPoint(results, location_obj, i) {
		var point = results[0].geometry.location;
		$(".w2dc-map-coords-1:eq("+i+")").val(point.lat());
		$(".w2dc-map-coords-2:eq("+i+")").val(point.lng());
		var map_coords_1 = point.lat();
		var map_coords_2 = point.lng();
		w2dc_coords_array_1.push(map_coords_1);
		w2dc_coords_array_2.push(map_coords_2);
		location_obj.setPoint(point);
		location_obj.w2dc_placeMarker();
		w2dc_geocodeAddress_backend(i+1);

		if ((i+1) == $(".w2dc-location-in-metabox").length) {
			w2dc_setMapCenter_backend(w2dc_coords_array_1, w2dc_coords_array_2);
			w2dc_ajax_loader_hide();
		}
	}

	function w2dc_geocodeAddress_backend(i) {
		if ($(".w2dc-location-in-metabox:eq("+i+")").length) {
			var locations_drop_boxes = [];
			$(".w2dc-location-in-metabox:eq("+i+")").find("select").each(function(j, val) {
				if ($(this).val())
					locations_drop_boxes.push($(this).children(":selected").text());
			});
	
			var location_string = locations_drop_boxes.reverse().join(', ');
	
			if ($(".w2dc-manual-coords:eq("+i+")").is(":checked") && jQuery(".w2dc-map-coords-1:eq("+i+")").val()!='' && $(".w2dc-map-coords-2:eq("+i+")").val()!='' && ($(".w2dc-map-coords-1:eq("+i+")").val()!=0 || $(".w2dc-map-coords-2:eq("+i+")").val()!=0)) {
				var map_coords_1 = $(".w2dc-map-coords-1:eq("+i+")").val();
				var map_coords_2 = $(".w2dc-map-coords-2:eq("+i+")").val();
				if ($.isNumeric(map_coords_1) && $.isNumeric(map_coords_2)) {
					var point = new google.maps.LatLng(map_coords_1, map_coords_2);
					w2dc_coords_array_1.push(map_coords_1);
					w2dc_coords_array_2.push(map_coords_2);
	
					var location_obj = new w2dc_glocation_backend(i, point, 
						location_string,
						$(".w2dc-address-line-1:eq("+i+")").val(),
						$(".w2dc-address-line-2:eq("+i+")").val(),
						$(".w2dc-zip-or-postal-index:eq("+i+")").val(),
						$(".w2dc-map-icon-file:eq("+i+")").val()
					);
					location_obj.w2dc_placeMarker();
				}
				w2dc_geocodeAddress_backend(i+1);
				if ((i+1) == jQuery(".w2dc-location-in-metabox").length) {
					w2dc_setMapCenter_backend(w2dc_coords_array_1, w2dc_coords_array_2);
					w2dc_ajax_loader_hide();
				}
			} else if (location_string || $(".w2dc-address-line-1:eq("+i+")").val() || $(".w2dc-address-line-2:eq("+i+")").val() || $(".w2dc-zip-or-postal-index:eq("+i+")").val()) {
				var location_obj = new w2dc_glocation_backend(i, null, 
					location_string,
					$(".w2dc-address-line-1:eq("+i+")").val(),
					$(".w2dc-address-line-2:eq("+i+")").val(),
					$(".w2dc-zip-or-postal-index:eq("+i+")").val(),
					$(".w2dc-map-icon-file:eq("+i+")").val()
				);

				// Geocode by address
				if (w2dc_google_maps_objects.address_autocomplete_code != '0')
					var options = { 'address': location_obj.compileAddress(), componentRestrictions: {country: w2dc_google_maps_objects.address_autocomplete_code}};
				else
					var options = { 'address': location_obj.compileAddress() };

				w2dc_geocoder.geocode( options, function(results, status) {
					if (status != google.maps.GeocoderStatus.OK) {
						if (status == google.maps.GeocoderStatus.OVER_QUERY_LIMIT && w2dc_attempts < 5) {
							w2dc_attempts++;
							setTimeout('w2dc_geocodeAddress_backend('+i+')', 2000);
						} else if (status == google.maps.GeocoderStatus.ZERO_RESULTS) {
							// last chance to find correct location with Places API
							var service = new google.maps.places.PlacesService(w2dc_map);
							service.textSearch({
								query: options.address
							}, function(results, status) {
								if (status == google.maps.places.PlacesServiceStatus.OK) {
									w2dc_setFoundPoint(results, location_obj, i);
								} else {
									alert("Sorry, we were unable to geocode that address (address #"+(i)+") for the following reason: " + status);
									w2dc_ajax_loader_hide();
								}
							});
						} else {
							alert("Sorry, we were unable to geocode that address (address #"+(i)+") for the following reason: " + status);
							w2dc_ajax_loader_hide();
						}
					} else {
						w2dc_setFoundPoint(results, location_obj, i);
					}
				});
			} else
				w2dc_ajax_loader_hide();
		} else
			w2dc_attempts = 0;
	}

	window.w2dc_placeMarker_backend = function(w2dc_glocation) {
		if (w2dc_google_maps_objects.map_markers_type != 'icons') {
			if (w2dc_google_maps_objects.global_map_icons_path != '') {
				var re = /(?:\.([^.]+))?$/;
				if (w2dc_glocation.map_icon_file && typeof re.exec(w2dc_google_maps_objects.global_map_icons_path+'icons/'+w2dc_glocation.map_icon_file)[1] != "undefined")
					var icon_file = w2dc_google_maps_objects.global_map_icons_path+'icons/'+w2dc_glocation.map_icon_file;
				else
					var icon_file = w2dc_google_maps_objects.global_map_icons_path+"blank.png";
		
				var customIcon = {
						url: icon_file,
						size: new google.maps.Size(parseInt(w2dc_google_maps_objects.marker_image_width), parseInt(w2dc_google_maps_objects.marker_image_height)),
						origin: new google.maps.Point(0, 0),
						anchor: new google.maps.Point(parseInt(w2dc_google_maps_objects.marker_image_anchor_x), parseInt(w2dc_google_maps_objects.marker_image_anchor_y))
				};
		
				var marker = new google.maps.Marker({
						position: w2dc_glocation.point,
						map: w2dc_map,
						icon: customIcon,
						draggable: true
				});
			} else 
				var marker = new google.maps.Marker({
						position: w2dc_glocation.point,
						map: w2dc_map,
						draggable: true
				});
			
			w2dc_markersArray.push(marker);
			google.maps.event.addListener(marker, 'click', function() {
				w2dc_show_infoWindow_backend(w2dc_glocation, marker);
			});
		
			google.maps.event.addListener(marker, 'dragend', function(event) {
				var point = marker.getPosition();
				if (point !== undefined) {
					var selected_location_num = w2dc_glocation.index;
					$(".w2dc-manual-coords:eq("+w2dc_glocation.index+")").attr("checked", true);
					$(".w2dc-manual-coords:eq("+w2dc_glocation.index+")").parents(".w2dc-manual-coords-wrapper").find(".w2dc-manual-coords-block").show(200);
					
					$(".w2dc-map-coords-1:eq("+w2dc_glocation.index+")").val(point.lat());
					$(".w2dc-map-coords-2:eq("+w2dc_glocation.index+")").val(point.lng());
				}
			});
		} else {
			w2dc_load_richtext();
			
			var icon = false;
			var color = false;
			if (!w2dc_glocation.map_icon_file || !w2dc_in_array(w2dc_glocation.map_icon_file, w2dc_google_maps_objects.map_markers_array)) {
				if (!icon && w2dc_google_maps_objects.default_marker_icon)
					icon = w2dc_google_maps_objects.default_marker_icon;
			} else
				icon = w2dc_glocation.map_icon_file;
			if (!color)
				if (w2dc_google_maps_objects.default_marker_color)
					color = w2dc_google_maps_objects.default_marker_color;
				else
					color = '#2393ba';
			
			if (icon) {
				var map_marker_icon = '<span class="w2dc-map-marker-icon w2dc-fa '+icon+'" style="color: '+color+';"></span>';
				var map_marker_class = 'w2dc-map-marker';
			} else {
				var map_marker_icon = '';
				var map_marker_class = 'w2dc-map-marker-empty';
			}

			var marker = new RichMarker({
				position: w2dc_glocation.point,
				map: w2dc_map,
				flat: true,
				draggable: true,
				height: 40,
				content: '<div class="'+map_marker_class+'" style="background: '+color+' none repeat scroll 0 0;">'+map_marker_icon+'</div>'
			});
			
			w2dc_markersArray.push(marker);
			google.maps.event.addListener(marker, 'position_changed', function(event) {
				var point = marker.getPosition();
				if (point !== undefined) {
					var selected_location_num = w2dc_glocation.index;
					$(".w2dc-manual-coords:eq("+w2dc_glocation.index+")").attr("checked", true);
					$(".w2dc-manual-coords:eq("+w2dc_glocation.index+")").parents(".w2dc-manual-coords-wrapper").find(".w2dc-manual-coords-block").show(200);
					
					$(".w2dc-map-coords-1:eq("+w2dc_glocation.index+")").val(point.lat());
					$(".w2dc-map-coords-2:eq("+w2dc_glocation.index+")").val(point.lng());
				}
			});
		}
	}
	
	// This function builds info Window and shows it hiding another
	function w2dc_show_infoWindow_backend(w2dc_glocation, marker) {
		var address = w2dc_glocation.compileHtmlAddress();
		var index = w2dc_glocation.index;
	
		// we use global w2dc_infoWindow, not to close/open it - just to set new content (in order to prevent blinking)
		if (!w2dc_infoWindow)
			w2dc_infoWindow = new google.maps.InfoWindow();
	
		w2dc_infoWindow.setContent(address);
		w2dc_infoWindow.open(w2dc_map, marker);
	}
	
	function w2dc_clearOverlays_backend() {
		if (w2dc_markersArray) {
			for(var i = 0; i<w2dc_markersArray.length; i++){
				w2dc_markersArray[i].setMap(null);
			}
		}
	}
	
	function w2dc_isAnyLocation_backend() {
		var is_location = false;
		$(".w2dc-location-in-metabox").each(function(i, val) {
			var locations_drop_boxes = [];
			$(this).find("select").each(function(j, val) {
				if ($(this).val()) {
					is_location = true;
					return false;
				}
			});
	
			if ($(".w2dc-manual-coords:eq("+i+")").is(":checked") && $(".w2dc-map-coords-1:eq("+i+")").val()!='' && $(".w2dc-map-coords-2:eq("+i+")").val()!='' && ($(".w2dc-map-coords-1:eq("+i+")").val()!=0 || $(".w2dc-map-coords-2:eq("+i+")").val()!=0)) {
				is_location = true;
				return false;
			}
		});
		if (is_location)
			return true;
	
		if ($(".w2dc-address-line-1[value!='']").length != 0)
			return true;
	
		if ($(".w2dc-address-line-2[value!='']").length != 0)
			return true;
	
		if ($(".w2dc-zip-or-postal-index[value!='']").length != 0)
			return true;
	}
})(jQuery);