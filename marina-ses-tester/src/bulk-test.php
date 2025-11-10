<?php
require_once 'classes/Auth.php';
require_once 'classes/SESMailer.php';
require_once 'vendor/autoload.php';

$auth = new Auth();
$auth->requireLogin();

$message = '';
$messageType = '';
$results = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emails = $_POST['emails'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $body = $_POST['body'] ?? '';
    $isHTML = isset($_POST['is_html']);
    
    if ($emails && $subject && $body) {
        // Parse email addresses
        $emailList = array_filter(array_map('trim', preg_split('/[\r\n,;]+/', $emails)));
        $validEmails = [];
        
        foreach ($emailList as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validEmails[] = $email;
            }
        }
        
        if (!empty($validEmails)) {
            try {
                $mailer = new SESMailer();
                $results = $mailer->sendBulkEmails($validEmails, $subject, $body, $isHTML);
                
                $successCount = count(array_filter($results, function($r) { return $r['result']['success']; }));
                $totalCount = count($results);
                
                $message = "Bulk email completed: {$successCount}/{$totalCount} emails sent successfully.";
                $messageType = $successCount == $totalCount ? 'success' : 'warning';
            } catch (Exception $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'error';
            }
        } else {
            $message = 'No valid email addresses found.';
            $messageType = 'error';
        }
    } else {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marina SES Tester - Bulk Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Bulk Email Test</h1>
            <p class="text-gray-600">Send emails to multiple recipients</p>
        </div>
        
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-md <?php 
                echo $messageType == 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 
                    ($messageType == 'warning' ? 'bg-yellow-100 border border-yellow-400 text-yellow-700' : 
                     'bg-red-100 border border-red-400 text-red-700'); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Bulk Email Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Bulk Email Configuration</h2>
                
                <form method="POST" class="space-y-6">
                    <div>
                        <label for="emails" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Addresses * 
                            <span class="text-xs text-gray-500">(one per line, or comma/semicolon separated)</span>
                        </label>
                        <textarea id="emails" name="emails" required rows="8" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="user1@example.com&#10;user2@example.com&#10;user3@example.com"><?php echo htmlspecialchars($_POST['emails'] ?? ''); ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            <span id="email-count">0</span> email addresses detected
                        </p>
                    </div>
                    
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                        <input type="text" id="subject" name="subject" required 
                               value="<?php echo htmlspecialchars($_POST['subject'] ?? 'Marina Bulk Email Test'); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Email subject">
                    </div>
                    
                    <div>
                        <label for="body" class="block text-sm font-medium text-gray-700 mb-2">Email Body *</label>
                        <textarea id="body" name="body" required rows="10" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Enter your email content here..."><?php echo htmlspecialchars($_POST['body'] ?? 'This is a bulk test email from Marina SES Tester.

Best regards,
Marina IT Department'); ?></textarea>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="is_html" name="is_html" 
                               <?php echo isset($_POST['is_html']) ? 'checked' : ''; ?>
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_html" class="ml-2 block text-sm text-gray-700">
                            Send as HTML email
                        </label>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="text-sm text-yellow-700">
                                <strong>Important:</strong> Bulk emails will be sent one by one. Large lists may take time to process.
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex space-x-4">
                        <button type="submit" 
                                class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Send Bulk Emails
                        </button>
                        
                        <button type="button" onclick="clearForm()" 
                                class="bg-gray-600 text-white py-2 px-6 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Clear Form
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Results -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Sending Results</h2>
                
                <?php if (!empty($results)): ?>
                    <div class="space-y-3">
                        <?php foreach ($results as $result): ?>
                            <div class="flex items-center justify-between p-3 border rounded-md <?php echo $result['result']['success'] ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'; ?>">
                                <div class="flex items-center">
                                    <?php if ($result['result']['success']): ?>
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    <?php else: ?>
                                        <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    <?php endif; ?>
                                    <span class="text-sm font-medium"><?php echo htmlspecialchars($result['email']); ?></span>
                                </div>
                                <div class="text-xs <?php echo $result['result']['success'] ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $result['result']['success'] ? 'Sent' : 'Failed'; ?>
                                </div>
                            </div>
                            <?php if (!$result['result']['success']): ?>
                                <div class="text-xs text-red-600 ml-7 -mt-2">
                                    <?php echo htmlspecialchars($result['result']['error']); ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600">No emails sent yet. Fill out the form and click "Send Bulk Emails" to see results here.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function clearForm() {
            document.getElementById('emails').value = '';
            document.getElementById('subject').value = 'Marina Bulk Email Test';
            document.getElementById('body').value = 'This is a bulk test email from Marina SES Tester.\n\nBest regards,\nMarina IT Department';
            document.getElementById('is_html').checked = false;
            updateEmailCount();
        }
        
        function updateEmailCount() {
            const emails = document.getElementById('emails').value;
            const emailList = emails.split(/[\r\n,;]+/).filter(email => email.trim() && email.includes('@'));
            document.getElementById('email-count').textContent = emailList.length;
        }
        
        // Update email count on input
        document.getElementById('emails').addEventListener('input', updateEmailCount);
        
        // Initial count update
        updateEmailCount();
    </script>
</body>
</html>
