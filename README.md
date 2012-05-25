#About
A simple crawler (spider) writen in PHP.

#Usage

```php
<?php
include_once "Crawler.class.php";

try
{
	$crawler = new Crawler('http://www.underave.net', 3, true);
	print_r($crawler->crawl());
}
catch (Exception $e)
{
	die($e->getMessage());
}

```

## License
http://www.gnu.org/copyleft/gpl.html