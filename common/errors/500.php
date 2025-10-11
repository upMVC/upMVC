<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Internal Server Error</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
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
            color: #fc466b;
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
            background: #fc466b;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .btn:hover {
            background: #e73c5e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(252, 70, 107, 0.3);
        }
        .navigation {
            margin-top: 20px;
        }
        .navigation a {
            color: #fc466b;
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
        <div class="error-code">500</div>
        <div class="error-message">Internal Server Error</div>
        <div class="error-description">
            Something went wrong on our end. We're working to fix this issue.
        </div>
        <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>" class="btn">Go Home</a>
        <div class="navigation">
            <a href="javascript:history.back()">← Go Back</a>
        </div>
    </div>
</body>
</html>