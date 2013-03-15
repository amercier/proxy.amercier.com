
$url = window.location.href.replace(/test\/(index\.html)?$/, '');

asyncTest('GET http://www.google.fr/', function() {
  $.ajax($url + 'http://www.google.fr', {
    headers: {
      'Accept': 'text/html'
    }
  })
    .done(function(response, status, deferred) {
      ok(true, '[' + deferred.status + '] ' + deferred.statusText + ' - ' + deferred.responseText);
      start();
    })
    .fail(function(deferred, status, response) {
      ok(false, '[' + deferred.status + '] ' + deferred.statusText + (deferred.responseText ? ' - ' + deferred.responseText : '') );
      start();
    });
});