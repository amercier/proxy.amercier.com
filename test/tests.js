
$url = window.location.href.replace(/test\/(index\.html)?$/, '');

asyncTest('Allowed domain', function() {
  $.ajax($url + 'http://www.google.com', {
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

asyncTest('Forbidden domain', function() {
  $.ajax($url + 'http://www.google.cn', {
    headers: {
      'Accept': 'text/html'
    }
  })
    .done(function(response, status, deferred) {
      ok(false, '[' + deferred.status + '] ' + deferred.statusText + ' - ' + deferred.responseText);
      start();
    })
    .fail(function(deferred, status, response) {
      ok(true, '[' + deferred.status + '] ' + deferred.statusText + (deferred.responseText ? ' - ' + deferred.responseText : '') );
      start();
    });
});