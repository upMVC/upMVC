<?php
/*
 * Admin Module - View
 * Renders admin dashboard and user management views
 */

namespace Admin;

use Common\Bmvc\BaseView;

class View
{
    private string $title = 'Admin Dashboard - upMVC';

    public function render(array $data = [])
    {
        $view = $data['view'] ?? 'dashboard';

        switch ($view) {
            case 'dashboard':
                $this->renderDashboard($data);
                break;

            case 'users_list':
                $this->renderUsersList($data);
                break;

            case 'user_form':
                $this->renderUserForm($data);
                break;

            case 'error':
                $this->renderError($data);
                break;

            default:
                echo 'Invalid view';
                break;
        }
    }

    /**
     * Render dashboard with stats
     */
    private function renderDashboard(array $data)
    {
        $baseView = new BaseView();
        $baseView->startHead($this->title);
        $baseView->endHead();
        $baseView->startBody($this->title);
        
        $stats = $data['stats'] ?? [];
        ?>
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
            <h1>Admin Dashboard</h1>
            
            <?php $this->renderMessages(); ?>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0;">
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff;">
                    <h3 style="margin: 0 0 10px 0; color: #333;">Total Users</h3>
                    <p style="font-size: 32px; font-weight: bold; margin: 0; color: #007bff;">
                        <?php echo $stats['userCount'] ?? 0; ?>
                    </p>
                </div>
            </div>

            <div style="margin-top: 30px;">
                <h2>Quick Actions</h2>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="<?php echo BASE_URL; ?>/admin/users" 
                       style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
                        Manage Users
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/users/add" 
                       style="display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;">
                        Add New User
                    </a>
                </div>
            </div>
        </div>
        <?php
        
        $baseView->startFooter();
        $baseView->endFooter();
    }

    /**
     * Render users list
     */
    private function renderUsersList(array $data)
    {
        $baseView = new BaseView();
        $baseView->startHead('Manage Users - Admin');
        $baseView->endHead();
        $baseView->startBody('Manage Users');
        
        $users = $data['users'] ?? [];
        ?>
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1>Manage Users</h1>
                <div>
                    <a href="<?php echo BASE_URL; ?>/admin" 
                       style="display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;">
                        ← Dashboard
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/users/add" 
                       style="display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;">
                        + Add User
                    </a>
                </div>
            </div>

            <?php $this->renderMessages(); ?>

            <table style="width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 12px; text-align: left;">ID</th>
                        <th style="padding: 12px; text-align: left;">Username</th>
                        <th style="padding: 12px; text-align: left;">Email</th>
                        <th style="padding: 12px; text-align: left;">Full Name</th>
                        <th style="padding: 12px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" style="padding: 20px; text-align: center; color: #6c757d;">
                                No users found. Add your first user!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td style="padding: 12px;"><?php echo htmlspecialchars($user['id']); ?></td>
                                <td style="padding: 12px;"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td style="padding: 12px;"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td style="padding: 12px;"><?php echo htmlspecialchars($user['fullname'] ?? ''); ?></td>
                                <td style="padding: 12px; text-align: center;">
                                    <a href="<?php echo BASE_URL; ?>/admin/users/edit/<?php echo $user['id']; ?>" 
                                       style="display: inline-block; padding: 5px 10px; background: #ffc107; color: #000; text-decoration: none; border-radius: 4px; margin-right: 5px;">
                                        Edit
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/admin/users/delete/<?php echo $user['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this user?');"
                                       style="display: inline-block; padding: 5px 10px; background: #dc3545; color: white; text-decoration: none; border-radius: 4px;">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
        
        $baseView->startFooter();
        $baseView->endFooter();
    }

    /**
     * Render user form (add/edit)
     */
    private function renderUserForm(array $data)
    {
        $baseView = new BaseView();
        $isEdit = $data['isEdit'] ?? false;
        $user = $data['user'] ?? null;
        $title = $isEdit ? 'Edit User' : 'Add New User';
        
        $baseView->startHead($title . ' - Admin');
        $baseView->endHead();
        $baseView->startBody($title);
        ?>
        <div class="container" style="max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1><?php echo $title; ?></h1>
                <a href="<?php echo BASE_URL; ?>/admin/users" 
                   style="display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;">
                    ← Back to Users
                </a>
            </div>

            <?php $this->renderMessages(); ?>

            <form method="POST" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Username*</label>
                    <input type="text" name="username" required 
                           value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>"
                           style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Email*</label>
                    <input type="email" name="email" required 
                           value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                           style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Full Name</label>
                    <input type="text" name="fullname" 
                           value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>"
                           style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">
                        Password<?php echo $isEdit ? '' : '*'; ?>
                    </label>
                    <input type="password" name="password" <?php echo $isEdit ? '' : 'required'; ?>
                           style="width: 100%; padding: 10px; border: 1px solid #dee2e6; border-radius: 4px;">
                    <?php if ($isEdit): ?>
                        <small style="color: #6c757d;">Leave blank to keep current password</small>
                    <?php endif; ?>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 30px;">
                    <button type="submit" 
                            style="padding: 10px 30px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">
                        <?php echo $isEdit ? 'Update User' : 'Create User'; ?>
                    </button>
                    <a href="<?php echo BASE_URL; ?>/admin/users" 
                       style="display: inline-block; padding: 10px 30px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
        <?php
        
        $baseView->startFooter();
        $baseView->endFooter();
    }

    /**
     * Render error page
     */
    private function renderError(array $data)
    {
        $baseView = new BaseView();
        $baseView->startHead('Error - Admin');
        $baseView->endHead();
        $baseView->startBody('Error');
        
        $message = $data['message'] ?? 'An error occurred';
        ?>
        <div class="container" style="max-width: 600px; margin: 0 auto; padding: 20px; text-align: center;">
            <h1 style="color: #dc3545;">Error</h1>
            <p><?php echo htmlspecialchars($message); ?></p>
            <a href="<?php echo BASE_URL; ?>/admin" 
               style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
                Back to Dashboard
            </a>
        </div>
        <?php
        
        $baseView->startFooter();
        $baseView->endFooter();
    }

    /**
     * Render flash messages
     */
    private function renderMessages()
    {
        if (isset($_SESSION['success'])) {
            echo '<div style="padding: 15px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 20px;">';
            echo htmlspecialchars($_SESSION['success']);
            echo '</div>';
            unset($_SESSION['success']);
        }

        if (isset($_SESSION['error'])) {
            echo '<div style="padding: 15px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 20px;">';
            echo htmlspecialchars($_SESSION['error']);
            echo '</div>';
            unset($_SESSION['error']);
        }
    }
}
