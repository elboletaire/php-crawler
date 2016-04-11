About
=====

A simple crawler (spider) writen in PHP.

Installation
============

~~~bash
composer require elboletaire/crawler:dev-master
~~~

Usage
=====

```php
<?php
// require 'vendor/autoload.php';

use Elboletaire\Crawler\Crawler;

try {
	$crawler = new Crawler('http://www.underave.net', 3, true);
	print_r($crawler->crawl());
} catch (Exception $e) {
	die($e->getMessage());
}

```

License
=======

Author: Òscar Casajuana

http://www.gnu.org/copyleft/gpl.html
