<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
</head>
<body>

<form class="js-form" action="/inquire" method="POST">
  <input type="text" name="q">
  <input type="submit" value="submit">
</form>

<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script>
(function($) {
  $('.js-form').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
      type: $(this).attr('method'),
      url:  $(this).attr('action'),
      data: { q: $(this).find('[name=q]').val() },
      processData: false,
      contentType: false
    }).then(function(res) {
      console.log(res);

      setTimeout(progress, 3000, res.location);
    });
  });

  var fetch = function(url, interval) {
    $.ajax({
      type: 'GET',
      url:  url,
      processData: false,
      contentType: false
    }).then(function(res) {
      console.log(res);

      if (res.status === 'Done') { clearInterval(interval); }
    });
  };

  var progress = function(url) {
    var interval = setInterval(fetch, 1000, url, interval);
  };
})(jQuery);
</script>

</body>
