<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'App\Modules\TestParent'; ?> - Enhanced upMVC</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Enhanced Module CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL ?? ''; ?>/src/Modules/TestParent/assets/css/style.css">
    
    <?php if ($debug_mode ?? false): ?>
    <!-- Debug Mode Indicator -->
    <style>
        .debug-indicator {
            position: fixed; top: 0; right: 0; z-index: 9999;
            background: #ff6b6b; color: white; padding: 5px 10px;
            font-size: 12px; font-weight: bold;
        }
    </style>
    <?php endif; ?>
</head>
<body>
    <?php if ($debug_mode ?? false): ?>
    <div class="debug-indicator">
        <i class="fas fa-bug"></i> DEBUG MODE - <?php echo $app_env; ?>
    </div>
    <?php endif; ?>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL ?? ''; ?>">
                <i class="fas fa-rocket"></i> Enhanced upMVC
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?php echo BASE_URL ?? ''; ?>/testparent">
                    <i class="fas fa-home"></i> App\Modules\TestParent
                </a>
                <a class="nav-link" href="<?php echo BASE_URL ?? ''; ?>/testparent/about">
                    <i class="fas fa-info-circle"></i> About
                </a>
                <?php if ($debug_mode ?? false): ?>
                <a class="nav-link" href="<?php echo BASE_URL ?? ''; ?>/testparent/api">
                    <i class="fas fa-code"></i> API
                </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (method_exists($this, 'renderFlashMessages')) $this->renderFlashMessages(); ?>