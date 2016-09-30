(function($) {
  $.fn.trashfiles = function() {
    var
      el = $('.sidebar-inject.trashfiles'),
      sidebar = $('.sidebar-list').first();

      sidebar.append($('<li></li>').append(el));

    el.click(function(e) {
      console.log('trashing...');

      var blueprintKey = el.attr('data-name');
      var id = el.attr('data-url');
      var baseUrl = window.location.href.replace(/(\/edit.*)/g, '/field') + '/' + blueprintKey + '/trashfiles/ajax/';

      $.ajax({
        url: baseUrl + encodeURIComponent(id),
        type: 'GET',
        success: function(response) {
          var r = JSON.parse(response);

          if(r.class == 'error') alert(r.message);
          else if(r.class == 'success' && r.uri) {

          }
        }
      });
    });
  };

})(jQuery);
