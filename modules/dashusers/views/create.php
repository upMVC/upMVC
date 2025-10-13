<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User - Dashboard</title>
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
        .form-floating > .form-control {
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
    </style>
</head>
<body class="bg-light">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Create New User
                    </h1>
                    <p class="mb-0 opacity-75">Add a new user to the system</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="/dashusers" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
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

                <!-- Create User Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-circle"></i> User Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/dashusers/create" novalidate>
                            <div class="row g-3">
                                <!-- Username -->
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <i class="fas fa-user input-icon"></i>
                                        <input type="text" class="form-control <?= !empty($errors['username']) ? 'is-invalid' : '' ?>" 
                                               id="username" name="username" 
                                               value="<?= htmlspecialchars($data['username'] ?? '') ?>" 
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
                                               value="<?= htmlspecialchars($data['email'] ?? '') ?>" 
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
                                               value="<?= htmlspecialchars($data['first_name'] ?? '') ?>" 
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
                                               value="<?= htmlspecialchars($data['last_name'] ?? '') ?>" 
                                               placeholder="Last Name">
                                        <label for="last_name">Last Name</label>
                                    </div>
                                </div>

                                <!-- Password -->
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <i class="fas fa-lock input-icon"></i>
                                        <input type="password" class="form-control <?= !empty($errors['password']) ? 'is-invalid' : '' ?>" 
                                               id="password" name="password" placeholder="Password" required>
                                        <label for="password" class="required">Password</label>
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
                                            Password must be at least 8 characters long
                                        </div>
                                    </div>
                                </div>

                                <!-- Confirm Password -->
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <i class="fas fa-lock input-icon"></i>
                                        <input type="password" class="form-control <?= !empty($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                               id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                                        <label for="confirm_password" class="required">Confirm Password</label>
                                        <button type="button" class="btn btn-outline-secondary position-absolute" 
                                                style="right: 0.75rem; top: 50%; transform: translateY(-50%); z-index: 4; border: none; background: none; padding: 0.25rem;"
                                                onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye" id="confirm_password-eye"></i>
                                        </button>
                                        <?php if (!empty($errors['confirm_password'])): ?>
                                        <div class="invalid-feedback">
                                            <?= htmlspecialchars($errors['confirm_password']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Role -->
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <i class="fas fa-user-shield input-icon"></i>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="">Select Role</option>
                                            <option value="user" <?= ($data['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>User</option>
                                            <option value="author" <?= ($data['role'] ?? '') === 'author' ? 'selected' : '' ?>>Author</option>
                                            <option value="editor" <?= ($data['role'] ?? '') === 'editor' ? 'selected' : '' ?>>Editor</option>
                                            <option value="admin" <?= ($data['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            <?php if (($_SESSION['user']['role'] ?? '') === 'super_admin'): ?>
                                            <option value="super_admin" <?= ($data['role'] ?? '') === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
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
                                            <option value="active" <?= ($data['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="inactive" <?= ($data['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                            <option value="suspended" <?= ($data['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
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
                                               value="<?= htmlspecialchars($data['phone'] ?? '') ?>" 
                                               placeholder="Phone Number">
                                        <label for="phone">Phone Number</label>
                                    </div>
                                </div>

                                <!-- Bio -->
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control" id="bio" name="bio" 
                                                  style="height: 100px" placeholder="Bio"><?= htmlspecialchars($data['bio'] ?? '') ?></textarea>
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
                                            <i class="fas fa-save"></i> Create User
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Role Permissions Info -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle"></i> Role Permissions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6 class="text-primary">User Roles</h6>
                                <ul class="list-unstyled">
                                    <li><strong>User:</strong> Basic access, can view assigned content</li>
                                    <li><strong>Author:</strong> Can create and edit own content</li>
                                    <li><strong>Editor:</strong> Can edit all content, manage authors</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-danger">Admin Roles</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Admin:</strong> Full content management, user management</li>
                                    <li><strong>Super Admin:</strong> Full system access, manage admins</li>
                                </ul>
                            </div>
                        </div>
                    </div>
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

        // Form validation
        (function() {
            'use strict';
            const form = document.querySelector('form');
            
            form.addEventListener('submit', function(event) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (password !== confirmPassword) {
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
</body>
</html>