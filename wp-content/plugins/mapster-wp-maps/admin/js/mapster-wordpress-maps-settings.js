(function( $ ) {

  // GL JS IMPORT
  var gljsdocumentToImport = false;

  $(document).on('change', '#gl-js-import-file', function(e) {
    GLJSgeoJSONUploaded(e);
  })

  const GLJSgeoJSONUploaded = async (e) => {
    const fileContents = await new Response(e.target.files[0]).json()
    importGLJSGeoJSON(fileContents)
  }
  const importGLJSGeoJSON = (file) => {
    if(!file) {
      window.alert("Please upload a file.");
    } else {
      gljsdocumentToImport = file;
    }
  }

  $(document).on('click', '#gl-js-import-button', function() {
    if(gljsdocumentToImport) {
      fetch(window.params.rest_url + 'mapster-wp-maps/import-gl-js', {
        headers : {
          'X-WP-Nonce' : window.params.nonce,
          'Content-Type' : 'application/json'
        },
        method : "POST",
        body : JSON.stringify({
          file : gljsdocumentToImport,
          category : $('#gl-js-import-category').val()
        })
      }).then(resp => resp.json()).then(response => {
        console.log(response);
        $('#gl-js-import-result span').html(response.count);
        $('#gl-js-import-result').fadeIn();
      })
    } else {
      window.alert("Please upload a file.");
    }
  })

  // GEOJSON IMPORT
  var geojsondocumentToImport = false;

  $(document).on('change', '#geojson-import-file', function(e) {
    GeoJSONUploaded(e);
  })

  const GeoJSONUploaded = async (e) => {
    const fileContents = await new Response(e.target.files[0]).json()
    console.log(fileContents)
    importGeoJSON(fileContents)
  }
  const importGeoJSON = (file) => {
    if(!file) {
      window.alert("Please upload a file.");
    } else {
      geojsondocumentToImport = file;
    }
  }

  $(document).on('click', '#geojson-import-button', function() {
    if(geojsondocumentToImport) {
      fetch(window.params.rest_url + 'mapster-wp-maps/import-geojson', {
        headers : {
          'X-WP-Nonce' : window.params.nonce,
          'Content-Type' : 'application/json'
        },
        method : "POST",
        body : JSON.stringify({
          file : geojsondocumentToImport,
          category : $('#geojson-import-category').val()
        })
      }).then(resp => resp.json()).then(response => {
        console.log(response);
        $('#geojson-import-result span').html(response.count);
        $('#geojson-import-result').fadeIn();
      })
    } else {
      window.alert("Please upload a file.");
    }
  })

})(jQuery)
