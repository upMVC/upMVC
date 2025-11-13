<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Forbidden</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            text-align: center;
            background: white;
            border-radius: 15px;
            padding: 60px 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 20px;
        }
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #fcb69f;
            margin: 0;
            line-height: 1;
        }
        .error-message {
            font-size: 24px;
            margin: 20px 0;
            color: #666;
        }
        .error-description {
            font-size: 16px;
            color: #888;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #fcb69f;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .btn:hover {
            background: #faa085;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(252, 182, 159, 0.3);
        }
        .navigation {
            margin-top: 20px;
        }
        .navigation a {
            color: #fcb69f;
            text-decoration: none;
            margin: 0 10px;
        }
        .navigation a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">403</div>
        <div class="error-message">Access Forbidden</div>
        <div class="error-description">
            You don't have permission to access this resource.
        </div>
        <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>" class="btn">Go Home</a>
        <div class="navigation">
            <a href="javascript:history.back()">← Go Back</a>
        </div>
    </div>
</body>
</html>




