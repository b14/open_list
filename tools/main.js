$(function() {
  // Setup the raw data.
  $('.data').each(function (i, element) {
    var
      $headline = $('h3', element),
      $data = $('pre', element);

      $headline.click(function (e) {
        $data.slideToggle('fast');
      });
  });

  // Setup the raw data.
  $('.help').each(function (i, element) {
    var
      $toggler = $('.toggler', element),
      $data = $('.text', element);

      $toggler.click(function (e) {
        $data.fadeToggle('fast');
      });
  });
});
