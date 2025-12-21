<div class="row">
    <div class="col-md-12 mb-4">
        <h2><i class="fas fa-tachometer-alt"></i> <?php echo $title; ?></h2>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Total Items</h6>
                        <h2 class="mb-0"><?php echo $stats['total_items'] ?? 0; ?></h2>
                    </div>
                    <i class="fas fa-database fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Active</h6>
                        <h2 class="mb-0"><?php echo $stats['active_items'] ?? 0; ?></h2>
                    </div>
                    <i class="fas fa-check-circle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Recent Activity</h6>
                        <h2 class="mb-0"><?php echo $stats['recent_activity'] ?? 0; ?></h2>
                    </div>
                    <i class="fas fa-chart-line fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Pending</h6>
                        <h2 class="mb-0"><?php echo $stats['pending_items'] ?? 0; ?></h2>
                    </div>
                    <i class="fas fa-clock fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Items -->
<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-list"></i> Recent Items</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_items)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_items as $item): ?>
                            <tr>
                                <td><?php echo $item['id'] ?? '—'; ?></td>
                                <td><?php echo htmlspecialchars($item['title'] ?? $item['name'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge bg-success">Active</span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($item['created_at'] ?? 'now')); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i> No items found. This is a demo dashboard.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($debug_mode ?? false): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="fas fa-bug"></i> Debug Information</h6>
            </div>
            <div class="card-body">
                <p><strong>Environment:</strong> <span class="badge bg-secondary"><?php echo $app_env; ?></span></p>
                <p><strong>Module:</strong> <code><?php echo $module_name; ?></code></p>
                <p><strong>Stats:</strong> <code><?php print_r($stats); ?></code></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>