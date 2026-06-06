<?php
/*
 *   Copyright (c) 2023 BitsHost — All rights reserved.
 *   https://bitshost.biz/
 */

namespace App\Common\Bmvc;

class BaseView
{
    protected $globals = [
        'settings' => [
            'theme'            => 'light',
            'site_name'        => 'Dashboard',
            'items_per_page'   => '10',
            'maintenance_mode' => 'false',
        ]
    ];


    /** Returns 'bv-active' when the given URL's path matches the current request path. */
    protected function isActive(string $url): string
    {
        $current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $path    = parse_url($url, PHP_URL_PATH) ?? '';
        return $path !== '' && $current === $path ? 'bv-active' : '';
    }

    /** Returns 'bv-active' when ANY of the given URLs matches — used for dropdown parents. */
    protected function dropActive(array $urls): string
    {
        $current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        foreach ($urls as $url) {
            $path = parse_url($url, PHP_URL_PATH) ?? '';
            if ($path !== '' && $current === $path) return 'bv-active';
        }
        return '';
    }

    public function addGlobal($key, $value): void
    {
        if ($key === 'settings' && isset($this->globals['settings'])) {
            $this->globals['settings'] = array_merge($this->globals['settings'], $value);
        } else {
            $this->globals[$key] = $value;
        }
    }

    public function getGlobal($key): mixed
    {
        return $this->globals[$key] ?? null;
    }

    // ---------------------------------------------------------------
    // Navigation bar
    // ---------------------------------------------------------------

    public function menu(): void
    {
        $base      = BASE_URL;
        $role      = $_SESSION['role']   ?? '';
        $uname     = htmlspecialchars($_SESSION['username'] ?? '');
        $logged    = isset($_SESSION['logged']) && $_SESSION['logged'] === true;
        $roleBadge = match($role) {
            'platform_admin' => ['label' => 'Platform Admin', 'bg' => '#1e3a5f', 'fg' => '#93c5fd'],
            'tenant_owner'   => ['label' => 'Owner',          'bg' => '#14532d', 'fg' => '#86efac'],
            'tenant_user'    => ['label' => 'Member',         'bg' => '#1e293b', 'fg' => '#94a3b8'],
            default          => null,
        };
        include __DIR__ . '/../Assets/bv-nav.php';
    }

    // ---------------------------------------------------------------
    // Page shell
    // ---------------------------------------------------------------

    public function startHead(string $title): void
    {
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <style><?php echo file_get_contents(__DIR__ . '/../Assets/bv-styles.css'); ?></style>
        <?php
    }

    public function endHead(): void
    {
        ?>
</head>
        <?php
    }

    public function startBody(string $title, string $actions = ''): void
    {
        ?>
<body>
<?php $this->menu(); ?>
<div class="bv-page">
    <div class="bv-page-header">
        <h1 class="bv-page-title"><?php echo htmlspecialchars($title); ?></h1>
        <?php if ($actions !== ''): ?><div class="bv-page-actions"><?php echo $actions; ?></div><?php endif; ?>
    </div><!-- /.bv-page-header -->
    <div class="bv-content">
    <?php
    }

    /**
     * Wrap a block of content in a styled card section.
     *
     * Example:
     *   $this->startSection('Users');
     *   // table or form HTML
     *   $this->endSection();
     */
    public function startSection(string $title = ''): void
    {
        echo '<div class="bv-section">';
        if ($title !== '') {
            echo '<div class="bv-section-header">' . htmlspecialchars($title) . '</div>';
        }
        echo '<div class="bv-section-body">';
    }

    public function endSection(): void
    {
        echo '</div></div><!-- /.bv-section -->';
    }

    public function endBody(): void
    {
        ?>
    </div><!-- /.bv-content -->
</div><!-- /.bv-page -->
        <?php
    }

    public function startFooter(): void
    {
        ?>
<footer class="bv-footer">
        <?php
    }

    public function endFooter(): void
    {
        ?>
    <p>
        &copy; <?php echo date('Y'); ?>
        <a href="https://bitshost.biz/free-web-hosting.html" target="_blank">BitsHost Cloud</a>
        &mdash; <a href="https://upmvc.com" target="_blank">upMVC</a>
    </p>
</footer>
</body>
</html>
        <?php
    }
}

