<?php

/**
 * PHPUnit bootstrap for upMVC test suite.
 *
 * Application::getInstance() falls back to dirname(__DIR__, 2) from
 * src/Etc/Application.php when UPMVC_APP_ROOT is not defined, which
 * resolves to the project root — correct for both standalone and CI.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
