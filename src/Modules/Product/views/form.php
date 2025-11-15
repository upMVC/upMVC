<div class="row">
    <div class="col-md-8 mx-auto">
        <h2><i class="fas fa-edit"></i> <?php echo $title; ?></h2>
        
        <?php $this->renderFlashMessages(); ?>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="POST" action="?action=<?php echo $action; ?>">
                    <?php if (isset($item['id'])): ?>
                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                    <?php endif; ?>
                    
                    <?php foreach ($fields as $field): ?>
                    <div class="mb-3">
                        <label for="<?php echo $field['name']; ?>" class="form-label">
                            <?php echo ucfirst(str_replace('_', ' ', $field['name'])); ?>
                        </label>
                        
                        <?php if ($field['html_type'] === 'textarea'): ?>
                        <textarea 
                            class="form-control" 
                            id="<?php echo $field['name']; ?>" 
                            name="<?php echo $field['name']; ?>"
                            rows="4"
                            required><?php echo htmlspecialchars($item[$field['name']] ?? ''); ?></textarea>
                        
                        <?php elseif ($field['html_type'] === 'select'): ?>
                        <select 
                            class="form-select" 
                            id="<?php echo $field['name']; ?>" 
                            name="<?php echo $field['name']; ?>"
                            required>
                            <option value="">Select...</option>
                            <option value="active" <?php echo ($item[$field['name']] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($item[$field['name']] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                        
                        <?php else: ?>
                        <input 
                            type="<?php echo $field['html_type']; ?>" 
                            class="form-control" 
                            id="<?php echo $field['name']; ?>" 
                            name="<?php echo $field['name']; ?>"
                            value="<?php echo htmlspecialchars($item[$field['name']] ?? ''); ?>"
                            required>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="d-flex justify-content-between">
                        <a href="?" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>