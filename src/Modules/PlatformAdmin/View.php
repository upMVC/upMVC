<?php

namespace App\Modules\PlatformAdmin;

use App\Common\Bmvc\BaseView;
use App\Etc\Security;

class View
{
    private string $title = 'Platform Admin — Tenants';

    public function renderList(array $data): void
    {
        $tenants = $data['tenants'] ?? [];
        $plans   = $data['plans']   ?? [];
        $total   = $data['total']   ?? 0;
        $limit   = $data['limit']   ?? 50;
        $offset  = $data['offset']  ?? 0;

        $csrf       = Security::csrfToken();
        $flashType  = $_SESSION['flash_type'] ?? null;
        $flashMsg   = $_SESSION['flash_msg']  ?? null;
        unset($_SESSION['flash_type'], $_SESSION['flash_msg']);

        $base = BASE_URL;

        $baseView = new BaseView();
        $baseView->startHead($this->title);
        $baseView->endHead();
        $baseView->startBody($this->title);
        ?>

        <div style="max-width:1100px;margin:30px auto;padding:0 16px;font-family:sans-serif;">

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                <h1 style="margin:0;font-size:1.5rem;">Platform Admin — Tenants
                    <small style="font-size:.75rem;color:#666;margin-left:8px;"><?php echo $total; ?> total</small>
                </h1>
            </div>

            <?php if ($flashMsg): ?>
            <div style="padding:10px 16px;border-radius:6px;margin-bottom:16px;
                        background:<?php echo $flashType === 'success' ? '#d1fae5' : '#fee2e2'; ?>;
                        color:<?php echo $flashType === 'success' ? '#065f46' : '#991b1b'; ?>;
                        border:1px solid <?php echo $flashType === 'success' ? '#6ee7b7' : '#fca5a5'; ?>;">
                <?php echo htmlspecialchars($flashMsg); ?>
            </div>
            <?php endif; ?>

            <?php if (empty($tenants)): ?>
                <p style="color:#666;">No tenants found. Run the seed script to add test data.</p>
            <?php else: ?>
            <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
                <thead>
                    <tr style="background:#f1f5f9;text-align:left;">
                        <th style="padding:10px 12px;border-bottom:2px solid #e2e8f0;">ID</th>
                        <th style="padding:10px 12px;border-bottom:2px solid #e2e8f0;">Slug</th>
                        <th style="padding:10px 12px;border-bottom:2px solid #e2e8f0;">Name</th>
                        <th style="padding:10px 12px;border-bottom:2px solid #e2e8f0;">Status</th>
                        <th style="padding:10px 12px;border-bottom:2px solid #e2e8f0;">Plan</th>
                        <th style="padding:10px 12px;border-bottom:2px solid #e2e8f0;">Created</th>
                        <th style="padding:10px 12px;border-bottom:2px solid #e2e8f0;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($tenants as $t):
                    $statusColor = match($t['status']) {
                        'active'    => '#d1fae5',
                        'trial'     => '#fef9c3',
                        'suspended' => '#fee2e2',
                        default     => '#f1f5f9',
                    };
                ?>
                    <tr style="border-bottom:1px solid #e2e8f0;">
                        <td style="padding:10px 12px;"><?php echo (int) $t['id']; ?></td>
                        <td style="padding:10px 12px;font-family:monospace;"><?php echo htmlspecialchars($t['slug']); ?></td>
                        <td style="padding:10px 12px;"><?php echo htmlspecialchars($t['name']); ?></td>
                        <td style="padding:10px 12px;">
                            <span style="padding:2px 8px;border-radius:12px;background:<?php echo $statusColor; ?>;font-size:.8rem;">
                                <?php echo htmlspecialchars($t['status']); ?>
                            </span>
                        </td>
                        <td style="padding:10px 12px;"><?php echo htmlspecialchars($t['plan_name'] ?? '—'); ?></td>
                        <td style="padding:10px 12px;color:#64748b;font-size:.8rem;"><?php echo htmlspecialchars(substr($t['created_at'], 0, 10)); ?></td>
                        <td style="padding:10px 12px;">
                            <div style="display:flex;gap:8px;flex-wrap:wrap;">

                                <!-- Update status -->
                                <form method="POST" action="<?php echo $base; ?>/platform-admin/tenants/<?php echo (int) $t['id']; ?>/status"
                                      style="display:flex;gap:4px;align-items:center;">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                                    <select name="status" style="padding:3px 6px;border:1px solid #cbd5e1;border-radius:4px;font-size:.8rem;">
                                        <?php foreach (['active','trial','suspended'] as $s): ?>
                                        <option value="<?php echo $s; ?>" <?php echo $t['status'] === $s ? 'selected' : ''; ?>>
                                            <?php echo $s; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit"
                                            style="padding:3px 8px;background:#3b82f6;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:.8rem;">
                                        Set
                                    </button>
                                </form>

                                <!-- Update plan -->
                                <form method="POST" action="<?php echo $base; ?>/platform-admin/tenants/<?php echo (int) $t['id']; ?>/plan"
                                      style="display:flex;gap:4px;align-items:center;">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                                    <select name="plan_id" style="padding:3px 6px;border:1px solid #cbd5e1;border-radius:4px;font-size:.8rem;">
                                        <?php foreach ($plans as $p): ?>
                                        <option value="<?php echo (int) $p['id']; ?>" <?php echo (int) $t['plan_id'] === (int) $p['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($p['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit"
                                            style="padding:3px 8px;background:#8b5cf6;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:.8rem;">
                                        Plan
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total > $limit): ?>
            <div style="margin-top:16px;display:flex;gap:8px;">
                <?php if ($offset > 0): ?>
                <a href="<?php echo $base; ?>/platform-admin?offset=<?php echo max(0, $offset - $limit); ?>&limit=<?php echo $limit; ?>"
                   style="padding:6px 14px;border:1px solid #cbd5e1;border-radius:4px;text-decoration:none;color:#374151;">
                    &larr; Prev
                </a>
                <?php endif; ?>
                <?php if ($offset + $limit < $total): ?>
                <a href="<?php echo $base; ?>/platform-admin?offset=<?php echo $offset + $limit; ?>&limit=<?php echo $limit; ?>"
                   style="padding:6px 14px;border:1px solid #cbd5e1;border-radius:4px;text-decoration:none;color:#374151;">
                    Next &rarr;
                </a>
                <?php endif; ?>
                <span style="padding:6px 0;color:#64748b;font-size:.85rem;">
                    Showing <?php echo $offset + 1; ?>–<?php echo min($offset + $limit, $total); ?> of <?php echo $total; ?>
                </span>
            </div>
            <?php endif; ?>

            <?php endif; ?>

        </div>

        <?php
        $baseView->endBody();
    }
}
