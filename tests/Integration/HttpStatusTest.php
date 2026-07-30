<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Asserts that the router sends real HTTP status lines.
 *
 * Why this test boots a web server instead of calling the router directly:
 * http_response_code() is a no-op under the CLI SAPI — it returns false and
 * sets nothing. So a plain unit test cannot distinguish a router that sends
 * 404 from one that sends nothing at all, which is exactly how
 * Router::handle404() shipped a 200 for every missing route: it rendered the
 * right page, and no test could see the wrong status behind it.
 *
 * The server runs the fixture front controller in fixtures/, which wires up
 * the Router alone — no config, no database — so this runs anywhere the rest
 * of the suite runs.
 */
final class HttpStatusTest extends TestCase
{
    /** @var resource|null */
    private static $process = null;

    private static string $base = '';

    /** @var array<int, resource> */
    private static array $pipes = [];

    public static function setUpBeforeClass(): void
    {
        if (!function_exists('proc_open')) {
            self::markTestSkipped('proc_open is disabled; cannot start a web server.');
        }

        $port    = self::reservePort();
        $fixture = __DIR__ . '/fixtures';
        $script  = $fixture . '/http-status-server.php';

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open(
            [PHP_BINARY, '-S', '127.0.0.1:' . $port, '-t', $fixture, $script],
            $descriptors,
            self::$pipes,
            $fixture,
            null,
            // Without bypass_shell, Windows wraps the server in cmd.exe and
            // proc_terminate() then kills the wrapper while the server keeps
            // holding the port.
            ['bypass_shell' => true]
        );

        if (!is_resource($process)) {
            self::markTestSkipped('Could not start PHP built-in server.');
        }

        self::$process = $process;
        self::$base    = 'http://127.0.0.1:' . $port;

        if (!self::waitForServer($port)) {
            self::stopServer();
            self::markTestSkipped('PHP built-in server did not come up on port ' . $port . '.');
        }
    }

    public static function tearDownAfterClass(): void
    {
        self::stopServer();
    }

    public function testMissingRouteReturns404(): void
    {
        $response = $this->request('GET', '/definitely-not-a-route');

        $this->assertSame(
            404,
            $response['status'],
            'A missing route must answer 404. Returning 200 here is the original bug: '
            . 'the error page renders either way, so only the status line reveals it.'
        );
    }

    public function testMissingRouteStillRendersTheErrorPage(): void
    {
        $response = $this->request('GET', '/definitely-not-a-route');

        $this->assertStringContainsString(
            'Page Not Found',
            $response['body'],
            'The 404 status must not come at the cost of the human-readable page.'
        );
    }

    public function testExistingRouteReturns200(): void
    {
        $response = $this->request('GET', '/hit');

        $this->assertSame(200, $response['status'], 'A registered route must still answer 200.');
        $this->assertStringContainsString('smoke ok', $response['body']);
    }

    public function testWrongMethodOnExistingRouteReturns405(): void
    {
        $response = $this->request('POST', '/hit');

        $this->assertSame(
            405,
            $response['status'],
            'A route that exists but does not accept the method is 405, not 404 and not 200.'
        );
    }

    public function testMethodNotAllowedAdvertisesAllowedMethods(): void
    {
        $response = $this->request('POST', '/hit');

        $this->assertArrayHasKey('allow', $response['headers']);
        $this->assertStringContainsStringIgnoringCase('GET', $response['headers']['allow']);
    }

    /**
     * @return array{status: int, body: string, headers: array<string, string>}
     */
    private function request(string $method, string $path): array
    {
        $context = stream_context_create([
            'http' => [
                'method'          => $method,
                // Without this, PHP throws on 4xx and returns no body.
                'ignore_errors'   => true,
                'follow_location' => 0,
                'timeout'         => 10,
                'header'          => "Content-Length: 0\r\n",
            ],
        ]);

        $body = @file_get_contents(self::$base . $path, false, $context);

        $this->assertNotFalse($body, "Request to $path produced no response.");

        // $http_response_header is injected into local scope by the http wrapper.
        $raw     = $http_response_header ?? [];
        $status  = 0;
        $headers = [];

        foreach ($raw as $i => $line) {
            if ($i === 0 || preg_match('#^HTTP/#i', $line)) {
                if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $m)) {
                    // Later status lines win, so a 100-continue preamble does
                    // not mask the real response.
                    $status  = (int) $m[1];
                    $headers = [];
                }
                continue;
            }

            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $headers[strtolower(trim($parts[0]))] = trim($parts[1]);
            }
        }

        return ['status' => $status, 'body' => (string) $body, 'headers' => $headers];
    }

    /**
     * Ask the OS for a free port, then release it so the server can claim it.
     */
    private static function reservePort(): int
    {
        $socket = @stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);

        if ($socket === false) {
            self::markTestSkipped("Could not reserve a local port: $errstr ($errno)");
        }

        $name = stream_socket_get_name($socket, false);
        fclose($socket);

        $port = (int) substr((string) $name, (int) strrpos((string) $name, ':') + 1);

        return $port > 0 ? $port : 8999;
    }

    private static function waitForServer(int $port, float $timeoutSeconds = 10.0): bool
    {
        $deadline = microtime(true) + $timeoutSeconds;

        while (microtime(true) < $deadline) {
            $client = @stream_socket_client(
                'tcp://127.0.0.1:' . $port,
                $errno,
                $errstr,
                0.5
            );

            if (is_resource($client)) {
                fclose($client);
                return true;
            }

            usleep(100_000);
        }

        return false;
    }

    private static function stopServer(): void
    {
        foreach (self::$pipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }
        self::$pipes = [];

        if (is_resource(self::$process)) {
            proc_terminate(self::$process);
            proc_close(self::$process);
        }

        self::$process = null;
    }
}
