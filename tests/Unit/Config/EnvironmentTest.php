<?php

namespace Tests\Unit\Config;

use App\Etc\Config\Environment;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    public function test_set_and_get_returns_value(): void
    {
        Environment::set('TEST_ENV_UNIT', 'hello');
        $this->assertSame('hello', Environment::get('TEST_ENV_UNIT'));
    }

    public function test_get_returns_default_for_missing_key(): void
    {
        $this->assertSame('fallback', Environment::get('_UPMVC_MISSING_KEY_XYZ', 'fallback'));
    }

    public function test_get_returns_null_default_when_not_specified(): void
    {
        $this->assertNull(Environment::get('_UPMVC_MISSING_KEY_XYZ'));
    }

    public function test_has_returns_true_for_existing_key(): void
    {
        Environment::set('TEST_HAS_KEY', 'yes');
        $this->assertTrue(Environment::has('TEST_HAS_KEY'));
    }

    public function test_has_returns_false_for_missing_key(): void
    {
        $this->assertFalse(Environment::has('_UPMVC_TOTALLY_ABSENT_KEY'));
    }

    public function test_isTesting_when_app_env_is_testing(): void
    {
        Environment::set('APP_ENV', 'testing');
        $this->assertTrue(Environment::isTesting());
    }

    public function test_isProduction_when_app_env_is_production(): void
    {
        Environment::set('APP_ENV', 'production');
        $this->assertTrue(Environment::isProduction());
        Environment::set('APP_ENV', 'testing');
    }

    public function test_isDevelopment_when_app_env_is_development(): void
    {
        Environment::set('APP_ENV', 'development');
        $this->assertTrue(Environment::isDevelopment());
        Environment::set('APP_ENV', 'testing');
    }

    public function test_isTesting_false_when_app_env_is_not_testing(): void
    {
        Environment::set('APP_ENV', 'production');
        $this->assertFalse(Environment::isTesting());
        Environment::set('APP_ENV', 'testing');
    }

    public function test_set_overwrites_existing_value(): void
    {
        Environment::set('TEST_OVERWRITE', 'first');
        Environment::set('TEST_OVERWRITE', 'second');
        $this->assertSame('second', Environment::get('TEST_OVERWRITE'));
    }
}
