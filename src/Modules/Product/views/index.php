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
        
        <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center">
                <?php if ($pagination['current_page'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            <div class="text-center text-muted">
                <small>
                    Showing page <?php echo $pagination['current_page']; ?> of <?php echo $pagination['total_pages']; ?>
                    (<?php echo $pagination['total_items']; ?> total items)
                </small>
            </div>
        </nav>
        <?php endif; ?>
    </div>
</div>