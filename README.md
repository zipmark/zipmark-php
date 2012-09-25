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
$client = new Zipmark_Client("Application Identifier", "Application Secret");
```

Application Identifier and Application Secret should be replaced with the vendor application identifier and secret provided by Zipmark.

### Production Mode

The Zipmark PHP client will access Zipmark's sandbox environment by default.  To direct traffic to Zipmark's production environment, enable production mode with the following:

```php
$client->setProduction(true);
```

### Loading a Bill from a known Bill ID

```php
$bill = $client->bills->get("Bill ID");
```

### Discovering available resources

```php
$resources = $client->resources();
```

Resources will contain an array of all available resources.

### Creating a new Bill

Create a bill object, set required attributes, send it to Zipmark

```php
$bill_data = array(
  'identifier'       => 'abc123',           // Unique Bill Identifier
  'amount_cents'     => 100,                // Bill amount in cents
  'bill_template_id' => 'UUID',             // UUID of Bill Template from Zipmark
  'memo'             => 'Memo to customer', // Text memo shown to customer
  'date'             => 'YYYY-MM-DD',       // Date of Bill issuance
  'content'          => '{}',               // JSON String with Bill content - rendered with template
);

$bill = $client->bills->create($bill_data);
```

As an alternative, it is possible to build an object first and then save it afterwards

```php
$bill_data = array(
  'identifier'       => 'abc123',           // Unique Bill Identifier
  'amount_cents'     => 100,                // Bill amount in cents
  'bill_template_id' => 'UUID',             // UUID of Bill Template from Zipmark
  'memo'             => 'Memo to customer', // Text memo shown to customer
  'date'             => 'YYYY-MM-DD',       // Date of Bill issuance
  'content'          => '{}',               // JSON String with Bill content - rendered with template
);

$bill = $client->bills->build($bill_data);

$bill->save();
```

### Updating an existing Bill

Get the bill, make a change, send it back to Zipmark

```php
$bill = $client->bills->get("Bill ID");

$bill->memo = "Please pay with Zipmark";

$bill->save();
```

### Retrieving a list of all Bills

Retrieve a list of all bills.

```php
$bills = $client->bills->getAll();
```

Get the number of objects available.

```php
$bills->count();
```

### Basic Iterator

The Zipmark_Iterator class understands Zipmark's pagination system.  It loads one page of objects at a time and will retrieve more objects as necessary while iterating through the objects.

```php
$bills = $client->bills->getAll();
$iterator = new Zipmark_Iterator($bills);
```

Get the current object (returns null if the iterator has passed either end of the list)

```php
$bill = $iterator->current();
```

Get the next/previous object (returns null if the next/previous object would pass either end of the list)

```php
$bill = $iterator->next();
$bill = $iterator->prev();
```

### Iterating through a list of all Bills

The Zipmark_Iterator can be used to iterate through all objects of a given resource type.

```php
$iterator = new Zipmark_Iterator($client->bills->getAll());

foreach ($iterator as $bill) {
  print "Bill " . $iterator->key() . " is ID ";
  print $bill->id . " for " . $bill->amount_cents . " cents.\n";
}
```

Which would result in output similar to:

```
Bill 0 is ID 3cf1290adc08b28899dd7c7e263cca4dc9a2 for 1234 cents
Bill 1 is ID 3cf1b7bc6cfbaeb6b8b2a6001037d284c918 for 100 cents
Bill 2 is ID 3cea3cab019984233228c2eaff0edcbbb733 for 3456 cents
Bill 3 is ID 3ceaf09259f883159622aa4401ab7d06d45a for 2345 cents
Bill 4 is ID 3cea079b288120ffb129dfb62ae18de3dfee for 1234 cents
Bill 5 is ID 3ce95db62b1069e59e122c515eb191c70987 for 12345 cents
Bill 6 is ID 3ce627f7559478bee1129dae3203e373f0df for 1030 cents
Bill 7 is ID 3ce69e91d68417d1e9892ca903eba8c66a2e for 1030 cents
Bill 8 is ID 3ce6eb4a5b433f3e9b073d15a5ff725dec46 for 1020 cents
Bill 9 is ID 3ce66b0e9a510f90fb26906dd0da04df6de0 for 101 cents
```

### Callback processing

The client is able to process, verify and extract data from callbacks received from the Zipmark service.

#### Loading a callback response

A Zipmark_Callback object must be initialized with a Zipmark_Client object and the HTTP callback content (headers and body)

The array of HTTP headers sent in the callback POST should be contained in the $_SERVER variable.
The body of the callback POST should be accessible through the call file_get_contents('php://input');

```php
$callback = new Zipmark_Callback($client, $httpHeaders, $httpBody);
```

#### Verifying a callback

```php
$callbackValid = $callback->isValid();
```

$callbackValid will contain a true or false value.

#### Retrieving the callback data

Valid callbacks contain events, object types and objects.  The below functions will return their respective values/objects, or null if the callback is invalid.

```php
$callbackEvent      = $callback->event();
$callbackObjectType = $callback->objectType();
$callbackObject     = $callback->object();
```

## API Documentation

Please see the [Zipmark API](https://dev.zipmark.com) or contact Zipmark Support via [email](mailto:developers@zipmark.com) or [chat](http://bit.ly/zipmarkAPIchat) for more information.

## Unit/Acceptance Tests

The Zipmark PHP Client library includes unit tests to verify all implemented functionality.  The unit tests are built with [SimpleTest](http://simpletest.org) and can be run from the command line as:

```
$ /path/to/zipmark/php/client/test/all_tests.php
```
