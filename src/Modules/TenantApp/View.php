<?php

namespace App\Modules\TenantApp;

use App\Etc\Security;

class View
{
    // ---------------------------------------------------------------
    // Shared: app shell open
    // ---------------------------------------------------------------

    private function shellOpen(array $tenant, string $activePage): void
    {
        $base      = BASE_URL;
        $slug      = htmlspecialchars($tenant['slug']);
        $name      = htmlspecialchars($tenant['name']);
        $status    = htmlspecialchars($tenant['status']);
        $username  = htmlspecialchars($_SESSION['username'] ?? '');
        $planName  = htmlspecialchars($tenant['plan_name'] ?? 'Free');

        $statusBg = match($tenant['status']) {
            'active'    => '#dcfce7', 'trial'  => '#fef9c3',
            'suspended' => '#fee2e2', default  => '#f1f5f9',
        };
        $statusFg = match($tenant['status']) {
            'active'    => '#166534', 'trial'  => '#92400e',
            'suspended' => '#991b1b', default  => '#374151',
        };
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $name; ?> — App</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            display: flex;
            min-height: 100vh;
        }

        /* ---- Sidebar ---- */
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: #0f172a;
            color: #e2e8f0;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }
        .sidebar-brand {
            padding: 24px 20px 16px;
            border-bottom: 1px solid #1e293b;
        }
        .sidebar-brand h2 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #f8fafc;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-brand .plan-badge {
            display: inline-block;
            margin-top: 6px;
            padding: 2px 8px;
            background: #1e3a5f;
            color: #93c5fd;
            border-radius: 10px;
            font-size: .73rem;
            font-weight: 600;
        }
        .sidebar-nav { padding: 12px 0; flex: 1; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            color: #94a3b8;
            text-decoration: none;
            font-size: .9rem;
            transition: background .15s, color .15s;
            border-left: 3px solid transparent;
        }
        .sidebar-nav a:hover {
            background: #1e293b;
            color: #e2e8f0;
        }
        .sidebar-nav a.active {
            background: #1e293b;
            color: #38bdf8;
            border-left-color: #38bdf8;
            font-weight: 600;
        }
        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid #1e293b;
            font-size: .8rem;
            color: #475569;
        }
        .sidebar-footer a {
            color: #64748b;
            text-decoration: none;
        }
        .sidebar-footer a:hover { color: #e2e8f0; }

        /* ---- Main ---- */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 28px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .topbar-title { font-size: 1rem; font-weight: 600; color: #1e293b; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .topbar-user {
            font-size: .85rem;
            color: #64748b;
        }
        .status-badge {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: .78rem;
            font-weight: 600;
            background: <?php echo $statusBg; ?>;
            color: <?php echo $statusFg; ?>;
        }
        .content { padding: 28px; flex: 1; }

        /* ---- Cards ---- */
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
        }
        .card-label { font-size: .75rem; color: #64748b; text-transform: uppercase; letter-spacing: .05em; }
        .card-value { font-size: 1.6rem; font-weight: 700; color: #0f172a; margin-top: 6px; }
        .card-sub   { font-size: .82rem; color: #64748b; margin-top: 4px; }
        .card-link  { font-size: .82rem; color: #3b82f6; text-decoration: none; }
        .card-link:hover { text-decoration: underline; }

        /* ---- Section ---- */
        .section { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 24px; }
        .section-header {
            padding: 14px 20px;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            font-size: .95rem;
            color: #0f172a;
        }
        .section-body { padding: 20px; }

        /* ---- Table ---- */
        .tbl { width: 100%; border-collapse: collapse; font-size: .88rem; }
        .tbl th {
            padding: 10px 14px;
            text-align: left;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            font-weight: 600;
            color: #374151;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .tbl td { padding: 11px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .tbl tr:last-child td { border-bottom: none; }
        .tbl tr:hover td { background: #f8fafc; }

        /* ---- Badges ---- */
        .badge {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 10px;
            font-size: .78rem;
            font-weight: 600;
        }
        .badge-owner  { background: #dbeafe; color: #1e40af; }
        .badge-user   { background: #f1f5f9; color: #374151; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }

        /* ---- Features ---- */
        .features { display: flex; gap: 16px; flex-wrap: wrap; }
        .feature-item { display: flex; align-items: center; gap: 6px; font-size: .88rem; color: #374151; }
        .feat-on  { color: #10b981; font-size: 1rem; }
        .feat-off { color: #cbd5e1; font-size: 1rem; }

        /* ---- Alert ---- */
        .alert-suspended {
            padding: 14px 18px;
            background: #fee2e2;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            color: #991b1b;
            margin-bottom: 20px;
        }

        /* ---- Nav divider / external link ---- */
        .sidebar-nav .nav-divider { border-top: 1px solid #1e293b; margin: 8px 0; }
        .sidebar-nav a.nav-ext { font-size: .78rem; color: #475569; padding: 6px 20px; border-left: none; }
        .sidebar-nav a.nav-ext:hover { color: #94a3b8; background: transparent; }

        /* ---- Responsive ---- */
        @media (max-width: 640px) {
            .sidebar { width: 200px; }
            .content { padding: 16px; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <h2>🏢 <?php echo $name; ?></h2>
        <span class="plan-badge"><?php echo $planName; ?></span>
    </div>
    <nav class="sidebar-nav">
        <a href="<?php echo $base; ?>/app/<?php echo $slug; ?>/admin"
           class="<?php echo $activePage === 'admin-dashboard' ? 'active' : ''; ?>">
            📊 Dashboard
        </a>
        <a href="<?php echo $base; ?>/app/<?php echo $slug; ?>/admin/users"
           class="<?php echo $activePage === 'admin-users' ? 'active' : ''; ?>">
            👥 Users
        </a>
        <div class="nav-divider"></div>
        <a href="<?php echo $base; ?>/app/<?php echo $slug; ?>" class="nav-ext">
            🌐 View Public Site ↗
        </a>
    </nav>
    <div class="sidebar-footer">
        Logged in as <strong><?php echo $username; ?></strong><br>
        <a href="<?php echo $base; ?>/logout">Sign out</a>
    </div>
</aside>

<!-- Main -->
<div class="main">
    <div class="topbar">
        <span class="topbar-title"><?php
            echo match($activePage) {
                'admin-users'    => 'Users',
                'admin-settings' => 'Settings',
                default          => 'Dashboard',
            };
        ?></span>
        <div class="topbar-right">
            <span class="topbar-user"><?php echo $username; ?></span>
            <span class="status-badge"><?php echo $status; ?></span>
        </div>
    </div>
    <div class="content">
<?php
    }

    private function shellClose(): void
    {
        echo "    </div>\n</div>\n</body>\n</html>\n";
    }

    // ---------------------------------------------------------------
    // Dashboard page
    // ---------------------------------------------------------------

    public function renderAdminDashboard(array $data): void
    {
        $tenant     = $data['tenant'];
        $userCount  = $data['user_count'] ?? 0;
        $base       = BASE_URL;
        $slug       = htmlspecialchars($tenant['slug']);

        $features = is_string($tenant['plan_features'] ?? null)
            ? (json_decode($tenant['plan_features'], true) ?? [])
            : [];
        $limits = is_string($tenant['plan_limits'] ?? null)
            ? (json_decode($tenant['plan_limits'], true) ?? [])
            : [];

        $this->shellOpen($tenant, 'admin-dashboard');
        ?>

        <?php if ($tenant['status'] === 'suspended'): ?>
        <div class="alert-suspended">
            ⚠️ Your account is <strong>suspended</strong>. Please contact support to reactivate.
        </div>
        <?php endif; ?>

        <!-- Stat cards -->
        <div class="cards">
            <div class="card">
                <div class="card-label">Organisation</div>
                <div class="card-value" style="font-size:1.15rem;margin-top:8px;">
                    <?php echo htmlspecialchars($tenant['name']); ?>
                </div>
                <div class="card-sub">slug: <?php echo htmlspecialchars($tenant['slug']); ?></div>
            </div>
            <div class="card">
                <div class="card-label">Plan</div>
                <div class="card-value"><?php echo htmlspecialchars($tenant['plan_name'] ?? '—'); ?></div>
                <div class="card-sub">
                    $<?php echo number_format((float)($tenant['plan_price'] ?? 0), 2); ?>/mo
                </div>
            </div>
            <div class="card">
                <div class="card-label">Users</div>
                <div class="card-value"><?php echo $userCount; ?></div>
                <a class="card-link" href="<?php echo $base; ?>/app/<?php echo $slug; ?>/admin/users">
                    Manage →
                </a>
            </div>
            <div class="card">
                <div class="card-label">Member since</div>
                <div class="card-value" style="font-size:1rem;margin-top:8px;">
                    <?php echo htmlspecialchars(substr($tenant['created_at'], 0, 10)); ?>
                </div>
            </div>
        </div>

        <!-- Plan features -->
        <?php if (!empty($features)): ?>
        <div class="section">
            <div class="section-header">Plan Features</div>
            <div class="section-body">
                <div class="features">
                    <?php foreach ($features as $feat => $enabled): ?>
                    <div class="feature-item">
                        <span class="<?php echo $enabled ? 'feat-on' : 'feat-off'; ?>">
                            <?php echo $enabled ? '✔' : '✖'; ?>
                        </span>
                        <?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($feat))); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($limits)): ?>
                <div style="margin-top:14px;font-size:.82rem;color:#64748b;display:flex;gap:20px;flex-wrap:wrap;">
                    <?php foreach ($limits as $key => $val): ?>
                    <span>
                        <?php echo htmlspecialchars(str_replace('_', ' ', $key)); ?>:
                        <strong style="color:#0f172a;"><?php echo $val == 0 ? '∞' : htmlspecialchars((string)$val); ?></strong>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Users preview -->
        <?php if (!empty($data['users_preview'] ?? [])): ?>
        <div class="section">
            <div class="section-header">
                Team Members
                <a href="<?php echo $base; ?>/app/<?php echo $slug; ?>/admin/users"
                   style="float:right;font-size:.82rem;font-weight:400;color:#3b82f6;text-decoration:none;">
                    View all →
                </a>
            </div>
            <div style="padding:0;">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data['users_preview'] as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['name']); ?></td>
                            <td style="font-family:monospace;font-size:.83rem;"><?php echo htmlspecialchars($u['username']); ?></td>
                            <td>
                                <span class="badge <?php echo $u['role'] === 'tenant_owner' ? 'badge-owner' : 'badge-user'; ?>">
                                    <?php echo $u['role'] === 'tenant_owner' ? 'Owner' : 'User'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $u['state'] ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo $u['state'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php
        $this->shellClose();
    }

    // ---------------------------------------------------------------
    // Users page
    // ---------------------------------------------------------------

    public function renderAdminUsers(array $data): void
    {
        $tenant = $data['tenant'];
        $users  = $data['users'] ?? [];

        $this->shellOpen($tenant, 'admin-users');
        ?>

        <div class="section">
            <div class="section-header">Team Members — <?php echo count($users); ?> total</div>
            <?php if (empty($users)): ?>
                <div class="section-body" style="color:#64748b;">No users yet.</div>
            <?php else: ?>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Since</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['name']); ?></td>
                        <td style="font-family:monospace;font-size:.83rem;"><?php echo htmlspecialchars($u['username']); ?></td>
                        <td style="color:#64748b;"><?php echo htmlspecialchars($u['email']); ?></td>
                        <td>
                            <span class="badge <?php echo $u['role'] === 'tenant_owner' ? 'badge-owner' : 'badge-user'; ?>">
                                <?php echo $u['role'] === 'tenant_owner' ? 'Owner' : 'User'; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo $u['state'] ? 'badge-active' : 'badge-inactive'; ?>">
                                <?php echo $u['state'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td style="color:#94a3b8;font-size:.8rem;">
                            <?php echo htmlspecialchars(substr($u['stamp'] ?? '', 0, 10)); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <?php
        $this->shellClose();
    }

    // ---------------------------------------------------------------
    // Public: Tenant frontend (no auth required)
    // ---------------------------------------------------------------

    public function renderFrontend(array $data): void
    {
        $tenant  = $data['tenant'];
        $isAdmin = $data['is_admin'] ?? false;
        $base    = BASE_URL;
        $slug    = htmlspecialchars($tenant['slug']);
        $name    = htmlspecialchars($tenant['name']);
        $status  = $tenant['status'];
        $initial = strtoupper(substr($tenant['name'], 0, 1));

        $statusBg = match($status) {
            'active'    => '#dcfce7', 'trial'     => '#fef9c3',
            'suspended' => '#fee2e2', default     => '#f1f5f9',
        };
        $statusFg = match($status) {
            'active'    => '#166534', 'trial'     => '#92400e',
            'suspended' => '#991b1b', default     => '#374151',
        };
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $name; ?></title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #fff; color: #1e293b; }

        /* Nav */
        .pub-nav {
            position: sticky; top: 0; z-index: 10;
            background: #fff; border-bottom: 1px solid #e2e8f0;
            padding: 0 32px; height: 60px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .pub-nav-brand { font-size: 1.1rem; font-weight: 700; color: #0f172a; }
        .pub-nav-links { display: flex; align-items: center; gap: 12px; }
        .pub-nav-links a {
            font-size: .9rem; color: #64748b; text-decoration: none;
            padding: 6px 12px; border-radius: 6px; transition: background .15s;
        }
        .pub-nav-links a:hover { background: #f1f5f9; color: #1e293b; }
        .pub-nav-links .btn-login {
            background: #0f172a; color: #fff;
            padding: 7px 18px; border-radius: 7px;
            font-weight: 600; font-size: .88rem;
        }
        .pub-nav-links .btn-login:hover { background: #1e293b; }

        /* Hero */
        .hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            color: #fff; padding: 80px 32px 64px; text-align: center;
        }
        .hero-avatar {
            width: 72px; height: 72px;
            background: rgba(255,255,255,.15); border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; font-weight: 800; color: #fff;
            margin: 0 auto 24px;
        }
        .hero h1 { font-size: 2.2rem; font-weight: 800; margin-bottom: 12px; }
        .hero p  { font-size: 1rem; color: #94a3b8; margin-bottom: 28px; max-width: 460px; margin-left: auto; margin-right: auto; }
        .status-pill {
            display: inline-block; padding: 4px 14px; border-radius: 20px;
            font-size: .85rem; font-weight: 600;
            background: <?php echo $statusBg; ?>; color: <?php echo $statusFg; ?>;
            margin-bottom: 28px;
        }
        .hero-cta {
            display: inline-block; background: #3b82f6; color: #fff;
            padding: 13px 32px; border-radius: 8px;
            font-size: 1rem; font-weight: 600; text-decoration: none;
            transition: background .15s;
        }
        .hero-cta:hover { background: #2563eb; }

        /* Suspended */
        .suspended-banner {
            background: #fee2e2; border: 1px solid #fca5a5;
            padding: 18px 32px; text-align: center; color: #991b1b; font-weight: 500;
        }

        /* Features section */
        .features-section { padding: 64px 32px; background: #f8fafc; }
        .features-section h2 {
            text-align: center; font-size: 1.5rem; font-weight: 700;
            color: #0f172a; margin-bottom: 40px;
        }
        .features-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px; max-width: 900px; margin: 0 auto;
        }
        .feat-card {
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 12px; padding: 28px 24px;
        }
        .feat-card-icon { font-size: 2rem; margin-bottom: 14px; }
        .feat-card h3 { font-size: 1rem; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
        .feat-card p  { font-size: .88rem; color: #64748b; line-height: 1.6; }

        /* Footer */
        .pub-footer {
            padding: 28px 32px; border-top: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
            font-size: .82rem; color: #94a3b8;
        }
    </style>
</head>
<body>

<?php if ($status === 'suspended'): ?>
<div class="suspended-banner">
    ⚠️ This account is currently <strong>suspended</strong>. Please contact support.
</div>
<?php endif; ?>

<header class="pub-nav">
    <span class="pub-nav-brand">🏢 <?php echo $name; ?></span>
    <div class="pub-nav-links">
        <?php if ($isAdmin): ?>
        <a href="<?php echo $base; ?>/app/<?php echo $slug; ?>/admin">⚙ Admin Dashboard</a>
        <?php endif; ?>
        <a href="<?php echo $base; ?>/auth" class="btn-login">Sign In</a>
    </div>
</header>

<section class="hero">
    <div class="hero-avatar"><?php echo $initial; ?></div>
    <h1><?php echo $name; ?></h1>
    <p>The platform built for your business. Sign in to access your account.</p>
    <div class="status-pill"><?php echo htmlspecialchars($status); ?></div><br>
    <?php if ($status !== 'suspended'): ?>
    <a href="<?php echo $base; ?>/auth" class="hero-cta">Sign In to Your Account →</a>
    <?php endif; ?>
</section>

<section class="features-section">
    <h2>Everything you need</h2>
    <div class="features-grid">
        <div class="feat-card">
            <div class="feat-card-icon">📊</div>
            <h3>Analytics Dashboard</h3>
            <p>Get a clear overview of your activity, users, and key metrics in one place.</p>
        </div>
        <div class="feat-card">
            <div class="feat-card-icon">👥</div>
            <h3>Team Management</h3>
            <p>Invite team members, assign roles, and manage access across your organisation.</p>
        </div>
        <div class="feat-card">
            <div class="feat-card-icon">🔒</div>
            <h3>Secure &amp; Reliable</h3>
            <p>Role-based access control, secure sessions, and isolated multi-tenant data.</p>
        </div>
    </div>
</section>

<footer class="pub-footer">
    <span><?php echo $name; ?></span>
    <span>Powered by upMVC SaaS</span>
</footer>

</body>
</html>
<?php
    }
}

