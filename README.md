proxy.amercier.com
==================

XHR proxy for *.amercier.com


Setup
-----

  - Install Composer: http://getcomposer.org/doc/00-intro.md#installation-nix
  - Download project packages:

    cd src/ && composer install


Usage
-----

This proxy is checking for the `Referer` HTTP header and ask for http://<referer>/