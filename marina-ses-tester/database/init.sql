-- Marina SES Tester Database Schema
CREATE DATABASE IF NOT EXISTS marina_ses;
USE marina_ses;

-- Admin users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Email templates table
CREATE TABLE email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('otp', 'transaction', 'official') NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body_html TEXT NOT NULL,
    body_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Email logs table
CREATE TABLE email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    test_type ENUM('basic', 'template', 'bulk') NOT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    template_id INT NULL,
    status ENUM('sent', 'failed', 'pending') NOT NULL,
    message_id VARCHAR(255) NULL,
    error_message TEXT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (template_id) REFERENCES email_templates(id)
);

-- SES configuration table
CREATE TABLE ses_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_name VARCHAR(50) NOT NULL,
    smtp_host VARCHAR(255) NOT NULL,
    smtp_port INT NOT NULL,
    smtp_username VARCHAR(255) NOT NULL,
    smtp_password VARCHAR(255) NOT NULL,
    from_email VARCHAR(255) NOT NULL,
    from_name VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: marina123)
INSERT INTO users (username, password, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@marina.gov.ph');

-- Insert Marina email templates
INSERT INTO email_templates (name, type, subject, body_html, body_text) VALUES 
(
    'OTP Verification',
    'otp',
    'Marina Portal - OTP Verification Code',
    '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Marina OTP Verification</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <img src="https://marina.gov.ph/wp-content/uploads/2019/06/marina-logo.png" alt="Marina Logo" style="height: 60px; margin-bottom: 15px;">
        <h1 style="color: white; margin: 0; font-size: 24px;">Maritime Industry Authority</h1>
        <p style="color: #e0e7ff; margin: 5px 0 0 0;">Republic of the Philippines</p>
    </div>
    
    <div style="background: white; padding: 40px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 10px 10px;">
        <h2 style="color: #1e40af; margin-top: 0;">OTP Verification Code</h2>
        
        <p>Dear Marina Portal User,</p>
        
        <p>Your One-Time Password (OTP) for accessing Marina services is:</p>
        
        <div style="background: #f3f4f6; border: 2px dashed #1e40af; padding: 20px; text-align: center; margin: 25px 0; border-radius: 8px;">
            <span style="font-size: 32px; font-weight: bold; color: #1e40af; letter-spacing: 5px;">{OTP_CODE}</span>
        </div>
        
        <p><strong>Important Security Information:</strong></p>
        <ul style="color: #6b7280;">
            <li>This OTP is valid for <strong>10 minutes</strong> only</li>
            <li>Do not share this code with anyone</li>
            <li>Marina will never ask for your OTP via phone or email</li>
            <li>If you did not request this code, please contact Marina IT support immediately</li>
        </ul>
        
        <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; color: #92400e;"><strong>Security Notice:</strong> This is an automated message from Marina''s secure system. Please do not reply to this email.</p>
        </div>
        
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        
        <div style="text-align: center; color: #6b7280; font-size: 14px;">
            <p><strong>Maritime Industry Authority</strong><br>
            Republic of the Philippines<br>
            Email: info@marina.gov.ph | Website: www.marina.gov.ph</p>
            
            <p style="margin-top: 20px; font-size: 12px;">
                This email was sent from an automated system. Please do not reply.<br>
                For technical support, contact Marina IT Department.
            </p>
        </div>
    </div>
</body>
</html>',
    'Marina Portal - OTP Verification Code

Dear Marina Portal User,

Your One-Time Password (OTP) for accessing Marina services is: {OTP_CODE}

Important Security Information:
- This OTP is valid for 10 minutes only
- Do not share this code with anyone
- Marina will never ask for your OTP via phone or email
- If you did not request this code, please contact Marina IT support immediately

Maritime Industry Authority
Republic of the Philippines
Email: info@marina.gov.ph | Website: www.marina.gov.ph

This is an automated message. Please do not reply.'
),
(
    'Transaction Confirmation',
    'transaction',
    'Marina Portal - Transaction Confirmation #{TRANSACTION_ID}',
    '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Marina Transaction Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <img src="https://marina.gov.ph/wp-content/uploads/2019/06/marina-logo.png" alt="Marina Logo" style="height: 60px; margin-bottom: 15px;">
        <h1 style="color: white; margin: 0; font-size: 24px;">Maritime Industry Authority</h1>
        <p style="color: #d1fae5; margin: 5px 0 0 0;">Republic of the Philippines</p>
    </div>
    
    <div style="background: white; padding: 40px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 10px 10px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="background: #d1fae5; color: #059669; padding: 10px 20px; border-radius: 25px; display: inline-block; font-weight: bold;">
                ✓ TRANSACTION SUCCESSFUL
            </div>
        </div>
        
        <h2 style="color: #059669; margin-top: 0;">Transaction Confirmation</h2>
        
        <p>Dear {CUSTOMER_NAME},</p>
        
        <p>Your transaction has been successfully processed. Here are the details:</p>
        
        <div style="background: #f9fafb; border: 1px solid #e5e7eb; padding: 25px; border-radius: 8px; margin: 25px 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #374151;">Transaction ID:</td>
                    <td style="padding: 8px 0; color: #059669; font-weight: bold;">{TRANSACTION_ID}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #374151;">Service:</td>
                    <td style="padding: 8px 0;">{SERVICE_NAME}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #374151;">Amount:</td>
                    <td style="padding: 8px 0; font-weight: bold;">₱{AMOUNT}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #374151;">Date & Time:</td>
                    <td style="padding: 8px 0;">{TRANSACTION_DATE}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #374151;">Reference Number:</td>
                    <td style="padding: 8px 0; font-family: monospace; background: #f3f4f6; padding: 5px; border-radius: 4px;">{REFERENCE_NUMBER}</td>
                </tr>
            </table>
        </div>
        
        <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; color: #1e40af;"><strong>Next Steps:</strong> Please keep this confirmation for your records. You can track your application status on the Marina portal using your reference number.</p>
        </div>
        
        <p><strong>Important Notes:</strong></p>
        <ul style="color: #6b7280;">
            <li>This serves as your official receipt</li>
            <li>Processing time may vary depending on the service</li>
            <li>You will receive updates via email and SMS</li>
            <li>For inquiries, please reference your transaction ID</li>
        </ul>
        
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        
        <div style="text-align: center; color: #6b7280; font-size: 14px;">
            <p><strong>Maritime Industry Authority</strong><br>
            Republic of the Philippines<br>
            Email: info@marina.gov.ph | Website: www.marina.gov.ph<br>
            Hotline: (02) 8527-8537</p>
            
            <p style="margin-top: 20px; font-size: 12px;">
                This email was sent from an automated system. Please do not reply.<br>
                For assistance, visit our website or contact our customer service.
            </p>
        </div>
    </div>
</body>
</html>',
    'Marina Portal - Transaction Confirmation #{TRANSACTION_ID}

Dear {CUSTOMER_NAME},

Your transaction has been successfully processed.

Transaction Details:
- Transaction ID: {TRANSACTION_ID}
- Service: {SERVICE_NAME}
- Amount: ₱{AMOUNT}
- Date & Time: {TRANSACTION_DATE}
- Reference Number: {REFERENCE_NUMBER}

Important Notes:
- This serves as your official receipt
- Processing time may vary depending on the service
- You will receive updates via email and SMS
- For inquiries, please reference your transaction ID

Maritime Industry Authority
Republic of the Philippines
Email: info@marina.gov.ph | Website: www.marina.gov.ph
Hotline: (02) 8527-8537

This is an automated message. Please do not reply.'
),
(
    'Official Communication',
    'official',
    'Marina Official Notice - {NOTICE_TITLE}',
    '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Marina Official Communication</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #7c2d12 0%, #dc2626 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <img src="https://marina.gov.ph/wp-content/uploads/2019/06/marina-logo.png" alt="Marina Logo" style="height: 60px; margin-bottom: 15px;">
        <h1 style="color: white; margin: 0; font-size: 24px;">Maritime Industry Authority</h1>
        <p style="color: #fecaca; margin: 5px 0 0 0;">Republic of the Philippines</p>
    </div>
    
    <div style="background: white; padding: 40px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 10px 10px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="background: #fef2f2; color: #dc2626; padding: 10px 20px; border-radius: 25px; display: inline-block; font-weight: bold; border: 2px solid #fecaca;">
                📋 OFFICIAL NOTICE
            </div>
        </div>
        
        <h2 style="color: #dc2626; margin-top: 0;">{NOTICE_TITLE}</h2>
        
        <p>Dear Stakeholder,</p>
        
        <div style="background: #f9fafb; border: 1px solid #e5e7eb; padding: 25px; border-radius: 8px; margin: 25px 0;">
            {NOTICE_CONTENT}
        </div>
        
        <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; color: #92400e;"><strong>Action Required:</strong> {ACTION_REQUIRED}</p>
        </div>
        
        <p><strong>Contact Information:</strong></p>
        <ul style="color: #6b7280;">
            <li>Email: {CONTACT_EMAIL}</li>
            <li>Phone: {CONTACT_PHONE}</li>
            <li>Office Hours: Monday to Friday, 8:00 AM - 5:00 PM</li>
        </ul>
        
        <div style="margin: 30px 0; padding: 20px; background: #f3f4f6; border-radius: 8px;">
            <p style="margin: 0; font-size: 14px; color: #4b5563;">
                <strong>Notice Date:</strong> {NOTICE_DATE}<br>
                <strong>Reference:</strong> {REFERENCE_NUMBER}<br>
                <strong>Issued by:</strong> {ISSUING_OFFICE}
            </p>
        </div>
        
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        
        <div style="text-align: center; color: #6b7280; font-size: 14px;">
            <p><strong>Maritime Industry Authority</strong><br>
            Republic of the Philippines<br>
            Email: info@marina.gov.ph | Website: www.marina.gov.ph<br>
            Main Office: 1584 Taft Avenue, Manila 1004</p>
            
            <p style="margin-top: 20px; font-size: 12px;">
                This is an official communication from Marina. Please retain this notice for your records.<br>
                For verification, contact Marina directly using the official contact information above.
            </p>
        </div>
    </div>
</body>
</html>',
    'Marina Official Notice - {NOTICE_TITLE}

Dear Stakeholder,

{NOTICE_CONTENT}

Action Required: {ACTION_REQUIRED}

Contact Information:
- Email: {CONTACT_EMAIL}
- Phone: {CONTACT_PHONE}
- Office Hours: Monday to Friday, 8:00 AM - 5:00 PM

Notice Details:
- Notice Date: {NOTICE_DATE}
- Reference: {REFERENCE_NUMBER}
- Issued by: {ISSUING_OFFICE}

Maritime Industry Authority
Republic of the Philippines
Email: info@marina.gov.ph | Website: www.marina.gov.ph
Main Office: 1584 Taft Avenue, Manila 1004

This is an official communication from Marina. Please retain this notice for your records.'
);

-- Insert default SES configuration (placeholder)
INSERT INTO ses_config (config_name, smtp_host, smtp_port, smtp_username, smtp_password, from_email, from_name) VALUES 
('Default SES Config', 'email-smtp.ap-southeast-1.amazonaws.com', 587, 'YOUR_SMTP_USERNAME', 'YOUR_SMTP_PASSWORD', 'noreply@marina.gov.ph', 'Marina Portal');
