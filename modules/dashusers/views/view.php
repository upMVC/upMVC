<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Dashboard</title>
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
        .user-profile-card {
            background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 15px;
        }
        .stat-card {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 10px;
        }
        .avatar-circle {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .activity-item {
            border-left: 3px solid #667eea;
            padding-left: 1rem;
            margin-bottom: 1rem;
        }
        .info-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 0.5rem 1rem;
            align-items: center;
        }
        .info-label {
            font-weight: 600;
            color: #6c757d;
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
                        <i class="fas fa-user-circle me-2"></i>
                        User Profile
                    </h1>
                    <p class="mb-0 opacity-75">View user details and activity</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="/dashusers" class="btn btn-light me-2">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                    <a href="/dashusers/edit/<?= $user['id'] ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit User
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- User Profile Card -->
            <div class="col-lg-4">
                <div class="card user-profile-card mb-4">
                    <div class="card-body text-center">
                        <div class="avatar-circle mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4 class="mb-1"><?= htmlspecialchars($user['username']) ?></h4>
                        <?php if (!empty(trim($user['first_name'] . ' ' . $user['last_name']))): ?>
                        <h6 class="mb-2 opacity-75"><?= htmlspecialchars(trim($user['first_name'] . ' ' . $user['last_name'])) ?></h6>
                        <?php endif; ?>
                        <p class="mb-3 opacity-75"><?= htmlspecialchars($user['email']) ?></p>
                        
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <?php
                            $roleColors = [
                                'super_admin' => 'danger',
                                'admin' => 'warning',
                                'editor' => 'info',
                                'author' => 'primary',
                                'user' => 'light text-dark'
                            ];
                            $statusColors = [
                                'active' => 'success',
                                'inactive' => 'secondary',
                                'suspended' => 'danger'
                            ];
                            $roleColor = $roleColors[$user['role']] ?? 'light text-dark';
                            $statusColor = $statusColors[$user['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $roleColor ?>">
                                <?= ucfirst(str_replace('_', ' ', $user['role'])) ?>
                            </span>
                            <span class="badge bg-<?= $statusColor ?>">
                                <?= ucfirst($user['status']) ?>
                            </span>
                        </div>

                        <?php if (!empty($user['bio'])): ?>
                        <p class="opacity-75 small"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                        <?php endif; ?>

                        <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="h5 mb-0"><?= date('M j, Y', strtotime($user['created_at'])) ?></div>
                                <small class="opacity-75">Joined</small>
                            </div>
                            <div class="col-6">
                                <div class="h5 mb-0"><?= $user['last_login'] ? date('M j, Y', strtotime($user['last_login'])) : 'Never' ?></div>
                                <small class="opacity-75">Last Login</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-bolt"></i> Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="/dashusers/edit/<?= $user['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit Profile
                            </a>
                            <?php if ($user['status'] === 'active'): ?>
                            <button class="btn btn-warning btn-sm" onclick="changeStatus(<?= $user['id'] ?>, 'suspended')">
                                <i class="fas fa-ban"></i> Suspend User
                            </button>
                            <?php else: ?>
                            <button class="btn btn-success btn-sm" onclick="changeStatus(<?= $user['id'] ?>, 'active')">
                                <i class="fas fa-check"></i> Activate User
                            </button>
                            <?php endif; ?>
                            <button class="btn btn-info btn-sm" onclick="sendPasswordReset(<?= $user['id'] ?>)">
                                <i class="fas fa-key"></i> Reset Password
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Details and Activity -->
            <div class="col-lg-8">
                <!-- User Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle"></i> User Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-grid">
                                    <span class="info-label">Username:</span>
                                    <span><?= htmlspecialchars($user['username']) ?></span>
                                    
                                    <span class="info-label">Email:</span>
                                    <span><?= htmlspecialchars($user['email']) ?></span>
                                    
                                    <span class="info-label">Full Name:</span>
                                    <span><?= htmlspecialchars(trim($user['first_name'] . ' ' . $user['last_name'])) ?: 'Not provided' ?></span>
                                    
                                    <span class="info-label">Phone:</span>
                                    <span><?= htmlspecialchars($user['phone'] ?? 'Not provided') ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-grid">
                                    <span class="info-label">Role:</span>
                                    <span class="badge bg-<?= $roleColor ?>">
                                        <?= ucfirst(str_replace('_', ' ', $user['role'])) ?>
                                    </span>
                                    
                                    <span class="info-label">Status:</span>
                                    <span class="badge bg-<?= $statusColor ?>">
                                        <?= ucfirst($user['status']) ?>
                                    </span>
                                    
                                    <span class="info-label">Created:</span>
                                    <span><?= date('M j, Y g:i A', strtotime($user['created_at'])) ?></span>
                                    
                                    <span class="info-label">Last Login:</span>
                                    <span><?= $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never' ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($user['bio'])): ?>
                        <hr>
                        <h6>Biography</h6>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Statistics -->
                <?php if (!empty($user_stats)): ?>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <h4 class="mb-1"><?= $user_stats['total_logins'] ?? 0 ?></h4>
                                <small>Total Logins</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <h4 class="mb-1"><?= $user_stats['posts_created'] ?? 0 ?></h4>
                                <small>Posts Created</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <h4 class="mb-1"><?= $user_stats['pages_created'] ?? 0 ?></h4>
                                <small>Pages Created</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <h4 class="mb-1"><?= count($recent_activity ?? []) ?></h4>
                                <small>Activities</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-history"></i> Activity Log
                        </h5>
                        <span class="badge bg-secondary"><?= count($recent_activity ?? []) ?> activities</span>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_activity)): ?>
                        <div class="timeline">
                            <?php foreach ($recent_activity as $activity): ?>
                            <div class="activity-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($activity['action']) ?></h6>
                                        <p class="text-muted mb-1">
                                            <?= htmlspecialchars($activity['description'] ?? 'No description available') ?>
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= date('M j, Y g:i A', strtotime($activity['created_at'])) ?>
                                            <?php if (!empty($activity['ip_address'])): ?>
                                            • <?= htmlspecialchars($activity['ip_address']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        <?= htmlspecialchars($activity['module']) ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No activity recorded</h6>
                            <p class="text-muted">This user hasn't performed any tracked actions yet.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Change Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change User Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to change the status of <strong><?= htmlspecialchars($user['username']) ?></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="statusForm" method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="change_status">
                        <input type="hidden" name="status" id="newStatus">
                        <button type="submit" class="btn btn-primary">Change Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changeStatus(userId, newStatus) {
            document.getElementById('newStatus').value = newStatus;
            document.getElementById('statusForm').action = '/dashusers/edit/' + userId;
            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }

        function sendPasswordReset(userId) {
            if (confirm('Send password reset email to this user?')) {
                // Implement password reset functionality
                alert('Password reset functionality would be implemented here');
            }
        }

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