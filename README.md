# Zipmark PHP Client

The Zipmark PHP Client library is used to interact with Zipmark's [API](https://dev.zipmark.com).

## Installation

The easiest way to download and install the Zipmark PHP Client is with git:

    git clone git://github.com/zipmark/zipmark-php.git /path/to/zipmark/client

### Requirements

This library depends on PHP 5.3.6 (or higher) and libcurl compiled with OpenSSL support.  phpinfo(); should show information like the following:

    curl

    cURL support => enabled
    cURL Information => 7.21.4
    Age => 3
    Features
    AsynchDNS => Yes
    Debug => No
    GSS-Negotiate => Yes
    IDN => No
    IPv6 => Yes
    Largefile => Yes
    NTLM => Yes
    SPNEGO => No
    SSL => Yes
    SSPI => No
    krb4 => No
    libz => Yes
    CharConv => No
    Protocols => dict, file, ftp, ftps, gopher, http, https, imap, imaps, ldap, ldaps, pop3, pop3s, rtsp, smtp, smtps, telnet, tftp
    Host => universal-apple-darwin11.0
    SSL Version => OpenSSL/0.9.8r
    ZLib Version => 1.2.5

    openssl

    OpenSSL support => enabled
    OpenSSL Library Version => OpenSSL 0.9.8r 8 Feb 2011
    OpenSSL Header Version => OpenSSL 0.9.8r 8 Feb 2011

## Initialization

Load the Zipmark PHP Client and set your Application Identifier and Secret globally:

    <?php
    require_once('./lib/zipmark.php');

    Zipmark_Client::$appId = 'AbCdEfGhIjKlMnOpQrStUvWxYz0123456789';
    Zipmark_Client::$appSecret = 'AbCdEfGhIjKlMnOpQrStUvWxYz0123456789AbCdEfGhIjKlMnOpQrStUvWxYz0123456789';
    ?>

The Client will connect to Zipmark's production environment by default.  To use for testing with Zipmark's sandbox environment, set the API URL:

    <?php
    Zipmark_
    Client::$apiUrl = 'https://sandbox.zipmark.com';
    ?>

## API Documentation

Please see the [Zipmark API](https://dev.zipmark.com) or contact Zipmark Support via [email](mailto:developers@zipmark.com) or [chat](http://bit.ly/zipmarkAPIchat) for more information.

## Unit/Acceptance Tests

The Zipmark PHP Client library includes unit tests to verify all implemented functionality.  The unit tests are built with [SimpleTest](http://simpletest.org) and can be run from the command line as:

    $ /path/to/zipmark/php/client/test/all_tests.php
