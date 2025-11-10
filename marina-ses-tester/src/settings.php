<?php
require_once 'classes/Auth.php';
require_once 'classes/SESMailer.php';

$auth = new Auth();
$auth->requireLogin();

$message = '';
$messageType = '';

$mailer = new SESMailer();
$config = $mailer->getConfig();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newConfig = [
        'smtp_host' => $_POST['smtp_host'] ?? '',
        'smtp_port' => $_POST['smtp_port'] ?? 587,
        'smtp_username' => $_POST['smtp_username'] ?? '',
        'smtp_password' => $_POST['smtp_password'] ?? '',
        'from_email' => $_POST['from_email'] ?? '',
        'from_name' => $_POST['from_name'] ?? ''
    ];
    
    if ($newConfig['smtp_host'] && $newConfig['smtp_username'] && $newConfig['smtp_password'] && $newConfig['from_email']) {
        try {
            if ($mailer->updateConfig($newConfig)) {
                $message = 'SES configuration updated successfully!';
                $messageType = 'success';
                // Reload config
                $config = $mailer->getConfig();
            } else {
                $message = 'Failed to update configuration.';
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
    <title>Marina SES Tester - Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">SES Configuration Settings</h1>
            <p class="text-gray-600">Configure your AWS SES SMTP credentials</p>
        </div>
        
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-md <?php echo $messageType == 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Configuration Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">SMTP Configuration</h2>
                
                <form method="POST" class="space-y-6">
                    <div>
                        <label for="smtp_host" class="block text-sm font-medium text-gray-700 mb-2">SMTP Host *</label>
                        <input type="text" id="smtp_host" name="smtp_host" required 
                               value="<?php echo htmlspecialchars($config['smtp_host'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="email-smtp.ap-southeast-1.amazonaws.com">
                        <p class="text-xs text-gray-500 mt-1">AWS SES SMTP endpoint for your region</p>
                    </div>
                    
                    <div>
                        <label for="smtp_port" class="block text-sm font-medium text-gray-700 mb-2">SMTP Port *</label>
                        <select id="smtp_port" name="smtp_port" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="587" <?php echo ($config['smtp_port'] ?? 587) == 587 ? 'selected' : ''; ?>>587 (STARTTLS)</option>
                            <option value="465" <?php echo ($config['smtp_port'] ?? 587) == 465 ? 'selected' : ''; ?>>465 (SSL)</option>
                            <option value="25" <?php echo ($config['smtp_port'] ?? 587) == 25 ? 'selected' : ''; ?>>25 (Plain)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="smtp_username" class="block text-sm font-medium text-gray-700 mb-2">SMTP Username *</label>
                        <input type="text" id="smtp_username" name="smtp_username" required 
                               value="<?php echo htmlspecialchars($config['smtp_username'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Your SES SMTP username">
                        <p class="text-xs text-gray-500 mt-1">SMTP credentials username from AWS SES</p>
                    </div>
                    
                    <div>
                        <label for="smtp_password" class="block text-sm font-medium text-gray-700 mb-2">SMTP Password *</label>
                        <input type="password" id="smtp_password" name="smtp_password" required 
                               value="<?php echo htmlspecialchars($config['smtp_password'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Your SES SMTP password">
                        <p class="text-xs text-gray-500 mt-1">SMTP credentials password from AWS SES</p>
                    </div>
                    
                    <div>
                        <label for="from_email" class="block text-sm font-medium text-gray-700 mb-2">From Email Address *</label>
                        <input type="email" id="from_email" name="from_email" required 
                               value="<?php echo htmlspecialchars($config['from_email'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="noreply@marina.gov.ph">
                        <p class="text-xs text-gray-500 mt-1">Must be a verified email address in SES</p>
                    </div>
                    
                    <div>
                        <label for="from_name" class="block text-sm font-medium text-gray-700 mb-2">From Name</label>
                        <input type="text" id="from_name" name="from_name" 
                               value="<?php echo htmlspecialchars($config['from_name'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Marina Portal">
                        <p class="text-xs text-gray-500 mt-1">Display name for outgoing emails</p>
                    </div>
                    
                    <div class="flex space-x-4">
                        <button type="submit" 
                                class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Save Configuration
                        </button>
                        
                        <button type="button" onclick="testConnection()" 
                                class="bg-green-600 text-white py-2 px-6 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                            Test Connection
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Setup Guide -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Setup Guide</h2>
                
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">1. AWS SES Setup</h3>
                        <ul class="text-sm text-gray-600 space-y-1 ml-4">
                            <li>• Log in to AWS Console</li>
                            <li>• Navigate to Simple Email Service (SES)</li>
                            <li>• Verify your domain (marina.gov.ph)</li>
                            <li>• Request production access (if needed)</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">2. SMTP Credentials</h3>
                        <ul class="text-sm text-gray-600 space-y-1 ml-4">
                            <li>• Go to SES → SMTP Settings</li>
                            <li>• Click "Create SMTP Credentials"</li>
                            <li>• Download and save the credentials</li>
                            <li>• Use the credentials in the form above</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">3. Regional Endpoints</h3>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p><strong>Singapore (ap-southeast-1):</strong></p>
                            <code class="bg-gray-100 px-2 py-1 rounded text-xs">email-smtp.ap-southeast-1.amazonaws.com</code>
                            
                            <p class="mt-2"><strong>US East (us-east-1):</strong></p>
                            <code class="bg-gray-100 px-2 py-1 rounded text-xs">email-smtp.us-east-1.amazonaws.com</code>
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="text-sm text-yellow-700">
                                <strong>Important:</strong> Make sure your SES account is out of sandbox mode to send emails to any address. In sandbox mode, you can only send to verified addresses.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Current Configuration Status -->
        <?php if ($config): ?>
            <div class="mt-8 bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Current Configuration</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">SMTP Settings</h3>
                        <div class="bg-gray-50 p-4 rounded-md text-sm">
                            <p><strong>Host:</strong> <?php echo htmlspecialchars($config['smtp_host']); ?></p>
                            <p><strong>Port:</strong> <?php echo htmlspecialchars($config['smtp_port']); ?></p>
                            <p><strong>Username:</strong> <?php echo htmlspecialchars(substr($config['smtp_username'], 0, 8) . '...'); ?></p>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Email Settings</h3>
                        <div class="bg-gray-50 p-4 rounded-md text-sm">
                            <p><strong>From Email:</strong> <?php echo htmlspecialchars($config['from_email']); ?></p>
                            <p><strong>From Name:</strong> <?php echo htmlspecialchars($config['from_name']); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function testConnection() {
            // This would make an AJAX call to test the SMTP connection
            alert('Connection test functionality would be implemented here.\n\nThis would:\n1. Validate SMTP credentials\n2. Test connection to SES\n3. Send a test email\n4. Report results');
        }
    </script>
</body>
</html>
