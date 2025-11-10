<?php
require_once 'classes/Auth.php';
require_once 'classes/SESMailer.php';
require_once 'vendor/autoload.php';

$auth = new Auth();
$auth->requireLogin();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $to = $_POST['to'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $body = $_POST['body'] ?? '';
    $isHTML = isset($_POST['is_html']);
    
    if ($to && $subject && $body) {
        try {
            $mailer = new SESMailer();
            $result = $mailer->sendEmail($to, $subject, $body, $isHTML);
            
            if ($result['success']) {
                $message = 'Email sent successfully! Message ID: ' . $result['message_id'];
                $messageType = 'success';
            } else {
                $message = 'Failed to send email: ' . $result['error'];
                $messageType = 'error';
            }
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
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
    <title>Marina SES Tester - Basic Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Basic Email Test</h1>
            <p class="text-gray-600">Send a simple test email through AWS SES</p>
        </div>
        
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-md <?php echo $messageType == 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <form method="POST" class="space-y-6">
                <div>
                    <label for="to" class="block text-sm font-medium text-gray-700 mb-2">To Email Address *</label>
                    <input type="email" id="to" name="to" required 
                           value="<?php echo htmlspecialchars($_POST['to'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="recipient@example.com">
                </div>
                
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                    <input type="text" id="subject" name="subject" required 
                           value="<?php echo htmlspecialchars($_POST['subject'] ?? 'Marina SES Test Email'); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Email subject">
                </div>
                
                <div>
                    <label for="body" class="block text-sm font-medium text-gray-700 mb-2">Email Body *</label>
                    <textarea id="body" name="body" required rows="10" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Enter your email content here..."><?php echo htmlspecialchars($_POST['body'] ?? 'This is a test email from Marina SES Tester.

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
                
                <div class="flex space-x-4">
                    <button type="submit" 
                            class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Send Test Email
                    </button>
                    
                    <button type="button" onclick="clearForm()" 
                            class="bg-gray-600 text-white py-2 px-6 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Clear Form
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Email Preview -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Email Preview</h2>
            <div class="border border-gray-300 rounded-md p-4 bg-gray-50">
                <div class="mb-2">
                    <strong>To:</strong> <span id="preview-to">recipient@example.com</span>
                </div>
                <div class="mb-2">
                    <strong>Subject:</strong> <span id="preview-subject">Marina SES Test Email</span>
                </div>
                <div class="mb-2">
                    <strong>Body:</strong>
                </div>
                <div id="preview-body" class="bg-white p-3 border rounded text-sm">
                    This is a test email from Marina SES Tester.

Best regards,
Marina IT Department
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function clearForm() {
            document.getElementById('to').value = '';
            document.getElementById('subject').value = 'Marina SES Test Email';
            document.getElementById('body').value = 'This is a test email from Marina SES Tester.\n\nBest regards,\nMarina IT Department';
            document.getElementById('is_html').checked = false;
            updatePreview();
        }
        
        function updatePreview() {
            document.getElementById('preview-to').textContent = document.getElementById('to').value || 'recipient@example.com';
            document.getElementById('preview-subject').textContent = document.getElementById('subject').value || 'Marina SES Test Email';
            document.getElementById('preview-body').textContent = document.getElementById('body').value || 'This is a test email from Marina SES Tester.\n\nBest regards,\nMarina IT Department';
        }
        
        // Update preview on input
        document.getElementById('to').addEventListener('input', updatePreview);
        document.getElementById('subject').addEventListener('input', updatePreview);
        document.getElementById('body').addEventListener('input', updatePreview);
        
        // Initial preview update
        updatePreview();
    </script>
</body>
</html>
