<?php
// view_messages.php - Password protected message viewer
session_start();

// Simple password protection - CHANGE THIS PASSWORD!
$correctPassword = 'Joseph2025!'; // Change this to a strong password

// Handle login
if ($_POST['password'] ?? '') {
    if ($_POST['password'] === $correctPassword) {
        $_SESSION['authenticated'] = true;
        $_SESSION['login_time'] = time();
    } else {
        $error = 'Invalid password';
    }
}

// Auto logout after 2 hours
if (($_SESSION['login_time'] ?? 0) < (time() - 7200)) {
    unset($_SESSION['authenticated']);
    unset($_SESSION['login_time']);
}

// Check if authenticated
if (!($_SESSION['authenticated'] ?? false)) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Login - Message Viewer</title>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                max-width: 400px; 
                margin: 100px auto; 
                padding: 20px;
                background: linear-gradient(135deg, #2c3e50 0%, #1a2530 100%);
                color: white;
            }
            .login-form { 
                background: rgba(255,255,255,0.1); 
                padding: 40px; 
                border-radius: 10px; 
                backdrop-filter: blur(10px);
            }
            input[type="password"] { 
                width: 100%; 
                padding: 12px; 
                margin: 15px 0; 
                border: none;
                border-radius: 5px;
                font-size: 16px;
            }
            button { 
                background: #3498db; 
                color: white; 
                padding: 12px 30px; 
                border: none; 
                border-radius: 5px; 
                cursor: pointer;
                font-size: 16px;
                font-weight: 600;
                width: 100%;
                transition: background 0.3s ease;
            }
            button:hover {
                background: #2980b9;
            }
            .error { 
                color: #e74c3c; 
                margin: 10px 0; 
                text-align: center;
            }
            h2 {
                text-align: center;
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="login-form">
            <h2>View Messages - Joseph Lugaho</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="POST">
                <input type="password" name="password" placeholder="Enter password" required>
                <button type="submit">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Read and display messages
$messagesFile = __DIR__ . '/messages/messages.json';
$messages = [];

if (file_exists($messagesFile)) {
    $messagesData = file_get_contents($messagesFile);
    if ($messagesData !== false) {
        $messages = json_decode($messagesData, true) ?? [];
    }
}

// Handle message deletion
if (isset($_GET['delete'])) {
    $idToDelete = $_GET['delete'];
    $messages = array_filter($messages, function($msg) use ($idToDelete) {
        return $msg['id'] !== $idToDelete;
    });
    file_put_contents($messagesFile, json_encode(array_values($messages), JSON_PRETTY_PRINT));
    header('Location: view_messages.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: view_messages.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Messages - Joseph Lugaho</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px;
            background-color: #f5f7fa;
            color: #333;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #3498db;
        }
        .message { 
            background: white; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            padding: 25px; 
            margin: 20px 0; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .message-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start; 
            margin-bottom: 15px; 
        }
        .message-meta { 
            color: #666; 
            font-size: 0.9em; 
            margin-bottom: 15px;
        }
        .delete-btn { 
            background: #e74c3c; 
            color: white; 
            border: none; 
            padding: 8px 15px; 
            border-radius: 4px; 
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em;
        }
        .delete-btn:hover {
            background: #c0392b;
        }
        .back-btn { 
            background: #3498db; 
            color: white; 
            padding: 10px 20px; 
            text-decoration: none; 
            border-radius: 5px; 
            display: inline-block; 
        }
        .back-btn:hover {
            background: #2980b9;
        }
        .message-content {
            line-height: 1.6;
            white-space: pre-wrap;
        }
        .no-messages {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 1.1em;
        }
        .stats {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="index.html" class="back-btn">← Back to Portfolio</a>
        <div>
            <a href="?logout=1" style="color: #666; text-decoration: none; margin-left: 15px;">Logout</a>
        </div>
    </div>
    
    <div class="stats">
        Total Messages: <?php echo count($messages); ?>
    </div>
    
    <?php if (empty($messages)): ?>
        <div class="no-messages">
            <h3>No messages yet</h3>
            <p>Messages from your contact form will appear here.</p>
        </div>
    <?php else: ?>
        <?php foreach (array_reverse($messages) as $message): ?>
            <div class="message">
                <div class="message-header">
                    <div>
                        <h3 style="margin: 0; color: #2c3e50;"><?php echo htmlspecialchars($message['subject']); ?></h3>
                        <p style="margin: 5px 0 0 0; color: #3498db; font-weight: 600;">
                            <?php echo htmlspecialchars($message['name']); ?> 
                            (<a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" style="color: #3498db;">
                                <?php echo htmlspecialchars($message['email']); ?>
                            </a>)
                        </p>
                    </div>
                    <a href="?delete=<?php echo $message['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this message?')">Delete</a>
                </div>
                <div class="message-meta">
                    <strong>Received:</strong> <?php echo $message['timestamp']; ?> | 
                    <strong>IP:</strong> <?php echo $message['ip']; ?>
                </div>
                <div class="message-content">
                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>