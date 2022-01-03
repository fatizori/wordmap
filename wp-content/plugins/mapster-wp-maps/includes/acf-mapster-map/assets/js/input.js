(function($){

	// Globals
	var fieldSelector = false;
	var map = false;
	var currentMarker = false;

	// Mapping
	function initializeMap() {
		map = new maplibregl.Map({
			container: getFieldElement().attr('id'),
			style: {
				'version': 8,
				"glyphs": "https://fonts.openmaptiles.org/{fontstack}/{range}.pbf",
				'sources': {
					'raster-tiles': {
					'type': 'raster',
					'tiles': [
							'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png'
						],
						'tileSize': 256,
						'attribution': '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
					}
				},
				'layers': [
					{
					'id': 'simple-tiles',
					'type': 'raster',
					'source': 'raster-tiles',
					'minzoom': 0,
					'maxzoom': 22
					}
				]
			},
			center: [0, 0],
			zoom: 2
		});
		map.on('load', () => {
			map.resize();

			if(getGeoJSON().features.length > 0) {
				var bbox = turf.bbox(getGeoJSON());
				map.fitBounds(bbox, { padding : 20, maxZoom : 13, duration: 0 });
			}
		});
	}

	function initializeGeoJSON() {

			if(getPostType() === 'location') {
				showCurrentCoordinates()
			}

			getACFValues();

			map.on('load', () => {
				map.addSource('feature', {
					type : 'geojson',
					data : getGeoJSON()
				})
				addLayers()
			})
	}

	function addLayers() {
		if(getPostType() === 'location') {
			if(getLocationType() === 'circle') {
				map.addLayer({
					id : 'feature',
					type : 'circle',
					source : 'feature'
				});
			}
			if(getLocationType() === 'label' || getLocationType() === 'marker') {
				map.addLayer({
					id : 'feature',
					type : 'symbol',
					source : 'feature'
				});
				if(getLocationType() === 'marker') {
					manageMarker()
				}
			}
		}
		if(getPostType() === 'line') {
			map.addLayer({
				id : 'feature',
				type : 'line',
				source : 'feature'
			});
		}
		if(getPostType() === 'polygon') {
			map.addLayer({
				id : 'feature',
				type : 'fill',
				source : 'feature'
			});
		}
		setPaintAndLayoutProperties()
	}

	function removeMarker() {
		if(currentMarker) {
			currentMarker.remove();
		}
	}

	function manageMarker() {
		if(getPostType() === 'location' && getLocationType() === 'marker') {
			removeMarker();
			if(getGeoJSON().features.length > 0) {
				var values = getACFValues()
				var options = values.marker;
				var newMarker = new maplibregl.Marker(options).setLngLat(getGeoJSON().features[0].geometry.coordinates);
				newMarker.addTo(map)
				currentMarker = newMarker
			}
		}
	}

	function setLocationType() {
		removeMarker()
		if(map.getLayer('feature')) {
			map.removeLayer('feature');
		}
		addLayers()
		setValueChangeListeners()
	}

	function setLocationTypeListener() {
		if($('.acf-field[data-name="location_style"]').length) {
			$(document).on('change', '.acf-field[data-name="location_style"] :input', function() {
				setLocationType()
				setPaintAndLayoutProperties()
			})
		}
	}

	function setPaintAndLayoutProperties() {
		var values = getACFValues()
		if(getPostType() === 'location' && getLocationType() === 'marker') {
			manageMarker();
		} else {
			for(var field in values.paint) {
				map.setPaintProperty('feature', `${getLayerType()}${field}`, values.paint[field]);
			}
			for(var field in values.layout) {
				map.setLayoutProperty('feature', `${getLayerType()}${field}`, values.layout[field]);
			}
		}
	}

	function setValueChangeListeners() {
		var values = getACFValues()
		for(var type in values) {
			for(var field in values[type]) {
				$(document).on('change', `.acf-field[data-name="${field}"] :input`, function() {
					setPaintAndLayoutProperties()
				})
			}
		}
		if(getPostType() === 'line') {
			var specialEvents = ['dashed_line', 'dash_length', 'gap_length'];
			specialEvents.forEach(thisName => {
				$(document).on('change', `.acf-field[data-name="${thisName}"] :input`, function() {
					setPaintAndLayoutProperties()
				});
			});
		}
		if(getPostType() === 'location' && getLocationType() === 'label') {
			var specialEvents = ['label_on', 'icon_on', 'icon-image', 'icon-translate-x', 'icon-translate-y', 'text-translate-x', 'text-translate-y'];
			specialEvents.forEach(thisName => {
				$(document).on('change', `.acf-field[data-name="${thisName}"] :input`, function() {
					setPaintAndLayoutProperties()
				});
			})
		}
	}

	// Drawing
	function initializeDraw() {
		var fieldOptions = getFieldOptions();
		var Draw = new MapboxDraw({
			displayControlsDefault : false,
			controls : {
				point : fieldOptions.pointAllowed,
				line_string : fieldOptions.lineStringAllowed,
				polygon : fieldOptions.polygonAllowed,
				trash : true
			}
		});
		map.addControl(Draw, 'top-left');

		map.on('draw.create', (e) => {
			var newGeoJSON = getGeoJSON();
			if(getFieldOptions().multipleAllowed) {
				newGeoJSON.features.push(e.features[0]);
			} else {
				newGeoJSON.features = [e.features[0]]
			}
			map.setLayoutProperty('feature', 'visibility', 'visible');
			$('#finish-drawing div').fadeOut();
			Draw.deleteAll();
			setGeoJSON(newGeoJSON)
		})

		map.on('draw.delete', (e) => {
			var newGeoJSON = getGeoJSON();
			newGeoJSON.features = [];
			map.setLayoutProperty('feature', 'visibility', 'visible');
			setGeoJSON(newGeoJSON)
		});

		map.on('load', () => {
			$(document).on('click', '#draw-point', () => {
				map.setLayoutProperty('feature', 'visibility', 'none');
				$('#finish-drawing div').fadeIn();
				Draw.changeMode('draw_point');
			})
			$(document).on('click', '#draw-linestring', () => {
				map.setLayoutProperty('feature', 'visibility', 'none');
				$('#finish-drawing div').fadeIn();
				Draw.changeMode('draw_line_string');
			})
			$(document).on('click', '#edit-linestring', () => {
				if(getGeoJSON().features[0]) {
					map.setLayoutProperty('feature', 'visibility', 'none');
					$('#finish-drawing div').fadeIn();
					var ids = Draw.add(getGeoJSON())
					Draw.changeMode('direct_select', { featureId : ids[0] });
				}
			})
			$(document).on('click', '#draw-polygon', () => {
				map.setLayoutProperty('feature', 'visibility', 'none');
				$('#finish-drawing div').fadeIn();
				Draw.changeMode('draw_polygon');
			})
			$(document).on('click', '#edit-polygon', () => {
				if(getGeoJSON().features[0]) {
					map.setLayoutProperty('feature', 'visibility', 'none');
					$('#finish-drawing div').fadeIn();
					var ids = Draw.add(getGeoJSON())
					Draw.changeMode('direct_select', { featureId : ids[0] });
				}
			})
			$(document).on('click', '#finish-drawing div', () => {
				var newGeoJSON = getGeoJSON();
				var allDrawFeatures = Draw.getAll();
				var thisIndex = newGeoJSON.features.findIndex(feature => feature.id === allDrawFeatures.features[0].id);
				if(thisIndex > -1) {
					newGeoJSON.features[thisIndex] = allDrawFeatures.features[0]
				}
				map.setLayoutProperty('feature', 'visibility', 'visible');
				$('#finish-drawing div').fadeOut();
				Draw.deleteAll();
				setGeoJSON(newGeoJSON)
			})
			$(document).on('click', '#draw-delete', () => {
				Draw.deleteAll();
				map.setLayoutProperty('feature', 'visibility', 'none');
				var newGeoJSON = { type : "FeatureCollection", features : []}
				setGeoJSON(newGeoJSON)
			})
		})
	}


	function initialize_field( $field ) {

		// UI modifications
		fieldSelector = $field;

		initializeMap();
		initializeDraw();
		initializeGeoJSON();
		setValueChangeListeners();
		setGeocoder();

		if(getPostType() === 'line' || getPostType() === 'polygon') {
			setUploader();
		}

		if(getPostType() === 'location') {
			setLocationTypeListener();
		}

	}

	// Saving
	function setGeoJSON(geoJSON) {
		saveField(geoJSON);
		map.getSource('feature').setData(geoJSON);
		manageMarker()
		if(getPostType() === 'location') {
			showCurrentCoordinates()
		}
	}

	function saveField(geoJSON) {
		$(`#mapster-map-geojson-${getFieldID()}`).attr('value', JSON.stringify(geoJSON));
	}

	// Helpers
	function getLocationType() {
		return $('.acf-field[data-name="location_style"]').find('select').val()
	}

	function getACFValues() {

		if(getPostType() === 'location' && getLocationType() === 'circle') {
			var colorVal = $('.acf-field[data-name="circle"] .acf-field[data-name="color"]').find(':input').val()
			var opacityVal = $('.acf-field[data-name="circle"] .acf-field[data-name="opacity"]').find(':input').val()
			var radiusVal = $('.acf-field[data-name="circle"] .acf-field[data-name="radius"]').find(':input').val()
			var strokeWidthVal = $('.acf-field[data-name="circle"] .acf-field[data-name="stroke-width"]').find(':input').val()
			var strokeColorVal = $('.acf-field[data-name="circle"] .acf-field[data-name="stroke-color"]').find(':input').val()
			var strokeOpacityVal = $('.acf-field[data-name="circle"] .acf-field[data-name="stroke-opacity"]').find(':input').val()
			const circleValues = {
				paint : {
					color : colorVal !== '' ? colorVal : '#000',
					opacity : opacityVal !== '' ? parseFloat(opacityVal)/100 : 1,
					radius : radiusVal !== '' ? parseFloat(radiusVal) : 5,
					'stroke-width' : strokeWidthVal !== '' ? parseFloat(strokeWidthVal) : 0,
					'stroke-color' : strokeColorVal !== '' ? strokeColorVal : '#FFF',
					'stroke-opacity' : strokeOpacityVal !== '' ? parseFloat(strokeOpacityVal)/100 : 1
				}
			}
			return circleValues;
		}

		if(getPostType() === 'location' && getLocationType() === 'marker') {
			var colorVal = $('.acf-field[data-name="marker"] .acf-field[data-name="color"]').find(':input').val()
			var scaleVal = $('.acf-field[data-name="marker"] .acf-field[data-name="scale"]').find(':input').val()
			var rotationVal = $('.acf-field[data-name="marker"] .acf-field[data-name="rotation"]').find(':input').val()
			var anchorVal = $('.acf-field[data-name="marker"] .acf-field[data-name="anchor"]').find(':input').val()
			const markerValues = {
				marker : {
					color : colorVal !== '' ? colorVal : '#000',
					scale : scaleVal !== '' ? parseFloat(scaleVal)/100 : 1,
					rotation : rotationVal !== '' ? parseFloat(rotationVal) : 0,
					anchor : anchorVal !== '' ? anchorVal : 'center'
				}
			}
			return markerValues;
		}

		if(getPostType() === 'location' && getLocationType() === 'label') {
			var textOn = $('.acf-field[data-name="label"] .acf-field[data-name="label_on"]').find(':input').is(':checked')
			var textFieldVal = $('.acf-field[data-name="label"] .acf-field[data-name="text-field"]').find(':input').val()
			var textFontVal = $('.acf-field[data-name="label"] .acf-field[data-name="text-font"]').find(':input').val()
			var textSizeVal = $('.acf-field[data-name="label"] .acf-field[data-name="text-size"]').find(':input').val()
			var textColorVal = $('.acf-field[data-name="label"] .acf-field[data-name="text-color"]').find(':input').val()
			var textOpacityVal = $('.acf-field[data-name="label"] .acf-field[data-name="text-opacity"]').find(':input').val()
			var textRotationVal = $('.acf-field[data-name="label"] .acf-field[data-name="text-rotate"]').find(':input').val()
			var textTranslateXVal = $('.acf-field[data-name="label"] .acf-field[data-name="text-translate-x"]').find(':input').val()
			var textTranslateYVal = $('.acf-field[data-name="label"] .acf-field[data-name="text-translate-y"]').find(':input').val()
			var textHaloWidthVal = $('.acf-field[data-name="label"] .acf-field[data-name="text-halo-width"]').find(':input').val()
			var textHaloColorVal = $('.acf-field[data-name="label"] .acf-field[data-name="text-halo-color"]').find(':input').val()
			var textHaloBlurVal = $('.acf-field[data-name="label"] .acf-field[data-name="text-halo-blur"]').find(':input').val()

			var iconOn = $('.acf-field[data-name="icon"] .acf-field[data-name="icon_on"]').find(':input').is(':checked')
			var iconImageVal = $('.acf-field[data-name="icon"] .acf-field[data-name="icon-image"] img').attr('src');
			var iconSizeVal = $('.acf-field[data-name="icon"] .acf-field[data-name="icon-size"]').find(':input').val()
			var iconOpacityVal = $('.acf-field[data-name="icon"] .acf-field[data-name="icon-opacity"]').find(':input').val()
			var iconRotationVal = $('.acf-field[data-name="icon"] .acf-field[data-name="icon-rotate"]').find(':input').val()
			var iconTranslateXVal = $('.acf-field[data-name="icon"] .acf-field[data-name="icon-translate-x"]').find(':input').val()
			var iconTranslateYVal = $('.acf-field[data-name="icon"] .acf-field[data-name="icon-translate-y"]').find(':input').val()
			var iconAnchorVal = $('.acf-field[data-name="icon"] .acf-field[data-name="icon-anchor"]').find(':input').val()
			if(iconImageVal !== '') {
				addNewIcon(iconImageVal, () => {
					map.setLayoutProperty('feature', 'icon-image', 'icon-image-location')
				})
			}
			const labelValues = {
				layout : {
					'text-field' : textFieldVal !== '' ? textFieldVal : "",
					'text-font' : [ textFontVal ],
					'text-rotate' : textRotationVal !== '' ? parseFloat(textRotationVal) : 0,
					'text-size' : textSizeVal !== '' ? parseFloat(textSizeVal) : 16,
					'icon-size' : iconSizeVal !== '' ? parseFloat(iconSizeVal)/100 : 1,
					'icon-rotate' : iconRotationVal !== '' ? parseFloat(iconRotationVal) : 0,
					'icon-anchor' : iconAnchorVal !== '' ? iconAnchorVal : 'center',
					'icon-offset' : iconTranslateXVal !== '' && iconTranslateYVal !== '' ? [parseFloat(iconTranslateXVal), parseFloat(iconTranslateYVal)] : [0, 0],
					'text-offset' : textTranslateXVal !== '' && textTranslateYVal !== '' ? [parseFloat(textTranslateXVal), parseFloat(textTranslateYVal)] : [0, 0]
				},
				paint : {
					'text-color' : textColorVal !== '' ? textColorVal : '#000000',
					'text-halo-width' : textHaloWidthVal !== '' ? parseFloat(textHaloWidthVal) : 1,
					'text-halo-color' : textHaloColorVal !== '' ? textHaloColorVal : '#FFFFFF',
					'text-halo-blur' : textHaloBlurVal !== '' ? parseFloat(textHaloBlurVal)/100 : 0.5,
					'text-opacity' : textOpacityVal !== '' ? parseFloat(textOpacityVal)/100 : 1,
					'icon-opacity' : iconOpacityVal !== '' ? parseFloat(iconOpacityVal)/100 : 1
				}
			}
			if(!textOn) {
				labelValues.layout['text-size'] = 0;
			}
			if(!iconOn) {
				labelValues.layout['icon-size'] = 0;
			}
			return labelValues;
		}

		if(getPostType() === 'line') {
			var colorVal = $('.acf-field[data-name="color"]').find(':input').val()
			var opacityVal = $('.acf-field[data-name="opacity"]').find(':input').val()
			var widthVal = $('.acf-field[data-name="width"]').find(':input').val()
			var hasDash = $('.acf-field[data-name="dashed_line"]').find(':input').is(':checked')
			var dashLength = $('.acf-field[data-name="dash_length"]').find(':input').val()
			var gapLength = $('.acf-field[data-name="gap_length"]').find(':input').val()
			const lineValues = {
				paint : {
					color : colorVal !== '' ? colorVal : '#000',
					opacity : opacityVal !== '' ? parseFloat(opacityVal)/100 : 1,
					width : widthVal !== '' ? parseFloat(widthVal) : 5
				}
			}
			if(hasDash) {
				lineValues.paint['dasharray'] = dashLength!=='' && gapLength!== '' ? [parseFloat(dashLength), parseFloat(gapLength)] : [1, 1];
			} else {
				lineValues.paint['dasharray'] = [1, 0]
			}
			return lineValues;
		}

		if(getPostType() === 'polygon') {
			var colorVal = $('.acf-field[data-name="color"]').find('input').val()
			var opacityVal = $('.acf-field[data-name="opacity"]').find('input').val()
			var outlineColorVal = $('.acf-field[data-name="outline-color"]').find('input').val()
			const polygonValues = {
				paint : {
					color : colorVal !== '' ? colorVal : '#000',
					opacity : opacityVal !== '' ? parseFloat(opacityVal)/100 : 1,
					'outline-color' : outlineColorVal !== '' ? outlineColorVal : 'rgba(0, 0, 0, 0)'
				}
			}
			return polygonValues;
		}
	}

	function getLayerType() {
		if(getPostType() === 'location' && getLocationType() === 'circle') {
			return 'circle-';
		}
		if(getPostType() === 'location' && getLocationType() === 'label') {
			return '';
		}
		if(getPostType() === 'line') {
			return 'line-';
		}
		if(getPostType() === 'polygon') {
			return 'fill-';
		}
	}

	function addNewIcon(iconSrc, callback) {
		if(!iconSrc) {
			callback()
		}
    if(window.location.protocol === 'https:' && iconSrc.indexOf('http://') > -1) {
      iconSrc = iconSrc.replace('http', 'https');
    }
		map.loadImage(iconSrc, (err, img) => {
			if(!map.loaded()) {
				map.once('idle', () => {
					if(map.hasImage('icon-image-location')) {
						map.updateImage('icon-image-location', img);
					} else {
						map.addImage('icon-image-location', img)
					}
					callback()
				})
			} else {
				if(map.hasImage('icon-image-location')) {
					map.updateImage('icon-image-location', img);
				} else {
					map.addImage('icon-image-location', img)
				}
				callback()
			}
		})
	}

	function getFieldElement() {
		return $(fieldSelector).find('.mapster-map')
	}

	function getFieldID() {
		return getFieldElement().attr('id').replace('mapster-map-', '');
	}

	function getPostType() {
		var classList = $('body').attr("class").split(/\s+/);
		var mapsterClass = classList.find(thisClass => thisClass.indexOf('post-type-mapster-wp-') > -1);
		return mapsterClass.replace('post-type-mapster-wp-', '');
	}

	function getGeoJSON() {
		var existingValue = $(`#mapster-map-geojson-${getFieldID()}`).val();
		if(existingValue && existingValue !== '') {
			var currentGeoJSON = JSON.parse(existingValue);
			return currentGeoJSON;
		}
		return { type : "FeatureCollection", features : [] };
	}

	function getFieldOptions() {
		var pointAllowed = getFieldElement().data('point') === 1 ? true : false;
		var lineStringAllowed = getFieldElement().data('linestring') === 1 ? true : false;
		var polygonAllowed = getFieldElement().data('polygon') === 1 ? true : false;
		var multipleAllowed = getFieldElement().data('multiple') === 1 ? true : false;
		return {
			pointAllowed, lineStringAllowed, polygonAllowed, multipleAllowed
		}
	}

	function showCurrentCoordinates() {
		if(getGeoJSON().features[0]) {
			var coordinates = getGeoJSON().features[0].geometry.coordinates;
			$('#current-coordinates').html(coordinates.join(', '));
		} else {
			$('#current-coordinates').html('');
		}
	}

	// Special functions
	function setUploader() {
		$('#mapster-map-upload').change(function(e) {
			geoJSONUploaded(e);
		})
	  const geoJSONUploaded = async (e) => {
	    const fileContents = await new Response(e.target.files[0]).json()
	    importGeoJSON(fileContents)
	  }
	  const importGeoJSON = (file) => {
	    if(!file) {
	      window.alert("Please upload a file.");
	    } else {
	      const errors = geojsonhint.hint(file)
	      if(errors.length === 0) {
					var feature = file.features.find(feature => feature.geometry.type === $('#mapster-map-upload').data('type'));
					if(feature) {
	        	if(window.confirm("Are you sure you want to upload this geoJSON? It will overwrite all existing features.")) {
							if(!feature.id) {
								feature.id = parseInt(Math.random() * Math.random() * 10000000);
							}
							var newGeoJSON = { type : "FeatureCollection", features : [feature]}
							setGeoJSON(newGeoJSON)
						}
					} else {
						window.alert("Please double-check that your geoJSON has the right geometry type for this post.")
					}
	      } else {
	        window.alert('GeoJSON error: ' + JSON.stringify(errors));
	      }
	    }
	  }
	}

	function setGeocoder() {
		const provider = new GeoSearch.OpenStreetMapProvider();
		$(document).on('click', `#mapster-get-results`, async function() {
			var searchValue = $('#mapster-map-geosearch').val();
			if(searchValue.length > 2) {
				const results = await provider.search({ query: searchValue });
				var htmlToAppend = createStoreGeneratorGeocoderResultsHTML(results)
				$(`#mapster-geocoder-results`).empty().append(htmlToAppend)
			} else {
				$(`#mapster-geocoder-results`).empty();
			}
		})
		$(document).on('keyup', `#mapster-map-geosearch`, function() {
			var searchValue = $('#mapster-map-geosearch').val();
			if(searchValue.length <= 2) {
				$(`#mapster-geocoder-results`).empty();
			}
		});

		// Selecting geocoder results
		$(document).on('click', `#mapster-geocoder-results li`, function() {
			var theseBounds = $(this).data('bounds');
			var thisCenter = $(this).data('center');
			var newGeoJSON = { type : "FeatureCollection", features : [{
				type : "Feature",
				properties : {},
				geometry : {
					type : "Point",
					coordinates : thisCenter
				}
			}]}
			setGeoJSON(newGeoJSON)
			map.fitBounds(theseBounds.map(bound => bound.slice().reverse()), { padding: 20 });
			$(`#mapster-geocoder-results`).empty();
		});

		const createStoreGeneratorGeocoderResultsHTML = (geocoderResults) => {
			var html = '';
			geocoderResults.slice(0, 5).forEach(result => {
				html += `<li data-center="${JSON.stringify([result.x, result.y])}" data-bounds="${JSON.stringify(result.bounds)}">${result.label}</li>`
			})
			return html;
		}
	}



	if( typeof acf.add_action !== 'undefined' ) {

		acf.add_action('ready_field/type=mapster-map', initialize_field);
		acf.add_action('append_field/type=mapster-map', initialize_field);
		acf.add_action('show_field/type=mapster-map', initialize_field);

	} else {

		$(document).on('acf/setup_fields', function(e, postbox){

			// find all relevant fields
			$(postbox).find('.field[data-field_type="mapster-map"]').each(function(){
				// initialize
				initialize_field( $(this) );

			});

		});

	}

})(jQuery);
