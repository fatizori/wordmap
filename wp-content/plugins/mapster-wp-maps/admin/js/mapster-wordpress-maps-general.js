(function( $ ) {

  $(document).on('click', '.mapster-duplicate', function() {

    fetch(window.params.rest_url + 'mapster-wp-maps/duplicate', {
      headers : {
        'X-WP-Nonce' : window.params.nonce,
        'Content-Type' : 'application/json'
      },
      method : "POST",
      body : JSON.stringify({
        id : $(this).attr('id').replace('mapster-', '')
      })
    }).then(resp => resp.json()).then(response => {
      window.location.reload();
    })
  })

})(jQuery)
