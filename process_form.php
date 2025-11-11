<?php
// process_form.php
header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_STRING);
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$subject = filter_var($_POST['subject'] ?? '', FILTER_SANITIZE_STRING);
$message = filter_var($_POST['message'] ?? '', FILTER_SANITIZE_STRING);

// Validate required fields
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Create messages directory if it doesn't exist
$messagesDir = __DIR__ . '/messages';
if (!is_dir($messagesDir)) {
    if (!mkdir($messagesDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Could not create storage directory']);
        exit;
    }
}

// Prepare message data
$messageData = [
    'id' => uniqid(),
    'timestamp' => date('Y-m-d H:i:s'),
    'name' => $name,
    'email' => $email,
    'subject' => $subject,
    'message' => $message,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
];

// Save to JSON file
$filename = $messagesDir . '/messages.json';
$messages = [];

// Read existing messages
if (file_exists($filename)) {
    $existingData = file_get_contents($filename);
    if ($existingData !== false) {
        $messages = json_decode($existingData, true) ?? [];
    }
}

// Add new message
$messages[] = $messageData;

// Save back to file
if (file_put_contents($filename, json_encode($messages, JSON_PRETTY_PRINT))) {
    // Optional: Send email notification (uncomment when ready)
    /*
    $to = "jlugaho@asu.edu";
    $emailSubject = "New Portfolio Message: " . $subject;
    $emailBody = "
    New message from your portfolio website:
    
    Name: $name
    Email: $email
    Subject: $subject
    
    Message:
    $message
    
    Received: " . date('Y-m-d H:i:s') . "
    IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "
    ";
    
    $headers = "From: portfolio@josephlugaho.com\r\n";
    $headers .= "Reply-To: $email\r\n";
    
    mail($to, $emailSubject, $emailBody, $headers);
    */
    
    echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save message. Please try again.']);
}
?>