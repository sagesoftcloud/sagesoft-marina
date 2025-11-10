<?php
require_once 'classes/Auth.php';
require_once 'classes/SESMailer.php';
require_once 'classes/TemplateManager.php';
require_once 'vendor/autoload.php';

$auth = new Auth();
$auth->requireLogin();

$templateManager = new TemplateManager();
$templates = $templateManager->getAllTemplates();

$message = '';
$messageType = '';
$selectedTemplate = null;
$templateVariables = [];

if (isset($_GET['template_id'])) {
    $selectedTemplate = $templateManager->getTemplate($_GET['template_id']);
    $templateVariables = $templateManager->getTemplateVariables($_GET['template_id']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $templateId = $_POST['template_id'] ?? '';
    $to = $_POST['to'] ?? '';
    $variables = [];
    
    // Collect template variables
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'var_') === 0) {
            $varName = substr($key, 4);
            $variables[$varName] = $value;
        }
    }
    
    if ($templateId && $to) {
        try {
            $processedTemplate = $templateManager->processTemplate($templateId, $variables);
            
            if ($processedTemplate) {
                $mailer = new SESMailer();
                $result = $mailer->sendEmail(
                    $to, 
                    $processedTemplate['subject'], 
                    $processedTemplate['body_html'], 
                    true, 
                    $templateId
                );
                
                if ($result['success']) {
                    $message = 'Template email sent successfully! Message ID: ' . $result['message_id'];
                    $messageType = 'success';
                } else {
                    $message = 'Failed to send email: ' . $result['error'];
                    $messageType = 'error';
                }
            } else {
                $message = 'Template not found.';
                $messageType = 'error';
            }
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Please select a template and enter recipient email.';
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marina SES Tester - Template Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Template Email Test</h1>
            <p class="text-gray-600">Test Marina's official email templates</p>
        </div>
        
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-md <?php echo $messageType == 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Template Selection and Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Select Template</h2>
                
                <form method="POST" class="space-y-6">
                    <div>
                        <label for="template_id" class="block text-sm font-medium text-gray-700 mb-2">Email Template *</label>
                        <select id="template_id" name="template_id" required onchange="loadTemplate()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select a template...</option>
                            <?php foreach ($templates as $template): ?>
                                <option value="<?php echo $template['id']; ?>" 
                                        <?php echo ($selectedTemplate && $selectedTemplate['id'] == $template['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($template['name']) . ' (' . ucfirst($template['type']) . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="to" class="block text-sm font-medium text-gray-700 mb-2">To Email Address *</label>
                        <input type="email" id="to" name="to" required 
                               value="<?php echo htmlspecialchars($_POST['to'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="recipient@example.com">
                    </div>
                    
                    <!-- Template Variables -->
                    <div id="template-variables">
                        <?php if ($selectedTemplate && !empty($templateVariables)): ?>
                            <h3 class="text-lg font-medium text-gray-700 mb-3">Template Variables</h3>
                            <?php foreach ($templateVariables as $variable): ?>
                                <div class="mb-4">
                                    <label for="var_<?php echo strtolower($variable); ?>" 
                                           class="block text-sm font-medium text-gray-700 mb-2">
                                        <?php echo htmlspecialchars($variable); ?>
                                    </label>
                                    <input type="text" 
                                           id="var_<?php echo strtolower($variable); ?>" 
                                           name="var_<?php echo strtolower($variable); ?>" 
                                           value="<?php echo htmlspecialchars($_POST['var_' . strtolower($variable)] ?? $this->getDefaultValue($variable)); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="Enter <?php echo strtolower(str_replace('_', ' ', $variable)); ?>">
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex space-x-4">
                        <button type="submit" 
                                class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Send Template Email
                        </button>
                        
                        <button type="button" onclick="previewTemplate()" 
                                class="bg-green-600 text-white py-2 px-6 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                            Preview Template
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Template Preview -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Template Preview</h2>
                
                <?php if ($selectedTemplate): ?>
                    <div class="space-y-4">
                        <div>
                            <strong class="text-sm text-gray-600">Template:</strong>
                            <p class="text-lg font-medium"><?php echo htmlspecialchars($selectedTemplate['name']); ?></p>
                        </div>
                        
                        <div>
                            <strong class="text-sm text-gray-600">Type:</strong>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                         <?php echo $selectedTemplate['type'] == 'otp' ? 'bg-blue-100 text-blue-800' : 
                                                   ($selectedTemplate['type'] == 'transaction' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                <?php echo ucfirst($selectedTemplate['type']); ?>
                            </span>
                        </div>
                        
                        <div>
                            <strong class="text-sm text-gray-600">Subject:</strong>
                            <p id="preview-subject" class="text-sm bg-gray-50 p-2 rounded border">
                                <?php echo htmlspecialchars($selectedTemplate['subject']); ?>
                            </p>
                        </div>
                        
                        <div>
                            <strong class="text-sm text-gray-600">Preview:</strong>
                            <div id="preview-body" class="border rounded-md p-4 bg-gray-50 max-h-96 overflow-y-auto text-sm">
                                <?php echo $selectedTemplate['body_html']; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600">Select a template to see the preview.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function loadTemplate() {
            const templateId = document.getElementById('template_id').value;
            if (templateId) {
                window.location.href = 'template-test.php?template_id=' + templateId;
            }
        }
        
        function getDefaultValue(variable) {
            const defaults = {
                'OTP_CODE': '123456',
                'CUSTOMER_NAME': 'Juan Dela Cruz',
                'TRANSACTION_ID': 'TXN-' + Date.now(),
                'SERVICE_NAME': 'Vessel Registration',
                'AMOUNT': '1,500.00',
                'TRANSACTION_DATE': new Date().toLocaleString('en-PH'),
                'REFERENCE_NUMBER': 'REF-' + Date.now(),
                'NOTICE_TITLE': 'Important System Maintenance',
                'NOTICE_CONTENT': 'This is a sample notice content for testing purposes.',
                'ACTION_REQUIRED': 'Please review and acknowledge this notice.',
                'CONTACT_EMAIL': 'support@marina.gov.ph',
                'CONTACT_PHONE': '(02) 8527-8537',
                'NOTICE_DATE': new Date().toLocaleDateString('en-PH'),
                'ISSUING_OFFICE': 'Marina IT Department'
            };
            return defaults[variable] || '';
        }
        
        function previewTemplate() {
            // This would update the preview with current form values
            // Implementation would involve AJAX call to process template with current variables
            alert('Preview functionality would show processed template with current variable values');
        }
    </script>
</body>
</html>

<?php
// Helper method for default values
function getDefaultValue($variable) {
    $defaults = [
        'OTP_CODE' => '123456',
        'CUSTOMER_NAME' => 'Juan Dela Cruz',
        'TRANSACTION_ID' => 'TXN-' . time(),
        'SERVICE_NAME' => 'Vessel Registration',
        'AMOUNT' => '1,500.00',
        'TRANSACTION_DATE' => date('F j, Y g:i A'),
        'REFERENCE_NUMBER' => 'REF-' . time(),
        'NOTICE_TITLE' => 'Important System Maintenance',
        'NOTICE_CONTENT' => 'This is a sample notice content for testing purposes.',
        'ACTION_REQUIRED' => 'Please review and acknowledge this notice.',
        'CONTACT_EMAIL' => 'support@marina.gov.ph',
        'CONTACT_PHONE' => '(02) 8527-8537',
        'NOTICE_DATE' => date('F j, Y'),
        'ISSUING_OFFICE' => 'Marina IT Department'
    ];
    return $defaults[$variable] ?? '';
}
?>
