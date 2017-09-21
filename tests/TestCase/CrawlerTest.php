<?php
namespace Elboletaire\Crawler\Test;

use Elboletaire\Crawler\Crawler;
use PHPUnit_Framework_TestCase as TestCase;

class CrawlerTest extends TestCase
{
    private $crawler;

    public function setUp()
    {
        $index = file_get_contents(CRAWLER_TEST_FILES_FOLDER . 'index.html');

        $this->crawler = $this->createMock(Crawler::class);
        $this->crawler->method('retrieveHtml')->willReturn($index);
    }

    public function tearDown()
    {
        unset($this->crawler);
    }

    public function testGetHostFromUrl()
    {

    }
}
