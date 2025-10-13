<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internal Server Error - upMVC</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #d32f2f; margin-bottom: 20px; }
        p { color: #666; line-height: 1.6; }
        .error-code { font-size: 72px; font-weight: bold; color: #d32f2f; margin: 20px 0; }
        .back-link { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #1976d2; color: white; text-decoration: none; border-radius: 4px; }
        .back-link:hover { background: #1565c0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">500</div>
        <h1>Internal Server Error</h1>
        <p>Something went wrong on our end. We're working to fix this issue.</p>
        <p>Please try again later or contact support if the problem persists.</p>
        <a href="<?php echo BASE_URL; ?>" class="back-link">← Back to Home</a>
    </div>
</body>
</html>