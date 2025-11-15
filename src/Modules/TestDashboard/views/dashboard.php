<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">
                    <i class="fas fa-rocket"></i> <?php echo $title; ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-success border-0">
                    <h5><i class="fas fa-check-circle"></i> Enhanced Module Active!</h5>
                    <p class="mb-0"><?php echo $message ?? 'Enhanced module loaded successfully!'; ?></p>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-magic text-primary"></i> Enhanced Features
                                </h5>
                                <ul class="list-unstyled">
                                    <?php foreach ($features ?? [] as $feature): ?>
                                    <li><i class="fas fa-check text-success"></i> <?php echo $feature; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-info text-info"></i> Route Information
                                </h5>
                                <table class="table table-sm">
                                    <?php foreach ($route_info ?? [] as $key => $value): ?>
                                    <tr>
                                        <td><strong><?php echo ucfirst(str_replace('_', ' ', $key)); ?></strong></td>
                                        <td><code><?php echo htmlspecialchars($value); ?></code></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </table>
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
                                <p><strong>Auto-Discovery:</strong> <span class="badge bg-success">Enabled via InitModsImproved.php</span></p>
                                <p><strong>Module Path:</strong> <code>src/Modules/TestDashboard/</code></p>
                                <a href="<?php echo BASE_URL ?? ''; ?>/testdashboard/api" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-code"></i> Test API Endpoint
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>