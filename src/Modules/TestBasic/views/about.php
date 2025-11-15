<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-info text-white">
                <h3 class="mb-0">
                    <i class="fas fa-info-circle"></i> <?php echo $title; ?>
                </h3>
            </div>
            <div class="card-body">
                <p class="lead"><?php echo $content ?? 'About this enhanced module'; ?></p>
                
                <h5 class="mt-4"><i class="fas fa-cog"></i> Technical Information</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <?php foreach ($tech_info ?? [] as $key => $value): ?>
                        <tr>
                            <td><strong><?php echo ucfirst(str_replace('_', ' ', $key)); ?></strong></td>
                            <td><?php echo is_bool($value) ? ($value ? 'Yes' : 'No') : htmlspecialchars($value); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                
                <div class="mt-4">
                    <a href="<?php echo BASE_URL ?? ''; ?>/testbasic" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Module
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>