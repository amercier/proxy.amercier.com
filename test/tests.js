
url = 'http://local/proxy.amercier.com/';

asyncTest('Allowed domain', function() {
  $.ajax(url + url + 'test/', { // Oh my, how meta
      headers: {
        'Accept': 'text/html',
        'x-referer-config': window.location.pathname.replace(/(index\.html)?(\?.*)?(#.*)?$/, '') + 'proxy.json'
      }
    })
    .done(function(response, status, deferred) {
      ok(true, '[' + deferred.status + '] ' + deferred.statusText + ' - ' + deferred.responseText);
      strictEqual(deferred.status, 200, 'Status code should be 200');
      strictEqual(deferred.statusText, 'OK', 'Status text should be "OK"');
      ok(/^<!DOCTYPE /.test(response), 'Response should start with "<!DOCTYPE "');
      ok(/<title>proxy.amercier.com<\/title>/.test(response), 'Response should contain "<title>proxy.amercier.com</title>"');
      start();
    })
    .fail(function(deferred, status, response) {
      ok(false, '[' + deferred.status + '] ' + deferred.statusText + (deferred.responseText ? ' - ' + deferred.responseText : '') );
      start();
    });
});

asyncTest('Not allowed domain', function() {
  $.ajax(url + 'http://www.iana.org/domains/example', {
      headers: {
        'Accept': 'text/html',
        'X-Referer-Config': window.location.pathname.replace(/test\/(index\.html)?(\?.*)?(#.*)?$/, '') + 'proxy.json'
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

