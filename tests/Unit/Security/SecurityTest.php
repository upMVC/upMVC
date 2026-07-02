<?php

namespace Tests\Unit\Security;

use App\Etc\Security;
use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        unset($_SESSION['csrf_token']);
    }

    // --- CSRF ---

    public function test_csrfToken_returns_non_empty_string(): void
    {
        $token = Security::csrfToken();
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
    }

    public function test_csrfToken_returns_same_token_on_repeated_calls(): void
    {
        $a = Security::csrfToken();
        $b = Security::csrfToken();
        $this->assertSame($a, $b);
    }

    public function test_csrfToken_is_64_hex_chars(): void
    {
        $token = Security::csrfToken();
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token);
    }

    public function test_validateCsrf_returns_true_for_valid_token(): void
    {
        $token = Security::csrfToken();
        $this->assertTrue(Security::validateCsrf($token));
    }

    public function test_validateCsrf_returns_false_for_wrong_token(): void
    {
        Security::csrfToken();
        $this->assertFalse(Security::validateCsrf('wrong_token'));
    }

    public function test_validateCsrf_returns_false_when_no_session_token(): void
    {
        unset($_SESSION['csrf_token']);
        $this->assertFalse(Security::validateCsrf('any_token'));
    }

    // --- sanitizeInput ---

    public function test_sanitizeInput_escapes_script_tags(): void
    {
        $result = Security::sanitizeInput('<script>alert(1)</script>');
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    public function test_sanitizeInput_escapes_single_and_double_quotes(): void
    {
        $this->assertStringContainsString('&quot;', Security::sanitizeInput('"'));
        // ENT_HTML5 uses &apos; for single quotes (not &#039;)
        $this->assertStringContainsString('&apos;', Security::sanitizeInput("'"));
    }

    public function test_sanitizeInput_trims_whitespace(): void
    {
        $this->assertSame('hello', Security::sanitizeInput('  hello  '));
    }

    public function test_sanitizeInput_handles_array_recursively(): void
    {
        $input  = ['name' => '<b>test</b>', 'nested' => ['val' => '<i>x</i>']];
        $result = Security::sanitizeInput($input);
        $this->assertSame('&lt;b&gt;test&lt;/b&gt;', $result['name']);
        $this->assertSame('&lt;i&gt;x&lt;/i&gt;', $result['nested']['val']);
    }

    public function test_sanitizeInput_passes_integer_unchanged(): void
    {
        $this->assertSame(42, Security::sanitizeInput(42));
    }

    // --- validateInput ---

    public function test_validateInput_no_errors_for_valid_data(): void
    {
        $data   = ['email' => 'user@example.com', 'name' => 'Alice'];
        $rules  = ['email' => ['required' => true, 'type' => 'email'], 'name' => ['required' => true]];
        $this->assertSame([], Security::validateInput($data, $rules));
    }

    public function test_validateInput_required_field_missing(): void
    {
        $errors = Security::validateInput([], ['name' => ['required' => true]]);
        $this->assertArrayHasKey('name', $errors);
    }

    public function test_validateInput_invalid_email(): void
    {
        $errors = Security::validateInput(['email' => 'not-an-email'], ['email' => ['type' => 'email']]);
        $this->assertArrayHasKey('email', $errors);
    }

    public function test_validateInput_valid_email_passes(): void
    {
        $errors = Security::validateInput(['email' => 'ok@example.com'], ['email' => ['type' => 'email']]);
        $this->assertSame([], $errors);
    }

    public function test_validateInput_invalid_url(): void
    {
        $errors = Security::validateInput(['site' => 'not-a-url'], ['site' => ['type' => 'url']]);
        $this->assertArrayHasKey('site', $errors);
    }

    public function test_validateInput_invalid_int(): void
    {
        $errors = Security::validateInput(['age' => 'abc'], ['age' => ['type' => 'int']]);
        $this->assertArrayHasKey('age', $errors);
    }

    public function test_validateInput_valid_int_passes(): void
    {
        $errors = Security::validateInput(['age' => '25'], ['age' => ['type' => 'int']]);
        $this->assertSame([], $errors);
    }

    public function test_validateInput_min_length_violation(): void
    {
        $errors = Security::validateInput(['pw' => 'ab'], ['pw' => ['min_length' => 8]]);
        $this->assertArrayHasKey('pw', $errors);
    }

    public function test_validateInput_max_length_violation(): void
    {
        $errors = Security::validateInput(['bio' => str_repeat('x', 300)], ['bio' => ['max_length' => 255]]);
        $this->assertArrayHasKey('bio', $errors);
    }

    public function test_validateInput_length_within_bounds_passes(): void
    {
        $errors = Security::validateInput(['name' => 'Alice'], ['name' => ['min_length' => 2, 'max_length' => 50]]);
        $this->assertSame([], $errors);
    }
}
