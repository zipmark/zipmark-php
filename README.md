# Zipmark PHP Client

The Zipmark PHP Client library is used to interact with Zipmark's [API](https://dev.zipmark.com).

## Installation

The easiest way to download and install the Zipmark PHP Client is with git:

```
git clone git://github.com/zipmark/zipmark-php.git /path/to/zipmark/client
```

### Requirements

This library depends on PHP 5.3.6 (or higher) and libcurl compiled with OpenSSL support.  phpinfo(); should show information like the following:

```
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
```

## Initialization

The Zipmark PHP Client supports both global and local client credentials.  The client is loaded by requiring a single file:

```php
require_once('./lib/zipmark.php');
```

## Usage Examples

The Zipmark PHP client supports objects and lists of objects.  

### Instantiating a client

```php
$client = new Zipmark_Client("Application Identifier", "Application Secret", ProductionEnabled);
```

Application Identifier and Application Secret should be replaced with the vendor application identifier and secret provided by Zipmark.

ProductionEnabled is a boolean flag that indicates whether traffic for this client is live production traffic.  Omitting this argument will result in all traffic being directed to Zipmark's sandbox environment.  If the ProductionEnabled flag is omitted, production mode can also be enabled with the following:

```php
$client->setProduction(true);
```

### Loading a Bill from a known Bill ID

```php
$bill = $client->bill->get("Bill ID");
```

### Creating a new Bill

Create a bill object, set required attributes, send it to Zipmark

```php
$bill = new Zipmark_Bill();
$bill->identifier = "abc123";     // Unique Bill Identifier
$bill->amount_cents = 100;        // Bill amount in cents
$bill->bill_template_id = "UUID"; // UUID of Bill Template from Zipmark
$bill->memo = "Memo to customer"; // Text memo shown to customer
$bill->date = "YYYY-MM-DD";       // Date of Bill issuance
$bill->content = "{}";            // JSON String with Bill content - rendered with template

$bill->create($client);
```

### Updating an existing Bill

Get the bill, make a change, send it back to Zipmark

```php
$bill = $client->bill->get("Bill ID");

$bill->memo = "Please pay with Zipmark";

$bill->update();
```

### Retrieving and using a list of all Bills

Retrieve a list of all bills.  The client understands Zipmark's pagination system.  It loads one page of objects at a time and will retrieve more objects as necessary while iterating through the objects.

```php
$bills = $client->bills->get();
```

Get the number of objects available.

```php
$bills->count();
```

Get the current object

```php
$bill = $bills->current();
```

Get the next/previous object (these functions will return a null if there's no next or previous object)

```php
$bill = $bills->next();
$bill = $bills->prev();
```

## API Documentation

Please see the [Zipmark API](https://dev.zipmark.com) or contact Zipmark Support via [email](mailto:developers@zipmark.com) or [chat](http://bit.ly/zipmarkAPIchat) for more information.

## Unit/Acceptance Tests

The Zipmark PHP Client library includes unit tests to verify all implemented functionality.  The unit tests are built with [SimpleTest](http://simpletest.org) and can be run from the command line as:

```
$ /path/to/zipmark/php/client/test/all_tests.php
```
