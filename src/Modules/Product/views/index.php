<div class="row mb-3">
    <div class="col-md-6">
        <h2><i class="fas fa-list"></i> <?php echo $title; ?></h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="?action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New
        </a>
    </div>
</div>

<?php $this->renderFlashMessages(); ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php if (!empty($items)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php foreach ($fields as $field): ?>
                        <th><?php echo ucfirst($field['name']); ?></th>
                        <?php endforeach; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo $item['id']; ?></td>
                        <?php foreach ($fields as $field): ?>
                        <td><?php echo htmlspecialchars($item[$field['name']] ?? '—'); ?></td>
                        <?php endforeach; ?>
                        <td>
                            <a href="?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="?action=delete&id=<?php echo $item['id']; ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Are you sure you want to delete this item?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No items found. <a href="?action=create">Create your first item</a>.
        </div>
        <?php endif; ?>
    </div>
</div>