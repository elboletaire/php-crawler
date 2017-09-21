<?php

require_once 'vendor/autoload.php';

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

define('CRAWLER_TEST_FILES_FOLDER', dirname(__FILE__) . DS . 'files' . DS);
