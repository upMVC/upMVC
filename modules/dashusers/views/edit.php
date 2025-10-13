<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .form-floating > label {
            padding-left: 2.75rem;
        }
        .form-floating > .form-control,
        .form-floating > .form-select {
            padding-left: 2.75rem;
        }
        .input-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            z-index: 4;
            color: #6c757d;
        }
        .required::after {
            content: "*";
            color: #dc3545;
            margin-left: 0.25rem;
        }
        .user-info-card {
            background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 15px;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>
                        Edit User
                    </h1>
                    <p class="mb-0 opacity-75">Update user information and permissions</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="/dashusers" class="btn btn-light me-2">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                    <a href="/dashusers/view/<?= $user['id'] ?>" class="btn btn-info">
                        <i class="fas fa-eye"></i> View Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                <!-- User Info Card -->
                <div class="card user-info-card mb-4">
                    <div class="card-body text-center">
                        <div class="avatar-circle mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                            <i class="fas fa-user"></i>
                        </div>
                        <h5 class="mb-1"><?= htmlspecialchars($user['username']) ?></h5>
                        <p class="mb-2 opacity-75"><?= htmlspecialchars($user['email']) ?></p>
                        <div class="d-flex justify-content-center gap-2">
                            <span class="badge bg-light text-dark">
                                <?= ucfirst(str_replace('_', ' ', $user['role'])) ?>
                            </span>
                            <span class="badge bg-light text-dark">
                                <?= ucfirst($user['status']) ?>
                            </span>
                        </div>
                        <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
                        <small class="opacity-75">
                            Member since <?= date('M j, Y', strtotime($user['created_at'])) ?>
                        </small>
                    </div>
                </div>

                <!-- Last Activity -->
                <?php if (!empty($recent_activity)): ?>
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-clock"></i> Recent Activity
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php foreach (array_slice($recent_activity, 0, 5) as $activity): ?>
                        <div class="d-flex align-items-start mb-2">
                            <i class="fas fa-circle text-primary mt-2" style="font-size: 0.5rem;"></i>
                            <div class="ms-2">
                                <small class="text-muted d-block">
                                    <?= date('M j, g:i A', strtotime($activity['created_at'])) ?>
                                </small>
                                <small><?= htmlspecialchars($activity['description'] ?? $activity['action']) ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-8">
                <!-- Alerts -->
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Edit User Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-circle"></i> User Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/dashusers/edit/<?= $user['id'] ?>" novalidate>
                            <div class="row g-3">
                                <!-- Username -->
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <i class="fas fa-user input-icon"></i>
                                        <input type="text" class="form-control <?= !empty($errors['username']) ? 'is-invalid' : '' ?>" 
                                               id="username" name="username" 
                                               value="<?= htmlspecialchars($user['username']) ?>" 
                                               placeholder="Username" required>
                                        <label for="username" class="required">Username</label>
                                        <?php if (!empty($errors['username'])): ?>
                                        <div class="invalid-feedback">
                                            <?= htmlspecialchars($errors['username']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <i class="fas fa-envelope input-icon"></i>
                                        <input type="email" class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>" 
                                               id="email" name="email" 
                                               value="<?= htmlspecialchars($user['email']) ?>" 
                                               placeholder="Email" required>
                                        <label for="email" class="required">Email</label>
                                        <?php if (!empty($errors['email'])): ?>
                                        <div class="invalid-feedback">
                                            <?= htmlspecialchars($errors['email']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- First Name -->
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <i class="fas fa-user-tag input-icon"></i>
                                        <input type="text" class="form-control" 
                                               id="first_name" name="first_name" 
                                               value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" 
                                               placeholder="First Name">
                                        <label for="first_name">First Name</label>
                                    </div>
                                </div>

                                <!-- Last Name -->
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <i class="fas fa-user-tag input-icon"></i>
                                        <input type="text" class="form-control" 
                                               id="last_name" name="last_name" 
                                               value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" 
                                               placeholder="Last Name">
                                        <label for="last_name">Last Name</label>
                                    </div>
                                </div>

                                <!-- New Password -->
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <i class="fas fa-lock input-icon"></i>
                                        <input type="password" class="form-control <?= !empty($errors['password']) ? 'is-invalid' : '' ?>" 
                                               id="password" name="password" placeholder="New Password">
                                        <label for="password">New Password</label>
                                        <button type="button" class="btn btn-outline-secondary position-absolute" 
                                                style="right: 0.75rem; top: 50%; transform: translateY(-50%); z-index: 4; border: none; background: none; padding: 0.25rem;"
                                                onclick="togglePassword('password')">
                                            <i class="fas fa-eye" id="password-eye"></i>
                                        </button>
                                        <?php if (!empty($errors['password'])): ?>
                                        <div class="invalid-feedback">
                                            <?= htmlspecialchars($errors['password']) ?>
                                        </div>
                                        <?php endif; ?>
                                        <div class="form-text">
                                            Leave empty to keep current password
                                        </div>
                                    </div>
                                </div>

                                <!-- Confirm Password -->
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <i class="fas fa-lock input-icon"></i>
                                        <input type="password" class="form-control" 
                                               id="confirm_password" name="confirm_password" placeholder="Confirm New Password">
                                        <label for="confirm_password">Confirm New Password</label>
                                        <button type="button" class="btn btn-outline-secondary position-absolute" 
                                                style="right: 0.75rem; top: 50%; transform: translateY(-50%); z-index: 4; border: none; background: none; padding: 0.25rem;"
                                                onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye" id="confirm_password-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Role -->
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <i class="fas fa-user-shield input-icon"></i>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                            <option value="author" <?= $user['role'] === 'author' ? 'selected' : '' ?>>Author</option>
                                            <option value="editor" <?= $user['role'] === 'editor' ? 'selected' : '' ?>>Editor</option>
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            <?php if (($_SESSION['user']['role'] ?? '') === 'super_admin'): ?>
                                            <option value="super_admin" <?= $user['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                                            <?php endif; ?>
                                        </select>
                                        <label for="role" class="required">Role</label>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <i class="fas fa-toggle-on input-icon"></i>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                            <option value="suspended" <?= $user['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                        </select>
                                        <label for="status" class="required">Status</label>
                                    </div>
                                </div>

                                <!-- Phone -->
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <i class="fas fa-phone input-icon"></i>
                                        <input type="tel" class="form-control" 
                                               id="phone" name="phone" 
                                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                                               placeholder="Phone Number">
                                        <label for="phone">Phone Number</label>
                                    </div>
                                </div>

                                <!-- Bio -->
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control" id="bio" name="bio" 
                                                  style="height: 100px" placeholder="Bio"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                                        <label for="bio">Bio / Description</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="/dashusers" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update User
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Danger Zone -->
                <?php if (($_SESSION['user']['role'] ?? '') === 'super_admin' && $user['id'] != ($_SESSION['user']['id'] ?? 0)): ?>
                <div class="card mt-4 border-danger">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-exclamation-triangle"></i> Danger Zone
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="text-danger">Delete User Account</h6>
                                <p class="text-muted mb-0">
                                    Once you delete this user account, there is no going back. This action cannot be undone.
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <button type="button" class="btn btn-danger" 
                                        onclick="confirmDelete(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')">
                                    <i class="fas fa-trash"></i> Delete Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete user <strong id="deleteUsername"><?= htmlspecialchars($user['username']) ?></strong>?</p>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-danger">Delete User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const eye = document.getElementById(fieldId + '-eye');
            
            if (field.type === 'password') {
                field.type = 'text';
                eye.classList.remove('fa-eye');
                eye.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                eye.classList.remove('fa-eye-slash');
                eye.classList.add('fa-eye');
            }
        }

        function confirmDelete(userId, username) {
            document.getElementById('deleteForm').action = '/dashusers/delete/' + userId;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Form validation
        (function() {
            'use strict';
            const form = document.querySelector('form');
            
            form.addEventListener('submit', function(event) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (password && password !== confirmPassword) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    const confirmField = document.getElementById('confirm_password');
                    confirmField.classList.add('is-invalid');
                    
                    // Add or update error message
                    let feedback = confirmField.parentNode.querySelector('.invalid-feedback');
                    if (!feedback) {
                        feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        confirmField.parentNode.appendChild(feedback);
                    }
                    feedback.textContent = 'Passwords do not match';
                }
                
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            });
        })();

        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>

    <style>
        .avatar-circle {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
    </style>
</body>
</html>