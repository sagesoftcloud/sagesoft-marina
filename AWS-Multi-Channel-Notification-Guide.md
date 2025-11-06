# AWS Email Notification System Guide
## Production-Ready Setup for Philippines Market

### Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Prerequisites](#prerequisites)
4. [AWS SES Setup (Email)](#aws-ses-setup-email)
5. [IAM Configuration](#iam-configuration)
6. [Implementation Examples](#implementation-examples)
7. [Testing Procedures](#testing-procedures)
8. [Monitoring & Logging](#monitoring--logging)
9. [Security Considerations](#security-considerations)
10. [Troubleshooting](#troubleshooting)

---

## Overview

This guide provides step-by-step instructions to implement a production-ready email notification system using AWS SES (Simple Email Service) for the Philippines market.

**Supported Use Cases:**
- OTP Authentication via Email
- Transaction Alerts
- Marketing Messages
- System Notifications
- Email Verification
- Welcome Emails
- Password Reset Notifications

---

## Architecture

```
[Web App/Mobile App] → [API Service] → [Email Service]
                                            ↓
                                    [Amazon SES]
                                            ↓
                                    [Email to Inbox]
```

---

## Prerequisites

### AWS Account Requirements
- ✅ AWS Account (Already available as AWS Partner)
- ✅ AWS CLI installed and configured
- ✅ Appropriate billing setup

### Philippines-Specific Considerations
- Email deliverability for .ph domains
- Local compliance requirements (Data Privacy Act)
- Timezone considerations for email scheduling

---

## AWS SES Setup (Email)

> **What we're doing:** Setting up Amazon SES to send emails from marina.gov.ph domain with proper authentication and deliverability.

### Step 1: Verify Email Domain/Address

> **Purpose:** AWS requires verification of email addresses/domains before you can send emails from them. This prevents spam and ensures you own the domain.
> 
> **What happens:** AWS will provide verification tokens that you add to your DNS records to prove domain ownership.

#### Verify Single Email Address:

**Via AWS Console:**
1. Go to AWS SES Console → Identities
2. Click "Create identity"
3. Select "Email address"
4. Enter: `noreply@marina.gov.ph`
5. Click "Create identity"
6. Check email inbox for verification link
7. Click the verification link

> **Status Check:** Email address shows "Verified" status in SES console

**Via AWS CLI:**
```bash
# Verify sender email
aws ses verify-email-identity \
    --email-address noreply@marina.gov.ph

# Check verification status
aws ses get-identity-verification-attributes \
    --identities noreply@marina.gov.ph
```

#### Verify Domain (Recommended for Production):

> **Why domain verification:** Allows sending from any email address under marina.gov.ph (e.g., alerts@marina.gov.ph, support@marina.gov.ph) without individual verification.

**Via AWS Console:**
1. Go to AWS SES Console → Identities
2. Click "Create identity"
3. Select "Domain"
4. Enter domain: `marina.gov.ph`
5. Click "Create identity"
6. Copy the TXT record value shown
7. Add TXT record to marina.gov.ph DNS:
   - Name: `_amazonses.marina.gov.ph`
   - Value: (the verification token from step 6)
8. Wait for DNS propagation (up to 72 hours)
9. Verification status will change to "Verified"

> **DNS Record Example:**
> ```
> Type: TXT
> Name: _amazonses.marina.gov.ph
> Value: "h1bGVhc2UgcmVwbGFjZSB0aGlzIHdpdGggYWN0dWFsIHRva2Vu"
> ```
> 
> **Status Check:** Domain shows "Verified" status in SES console
> **Next Step:** Once verified, proceed to DKIM configuration

**Via AWS CLI:**
```bash
# Verify domain
aws ses verify-domain-identity \
    --domain marina.gov.ph

# Get domain verification token
aws ses get-identity-verification-attributes \
    --identities marina.gov.ph
```

---

### Step 2: Configure DKIM (Domain Authentication)

> **Purpose:** DKIM (DomainKeys Identified Mail) adds digital signatures to emails, proving they actually came from marina.gov.ph and weren't tampered with.
> 
> **Why it matters:** Improves email deliverability, reduces spam classification, and builds trust with email providers.
> 
> **What we're doing:** Enabling AWS Easy DKIM and adding DNS records to activate email authentication.

**Via AWS Console:**
1. Go to AWS SES Console → Identities
2. Click on `marina.gov.ph` domain
3. Go to "Authentication" tab
4. In "DomainKeys Identified Mail (DKIM)" section:
   - Click "Edit"
   - Check "Enable DKIM signing"
   - Select "Easy DKIM" (recommended)
   - RSA key length: 2048-bit (recommended)
   - Click "Save changes"
5. Copy the 3 CNAME records shown
6. Add these CNAME records to marina.gov.ph DNS:
   ```
   token1._domainkey.marina.gov.ph → token1.dkim.amazonses.com
   token2._domainkey.marina.gov.ph → token2.dkim.amazonses.com
   token3._domainkey.marina.gov.ph → token3.dkim.amazonses.com
   ```
7. Wait for DNS propagation
8. DKIM status will change to "Successful"

> **DNS Records Example:**
> ```
> Type: CNAME
> Name: abc123._domainkey.marina.gov.ph
> Value: abc123.dkim.amazonses.com
> ```
> 
> **Status Check:** DKIM shows "Successful" status in Authentication tab
> **What's accomplished:** Emails from marina.gov.ph will now have digital signatures
> **Next Step:** Request production access to send to any email address

**Via AWS CLI:**
```bash
# Enable DKIM for domain
aws ses put-identity-dkim-attributes \
    --identity marina.gov.ph \
    --dkim-enabled

# Get DKIM tokens for DNS setup
aws ses get-identity-dkim-attributes \
    --identities marina.gov.ph
```

---

### Step 3: Request Production Access

> **Current Status:** Your SES account is in "sandbox mode" - can only send to verified email addresses with a limit of 200 emails/day.
> 
> **What we're doing:** Requesting AWS to move your account to production mode for unlimited sending to any email address.
> 
> **Why needed:** Marina needs to send emails to citizens and stakeholders who haven't verified their addresses with AWS.

**Via AWS Console:**
1. Go to AWS SES Console → Account dashboard
2. Click "Request production access" button
3. Fill out the form:
   - **Mail type**: Transactional
   - **Website URL**: https://marina.gov.ph
   - **Use case description**: 
     ```
     Government email notifications for Marina (Maritime Industry Authority) including:
     - OTP authentication for citizen services
     - Transaction alerts and confirmations
     - System notifications for maritime services
     - Official communications to stakeholders
     ```
   - **Additional contact addresses**: Add backup email
   - **Preferred AWS region**: Asia Pacific (Singapore) ap-southeast-1
4. Click "Submit request"
5. Wait for AWS approval (usually 24-48 hours)
6. Check email for approval notification

> **What happens next:** AWS reviews your request and typically approves government use cases within 24-48 hours.
> 
> **Status Check:** Account dashboard will show "Production" instead of "Sandbox"
> **Limits after approval:** 
> - 200 emails per second (can be increased)
> - 200,000 emails per day (can be increased)
> - Can send to any email address
> 
> **Next Step:** Configure bounce and complaint handling for email reputation management

**Via AWS CLI (Check Status):**
```bash
# Check current sending quota (sandbox = 200 emails/day)
aws ses get-send-quota

# Note: Production access request must be done via Console
```

---

### Step 4: Configure Bounce and Complaint Handling

> **Purpose:** Monitor email bounces (failed deliveries) and complaints (spam reports) to maintain good sender reputation.
> 
> **Why critical:** High bounce/complaint rates can get your domain blacklisted by email providers.
> 
> **What we're setting up:** SNS notifications to track email delivery issues automatically.

**Via AWS Console:**
1. **Create SNS Topic:**
   - Go to Amazon SNS Console
   - Click "Create topic"
   - Type: Standard
   - Name: `ses-bounces`
   - Click "Create topic"
   - Copy the Topic ARN

> **What we just did:** Created a notification channel to receive bounce/complaint alerts

2. **Configure SES Notifications:**
   - Go to AWS SES Console → Identities
   - Click on `marina.gov.ph` domain
   - Go to "Notifications" tab
   - Click "Edit" for Bounce notifications:
     - Enable: ✓
     - SNS topic: Select `ses-bounces`
     - Include original headers: ✓
   - Click "Edit" for Complaint notifications:
     - Enable: ✓
     - SNS topic: Select `ses-bounces`
     - Include original headers: ✓
   - Click "Save changes"

> **What's accomplished:** 
> - AWS will now notify you when emails bounce or get marked as spam
> - You can track delivery issues and maintain sender reputation
> - Automatic monitoring of email health metrics
> 
> **Status Check:** Notifications tab shows SNS topics configured for bounces and complaints
> **Next Step:** Set up IAM permissions for your application to send emails

**Via AWS CLI:**
```bash
# Create SNS topic for bounce notifications
aws sns create-topic --name ses-bounces

# Set bounce notifications
aws ses put-identity-notification-attributes \
    --identity marina.gov.ph \
    --notification-type Bounce \
    --sns-topic arn:aws:sns:ap-southeast-1:ACCOUNT-ID:ses-bounces
```

> **Progress Summary:**
> ✅ Domain verified - AWS knows you own marina.gov.ph
> ✅ DKIM configured - Emails will be digitally signed
> ✅ Production access requested - Can send to any email
> ✅ Bounce handling setup - Monitoring email reputation
> 
> **Next:** Create IAM permissions for your application

---

## IAM Configuration

> **What we're doing:** Creating secure access credentials for your application to send emails through SES.
> 
> **Why needed:** Your application needs permission to use AWS SES, but we want to limit access to only what's necessary (principle of least privilege).
> 
> **Security approach:** Create a dedicated user with minimal permissions instead of using root account credentials.

### Step 1: Create IAM Policy

> **Purpose:** Define exactly what permissions your email service needs - no more, no less.
> 
> **What this policy allows:**
> - Send emails via SES
> - Check sending quotas and statistics
> - Write logs for monitoring
> 
> **What it doesn't allow:** Access to other AWS services, user management, or billing

**Via AWS Console:**
1. Go to IAM Console → Policies
2. Click "Create policy"
3. Select "JSON" tab
4. Paste the following policy:
   ```json
   {
       "Version": "2012-10-17",
       "Statement": [
           {
               "Effect": "Allow",
               "Action": [
                   "ses:SendEmail",
                   "ses:SendRawEmail",
                   "ses:GetSendQuota",
                   "ses:GetSendStatistics",
                   "ses:GetIdentityVerificationAttributes",
                   "ses:GetIdentityDkimAttributes"
               ],
               "Resource": "*"
           },
           {
               "Effect": "Allow",
               "Action": [
                   "logs:CreateLogGroup",
                   "logs:CreateLogStream",
                   "logs:PutLogEvents"
               ],
               "Resource": "arn:aws:logs:*:*:*"
           }
       ]
   }
   ```
5. Click "Next: Tags" (optional)
6. Click "Next: Review"
7. Policy name: `EmailNotificationServicePolicy`
8. Description: `Policy for Marina email notification service`
9. Click "Create policy"

> **Status Check:** Policy appears in IAM Policies list with "Customer managed" type
> **What's accomplished:** Defined security boundaries for email service
> **Next Step:** Create a user and attach this policy

**Via AWS CLI:**
```bash
# Create policy file
cat > email-notification-policy.json << 'EOF'
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "ses:SendEmail",
                "ses:SendRawEmail",
                "ses:GetSendQuota",
                "ses:GetSendStatistics",
                "ses:GetIdentityVerificationAttributes",
                "ses:GetIdentityDkimAttributes"
            ],
            "Resource": "*"
        },
        {
            "Effect": "Allow",
            "Action": [
                "logs:CreateLogGroup",
                "logs:CreateLogStream",
                "logs:PutLogEvents"
            ],
            "Resource": "arn:aws:logs:*:*:*"
        }
    ]
}
EOF

# Create IAM policy
aws iam create-policy \
    --policy-name EmailNotificationServicePolicy \
    --policy-document file://email-notification-policy.json
```

---

### Step 2: Create IAM User

> **Purpose:** Create a dedicated service account for your email application with programmatic access.
> 
> **What we're creating:** A user account that can only send emails (not access AWS Console) with API keys for your application.
> 
> **Security benefit:** If these credentials are compromised, damage is limited to email sending only.

**Via AWS Console:**
1. Go to IAM Console → Users
2. Click "Create user"
3. User name: `email-notification-service-user`
4. Select "Programmatic access"
5. Click "Next: Permissions"
6. Select "Attach existing policies directly"
7. Search and select: `EmailNotificationServicePolicy`
8. Click "Next: Tags" (optional)
9. Click "Next: Review"
10. Click "Create user"
11. **IMPORTANT**: Copy Access Key ID and Secret Access Key
12. Store credentials securely

> **⚠️ CRITICAL SECURITY STEP:** 
> - Download the CSV file or copy the credentials immediately
> - Store them in a secure password manager or environment variables
> - Never commit these credentials to code repositories
> - You cannot retrieve the Secret Access Key again after this screen
> 
> **Status Check:** User appears in IAM Users list with policy attached
> **What's accomplished:** Created secure API access for your application
> **Next Step:** Configure your application with these credentials

**Via AWS CLI:**
```bash
# Create IAM user for application
aws iam create-user --user-name email-notification-service-user

# Attach policy to user
aws iam attach-user-policy \
    --user-name email-notification-service-user \
    --policy-arn arn:aws:iam::ACCOUNT-ID:policy/EmailNotificationServicePolicy

# Create access keys
aws iam create-access-key --user-name email-notification-service-user
```

> **Progress Summary:**
> ✅ SES domain verified and configured
> ✅ DKIM authentication enabled
> ✅ Production access requested
> ✅ Bounce/complaint monitoring setup
> ✅ IAM policy created with minimal permissions
> ✅ Service user created with API credentials
> 
> **Next:** Implement the email service in your application code

---

## Implementation Examples

### Python Implementation
```python
import boto3
import json
import logging
from datetime import datetime
from typing import Dict, Any, Optional

class EmailNotificationService:
    def __init__(self, region='ap-southeast-1'):
        self.ses = boto3.client('ses', region_name=region)
        self.logger = logging.getLogger(__name__)
        
    def send_email(self, to_email: str, subject: str, body_text: str, 
                   body_html: str = None, from_email: str = 'noreply@marina.gov.ph',
                   reply_to: str = None) -> Dict[str, Any]:
        """Send Email via Amazon SES"""
        try:
            message = {
                'Subject': {'Data': subject, 'Charset': 'UTF-8'},
                'Body': {'Text': {'Data': body_text, 'Charset': 'UTF-8'}}
            }
            
            if body_html:
                message['Body']['Html'] = {'Data': body_html, 'Charset': 'UTF-8'}
            
            destination = {'ToAddresses': [to_email]}
            
            params = {
                'Source': from_email,
                'Destination': destination,
                'Message': message
            }
            
            if reply_to:
                params['ReplyToAddresses'] = [reply_to]
            
            response = self.ses.send_email(**params)
            
            self.logger.info(f"Email sent successfully: {response['MessageId']}")
            return {
                'success': True,
                'message_id': response['MessageId'],
                'to_email': to_email
            }
            
        except Exception as e:
            self.logger.error(f"Email sending failed: {str(e)}")
            return {
                'success': False,
                'error': str(e),
                'to_email': to_email
            }
    
    def send_otp_email(self, to_email: str, otp_code: str, company_name: str = "Your Company") -> Dict[str, Any]:
        """Send OTP via Email with professional template"""
        subject = f"Your {company_name} Verification Code"
        
        body_text = f"""
Your verification code is: {otp_code}

This code is valid for 5 minutes.
Do not share this code with anyone.

If you didn't request this code, please ignore this email.

Best regards,
{company_name} Team
        """
        
        body_html = f"""
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #2c3e50;">Verification Code</h2>
                <p>Your verification code is:</p>
                <div style="background-color: #f8f9fa; padding: 20px; text-align: center; border-radius: 5px; margin: 20px 0;">
                    <h1 style="color: #007bff; font-size: 32px; margin: 0; letter-spacing: 5px;">{otp_code}</h1>
                </div>
                <p><strong>This code is valid for 5 minutes.</strong></p>
                <p>Do not share this code with anyone.</p>
                <p>If you didn't request this code, please ignore this email.</p>
                <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
                <p style="color: #666; font-size: 12px;">
                    Best regards,<br>
                    {company_name} Team
                </p>
            </div>
        </body>
        </html>
        """
        
        return self.send_email(to_email, subject, body_text, body_html)
    
    def send_transaction_alert(self, to_email: str, transaction_data: Dict[str, Any]) -> Dict[str, Any]:
        """Send transaction alert email"""
        subject = "Transaction Alert - " + transaction_data.get('type', 'Transaction')
        
        body_text = f"""
Transaction Alert

Type: {transaction_data.get('type', 'N/A')}
Amount: {transaction_data.get('amount', 'N/A')}
Date: {transaction_data.get('date', datetime.now().strftime('%Y-%m-%d %H:%M:%S'))}
Reference: {transaction_data.get('reference', 'N/A')}

Thank you for using our service.
        """
        
        body_html = f"""
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #28a745;">Transaction Alert</h2>
                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
                    <p><strong>Type:</strong> {transaction_data.get('type', 'N/A')}</p>
                    <p><strong>Amount:</strong> {transaction_data.get('amount', 'N/A')}</p>
                    <p><strong>Date:</strong> {transaction_data.get('date', datetime.now().strftime('%Y-%m-%d %H:%M:%S'))}</p>
                    <p><strong>Reference:</strong> {transaction_data.get('reference', 'N/A')}</p>
                </div>
                <p>Thank you for using our service.</p>
            </div>
        </body>
        </html>
        """
        
        return self.send_email(to_email, subject, body_text, body_html)
    
    def send_marketing_email(self, to_email: str, campaign_data: Dict[str, Any]) -> Dict[str, Any]:
        """Send marketing email"""
        subject = campaign_data.get('subject', 'Special Offer')
        
        body_text = campaign_data.get('text_content', 'Check out our latest offers!')
        body_html = campaign_data.get('html_content')
        
        return self.send_email(to_email, subject, body_text, body_html)
    
    def send_welcome_email(self, to_email: str, user_name: str, company_name: str = "Your Company") -> Dict[str, Any]:
        """Send welcome email to new users"""
        subject = f"Welcome to {company_name}!"
        
        body_text = f"""
Hi {user_name},

Welcome to {company_name}!

We're excited to have you on board. Your account has been successfully created.

If you have any questions, feel free to contact our support team.

Best regards,
{company_name} Team
        """
        
        body_html = f"""
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #007bff;">Welcome to {company_name}!</h2>
                <p>Hi {user_name},</p>
                <p>We're excited to have you on board. Your account has been successfully created.</p>
                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
                    <p>If you have any questions, feel free to contact our support team.</p>
                </div>
                <p>Best regards,<br>{company_name} Team</p>
            </div>
        </body>
        </html>
        """
        
        return self.send_email(to_email, subject, body_text, body_html)

# Usage Examples
if __name__ == "__main__":
    # Initialize service
    email_service = EmailNotificationService()
    
    # Send OTP email
    otp_result = email_service.send_otp_email("user@example.com", "123456", "Marina Company")
    print(f"OTP Email Result: {otp_result}")
    
    # Send transaction alert
    transaction_data = {
        'type': 'Payment',
        'amount': 'PHP 1,000.00',
        'date': '2024-01-06 09:30:00',
        'reference': 'TXN123456789'
    }
    
    alert_result = email_service.send_transaction_alert("user@example.com", transaction_data)
    print(f"Transaction Alert Result: {alert_result}")
    
    # Send welcome email
    welcome_result = email_service.send_welcome_email("newuser@example.com", "John Doe", "Marina Company")
    print(f"Welcome Email Result: {welcome_result}")
```

### Node.js Implementation
```javascript
const AWS = require('aws-sdk');

class EmailNotificationService {
    constructor(region = 'ap-southeast-1') {
        this.ses = new AWS.SES({ region });
    }
    
    async sendEmail(toEmail, subject, bodyText, bodyHtml = null, fromEmail = 'noreply@marina.gov.ph') {
        try {
            const params = {
                Source: fromEmail,
                Destination: { ToAddresses: [toEmail] },
                Message: {
                    Subject: { Data: subject, Charset: 'UTF-8' },
                    Body: { Text: { Data: bodyText, Charset: 'UTF-8' } }
                }
            };
            
            if (bodyHtml) {
                params.Message.Body.Html = { Data: bodyHtml, Charset: 'UTF-8' };
            }
            
            const result = await this.ses.sendEmail(params).promise();
            return {
                success: true,
                messageId: result.MessageId,
                toEmail
            };
        } catch (error) {
            return {
                success: false,
                error: error.message,
                toEmail
            };
        }
    }
    
    async sendOTPEmail(toEmail, otpCode, companyName = 'Your Company') {
        const subject = `Your ${companyName} Verification Code`;
        const bodyText = `Your verification code is: ${otpCode}\n\nThis code is valid for 5 minutes.\nDo not share this code with anyone.`;
        
        const bodyHtml = `
        <html>
        <body style="font-family: Arial, sans-serif;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2>Verification Code</h2>
                <div style="background-color: #f8f9fa; padding: 20px; text-align: center;">
                    <h1 style="color: #007bff; font-size: 32px;">${otpCode}</h1>
                </div>
                <p><strong>This code is valid for 5 minutes.</strong></p>
            </div>
        </body>
        </html>
        `;
        
        return await this.sendEmail(toEmail, subject, bodyText, bodyHtml);
    }
}

module.exports = EmailNotificationService;
```

---

## Testing Procedures

> **What we're doing:** Verifying that your SES setup works correctly before integrating with your application.
> 
> **Testing strategy:** Start with simple tests, then progress to more complex scenarios that match your production use cases.
> 
> **Why test now:** Catch configuration issues early before they affect real users.

### Step 1: Email Testing

> **Purpose:** Verify basic email sending functionality and deliverability.
> 
> **What to test:**
> - Basic text email delivery
> - HTML email rendering
> - Different email providers (Gmail, Yahoo, Outlook)
> - Spam folder placement

**Via AWS Console:**
1. Go to AWS SES Console → Identities
2. Click on `marina.gov.ph` domain
3. Click "Send test email" button
4. Fill in the form:
   - **From address**: `noreply@marina.gov.ph`
   - **To address**: Your test email
   - **Subject**: `Test Email from Marina`
   - **Body**: 
     ```
     This is a test email from Marina's AWS SES setup.
     
     If you receive this, the configuration is working correctly.
     ```
5. Click "Send test email"
6. Check recipient inbox (including spam folder)

> **What to verify:**
> ✅ Email arrives in inbox (not spam)
> ✅ Sender shows as "noreply@marina.gov.ph"
> ✅ No security warnings in email client
> ✅ DKIM signature present (check email headers)
> 
> **If email goes to spam:** Check DKIM setup and DNS records
> **If email doesn't arrive:** Verify domain verification and production access

**Via AWS CLI:**
```bash
# Test basic email
aws ses send-email \
    --source "noreply@marina.gov.ph" \
    --destination "ToAddresses=test@example.com" \
    --message "Subject={Data=Test Email},Body={Text={Data=This is a test email from AWS SES}}"

# Test HTML email with file
cat > test-email.json << 'EOF'
{
    "Subject": {
        "Data": "Test HTML Email",
        "Charset": "UTF-8"
    },
    "Body": {
        "Text": {
            "Data": "This is a test email from AWS SES (text version)",
            "Charset": "UTF-8"
        },
        "Html": {
            "Data": "<html><body><h1>Test Email</h1><p>This is a <strong>test email</strong> from AWS SES</p></body></html>",
            "Charset": "UTF-8"
        }
    }
}
EOF

aws ses send-email \
    --source "noreply@marina.gov.ph" \
    --destination "ToAddresses=test@example.com" \
    --message file://test-email.json
```

> **Status Check:** Successful CLI commands return a MessageId
> **Next Step:** Test your application's email templates

---

### Step 2: OTP Email Testing

> **Purpose:** Test the specific email templates your application will use for OTP authentication.
> 
> **What we're validating:**
> - Template rendering works correctly
> - OTP codes display properly
> - Email formatting is professional
> - Delivery time is acceptable for OTP use

```python
# Test OTP email functionality
from email_notification_service import EmailNotificationService

def test_otp_email():
    service = EmailNotificationService()
    
    # Test OTP email
    result = service.send_otp_email(
        to_email="test@marina.gov.ph",
        otp_code="123456",
        company_name="Marina Test"
    )
    
    print(f"OTP Email Test Result: {result}")
    return result['success']

if __name__ == "__main__":
    success = test_otp_email()
    print(f"Test {'PASSED' if success else 'FAILED'}")
```

> **What to verify:**
> ✅ OTP code is clearly visible and formatted
> ✅ Email arrives within 30 seconds (important for OTP)
> ✅ Professional appearance with Marina branding
> ✅ Clear expiration time (5 minutes)
> ✅ Security warnings about not sharing code
> 
> **If OTP emails are slow:** Check your region selection and network
> **If formatting is broken:** Verify HTML template syntax

---

### Step 3: Load Testing Script

> **Purpose:** Ensure your setup can handle expected email volumes without hitting rate limits.
> 
> **What we're testing:**
> - Rate limit compliance (14 emails/second default)
> - Error handling under load
> - Success rate at volume
> - Performance metrics

```python
import asyncio
import time
from concurrent.futures import ThreadPoolExecutor
from email_notification_service import EmailNotificationService

async def load_test_emails():
    service = EmailNotificationService()
    
    # Test parameters
    test_email = "test@marina.gov.ph"
    num_emails = 50  # Start with smaller number for testing
    
    start_time = time.time()
    
    # Email Load Test
    with ThreadPoolExecutor(max_workers=5) as executor:
        email_futures = [
            executor.submit(
                service.send_email,
                test_email,
                f"Load Test Email {i}",
                f"This is load test email number {i}"
            )
            for i in range(num_emails)
        ]
        
        email_results = [future.result() for future in email_futures]
    
    end_time = time.time()
    
    # Results
    email_success = sum(1 for r in email_results if r['success'])
    
    print(f"Email Load Test Results:")
    print(f"Duration: {end_time - start_time:.2f} seconds")
    print(f"Success Rate: {email_success}/{num_emails} ({email_success/num_emails*100:.1f}%)")
    print(f"Emails per second: {email_success/(end_time - start_time):.2f}")

if __name__ == "__main__":
    asyncio.run(load_test_emails())
```

> **Expected Results:**
> - Success rate: >95%
> - Rate: <14 emails/second (to avoid throttling)
> - No rate limit errors
> 
> **If you see throttling errors:** Reduce concurrent workers or add delays
> **If success rate is low:** Check network connectivity and credentials

---

### Step 4: Email Template Testing

> **Purpose:** Validate all email templates your application will use in production.
> 
> **Templates to test:**
> - OTP authentication emails
> - Transaction alerts
> - Welcome emails
> - Password reset notifications
> - Marketing communications

```python
def test_all_email_templates():
    service = EmailNotificationService()
    
    test_results = []
    
    # Test OTP Email
    otp_result = service.send_otp_email("test@marina.gov.ph", "123456")
    test_results.append(("OTP Email", otp_result['success']))
    
    # Test Transaction Alert
    transaction_data = {
        'type': 'Payment',
        'amount': 'PHP 1,500.00',
        'date': '2024-01-06 10:30:00',
        'reference': 'TEST123456'
    }
    alert_result = service.send_transaction_alert("test@marina.gov.ph", transaction_data)
    test_results.append(("Transaction Alert", alert_result['success']))
    
    # Test Welcome Email
    welcome_result = service.send_welcome_email("test@marina.gov.ph", "Test User")
    test_results.append(("Welcome Email", welcome_result['success']))
    
    # Print results
    print("Email Template Test Results:")
    for template_name, success in test_results:
        status = "✅ PASS" if success else "❌ FAIL"
        print(f"{template_name}: {status}")
    
    return all(result[1] for result in test_results)
```

> **What to verify for each template:**
> ✅ Professional appearance and branding
> ✅ All dynamic content renders correctly
> ✅ Links work (if any)
> ✅ Mobile-friendly formatting
> ✅ Appropriate tone for government communications
> 
> **Status Check:** All templates should pass testing
> **What's accomplished:** Confirmed all email types work correctly
> **Next Step:** Set up monitoring and logging for production use

---

## Monitoring & Logging

> **What we're doing:** Setting up comprehensive monitoring to track email delivery, performance, and reputation metrics.
> 
> **Why monitoring matters:** 
> - Detect delivery issues before users complain
> - Maintain good sender reputation
> - Track usage for capacity planning
> - Meet government audit requirements
> 
> **Monitoring strategy:** Dashboard for visual monitoring + automated alerts for immediate issues

### Step 1: CloudWatch Dashboard Setup

> **Purpose:** Create a visual dashboard to monitor email metrics in real-time.
> 
> **What we'll track:**
> - Email send volume and success rates
> - Bounce and complaint rates (critical for reputation)
> - Delivery performance metrics
> - Cost tracking

**Via AWS Console:**
1. Go to CloudWatch Console → Dashboards
2. Click "Create dashboard"
3. Dashboard name: `EmailNotificationSystem`
4. Click "Create dashboard"
5. Click "Add widget"
6. Select "Line" chart
7. Configure metrics:
   - Namespace: `AWS/SES`
   - Metrics: Select `Send`, `Bounce`, `Complaint`, `Delivery`
   - Period: 5 minutes
   - Statistic: Sum
8. Click "Create widget"
9. Add another widget for reputation metrics:
   - Namespace: `AWS/SES`
   - Metrics: `Reputation.BounceRate`, `Reputation.ComplaintRate`
   - Statistic: Average
10. Click "Save dashboard"

> **What you'll see:**
> - Real-time email volume trends
> - Bounce/complaint rate trends (should stay low)
> - Delivery success patterns
> - Performance during peak hours
> 
> **Status Check:** Dashboard shows current SES metrics
> **What's accomplished:** Visual monitoring of email system health
> **Next Step:** Set up automated alerts for critical issues

**Via AWS CLI:**
```bash
# Create CloudWatch dashboard for email monitoring
cat > email-dashboard-config.json << 'EOF'
{
    "widgets": [
        {
            "type": "metric",
            "properties": {
                "metrics": [
                    ["AWS/SES", "Send"],
                    ["AWS/SES", "Bounce"],
                    ["AWS/SES", "Complaint"],
                    ["AWS/SES", "Delivery"]
                ],
                "period": 300,
                "stat": "Sum",
                "region": "ap-southeast-1",
                "title": "Email Delivery Metrics"
            }
        },
        {
            "type": "metric",
            "properties": {
                "metrics": [
                    ["AWS/SES", "Reputation.BounceRate"],
                    ["AWS/SES", "Reputation.ComplaintRate"]
                ],
                "period": 300,
                "stat": "Average",
                "region": "ap-southeast-1",
                "title": "Email Reputation Metrics"
            }
        }
    ]
}
EOF

aws cloudwatch put-dashboard \
    --dashboard-name "EmailNotificationSystem" \
    --dashboard-body file://email-dashboard-config.json
```

---

### Step 2: CloudWatch Alarms

> **Purpose:** Automatically alert you when email metrics indicate problems that need immediate attention.
> 
> **Critical thresholds:**
> - Bounce rate >5%: Risk of reputation damage
> - Complaint rate >0.1%: Risk of account suspension
> - High volume: Potential security issue or runaway process
> 
> **Alert strategy:** Early warning before problems become critical

**Via AWS Console:**
1. **Bounce Rate Alarm:**
   - Go to CloudWatch Console → Alarms
   - Click "Create alarm"
   - Select metric: `AWS/SES` → `Reputation.BounceRate`
   - Statistic: Average
   - Period: 5 minutes
   - Threshold: Greater than 0.05 (5%)
   - Evaluation periods: 2
   - Alarm name: `SES-High-Bounce-Rate`
   - Description: `Alert when email bounce rate exceeds 5%`
   - Click "Create alarm"

> **Why 5% matters:** Email providers start flagging senders with >5% bounce rates as potentially problematic

2. **Complaint Rate Alarm:**
   - Repeat above steps with:
   - Metric: `Reputation.ComplaintRate`
   - Threshold: Greater than 0.001 (0.1%)
   - Alarm name: `SES-High-Complaint-Rate`

> **Why 0.1% matters:** AWS may suspend accounts with >0.1% complaint rates

3. **Daily Volume Alarm:**
   - Metric: `Send`
   - Statistic: Sum
   - Period: 1 day (86400 seconds)
   - Threshold: Greater than 10000
   - Alarm name: `SES-High-Email-Volume`

> **Why volume monitoring:** Detect potential security breaches or application bugs causing email spam

**Via AWS CLI:**
```bash
# Email bounce rate alarm (should be < 5%)
aws cloudwatch put-metric-alarm \
    --alarm-name "SES-High-Bounce-Rate" \
    --alarm-description "Alert when email bounce rate exceeds 5%" \
    --metric-name "Reputation.BounceRate" \
    --namespace "AWS/SES" \
    --statistic "Average" \
    --period 300 \
    --threshold 0.05 \
    --comparison-operator "GreaterThanThreshold" \
    --evaluation-periods 2

# Email complaint rate alarm (should be < 0.1%)
aws cloudwatch put-metric-alarm \
    --alarm-name "SES-High-Complaint-Rate" \
    --alarm-description "Alert when email complaint rate exceeds 0.1%" \
    --metric-name "Reputation.ComplaintRate" \
    --namespace "AWS/SES" \
    --statistic "Average" \
    --period 300 \
    --threshold 0.001 \
    --comparison-operator "GreaterThanThreshold" \
    --evaluation-periods 2

# Daily email volume alarm
aws cloudwatch put-metric-alarm \
    --alarm-name "SES-High-Email-Volume" \
    --alarm-description "Alert when daily email volume is unusually high" \
    --metric-name "Send" \
    --namespace "AWS/SES" \
    --statistic "Sum" \
    --period 86400 \
    --threshold 10000 \
    --comparison-operator "GreaterThanThreshold" \
    --evaluation-periods 1
```

> **Status Check:** Alarms show "OK" state in CloudWatch console
> **What's accomplished:** Automated monitoring for critical email reputation metrics
> **Next Step:** Configure application-level logging for detailed troubleshooting

### Step 3: Application Logging
```python
import logging
import json
from datetime import datetime

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('email_notification_service.log'),
        logging.StreamHandler()
    ]
)

class EmailNotificationLogger:
    def __init__(self):
        self.logger = logging.getLogger('EmailNotificationService')
    
    def log_email_attempt(self, recipient, subject, success, message_id=None, error=None):
        # Mask email for privacy (show only domain)
        masked_email = self._mask_email(recipient)
        
        log_data = {
            'timestamp': datetime.now().isoformat(),
            'recipient': masked_email,
            'subject': subject,
            'success': success,
            'message_id': message_id,
            'error': error
        }
        
        if success:
            self.logger.info(f"Email sent successfully: {json.dumps(log_data)}")
        else:
            self.logger.error(f"Email sending failed: {json.dumps(log_data)}")
    
    def _mask_email(self, email):
        """Mask email for privacy: user@domain.com -> u***@domain.com"""
        if '@' in email:
            local, domain = email.split('@', 1)
            if len(local) > 1:
                masked_local = local[0] + '*' * (len(local) - 1)
                return f"{masked_local}@{domain}"
        return email
    
    def log_daily_summary(self, total_sent, total_failed, bounce_count, complaint_count):
        summary = {
            'date': datetime.now().date().isoformat(),
            'total_sent': total_sent,
            'total_failed': total_failed,
            'success_rate': (total_sent / (total_sent + total_failed)) * 100 if (total_sent + total_failed) > 0 else 0,
            'bounce_count': bounce_count,
            'complaint_count': complaint_count
        }
        
        self.logger.info(f"Daily email summary: {json.dumps(summary)}")
```

### Step 4: SES Event Publishing (Optional)
```bash
# Create SNS topic for SES events
aws sns create-topic --name ses-events

# Configure SES to publish bounce events
aws ses put-configuration-set-event-destination \
    --configuration-set-name default \
    --event-destination Name=bounce-events,Enabled=true,MatchingEventTypes=bounce,SNSDestination={TopicARN=arn:aws:sns:ap-southeast-1:ACCOUNT-ID:ses-events}

# Configure SES to publish complaint events
aws ses put-configuration-set-event-destination \
    --configuration-set-name default \
    --event-destination Name=complaint-events,Enabled=true,MatchingEventTypes=complaint,SNSDestination={TopicARN=arn:aws:sns:ap-southeast-1:ACCOUNT-ID:ses-events}
```

---

## Security Considerations

### 1. Data Protection
- **PII Handling**: Never log full email addresses (use masking)
- **Message Content**: Avoid logging sensitive email content
- **Encryption**: Use TLS for all API communications (enabled by default)
- **Email Headers**: Implement proper email headers for security

### 2. Access Control
```bash
# Principle of least privilege IAM policy for email-only
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "ses:SendEmail",
                "ses:SendRawEmail"
            ],
            "Resource": "arn:aws:ses:ap-southeast-1:ACCOUNT-ID:identity/marina.gov.ph"
        },
        {
            "Effect": "Allow",
            "Action": [
                "ses:GetSendQuota",
                "ses:GetSendStatistics"
            ],
            "Resource": "*"
        }
    ]
}
```

### 3. Rate Limiting & Abuse Prevention
```python
from functools import wraps
import time
from collections import defaultdict

class EmailRateLimiter:
    def __init__(self):
        self.requests = defaultdict(list)
    
    def is_allowed(self, email, max_emails=10, time_window=60):
        now = time.time()
        # Clean old requests
        self.requests[email] = [
            req_time for req_time in self.requests[email]
            if now - req_time < time_window
        ]
        
        if len(self.requests[email]) >= max_emails:
            return False
        
        self.requests[email].append(now)
        return True

def email_rate_limit(max_emails=10, time_window=60):
    limiter = EmailRateLimiter()
    
    def decorator(func):
        @wraps(func)
        def wrapper(*args, **kwargs):
            email = kwargs.get('to_email') or args[1] if len(args) > 1 else None
            
            if email and not limiter.is_allowed(email, max_emails, time_window):
                raise Exception(f"Rate limit exceeded for {email}")
            
            return func(*args, **kwargs)
        return wrapper
    return decorator
```

### 4. Input Validation & Sanitization
```python
import re
import html
from typing import Optional

class EmailInputValidator:
    @staticmethod
    def validate_email(email: str) -> bool:
        """Validate email format"""
        pattern = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
        return re.match(pattern, email) is not None
    
    @staticmethod
    def sanitize_subject(subject: str) -> str:
        """Sanitize email subject"""
        # Remove potential header injection
        subject = re.sub(r'[\r\n]', '', subject)
        # Limit length
        subject = subject[:200]
        # HTML escape
        return html.escape(subject)
    
    @staticmethod
    def sanitize_content(content: str, is_html: bool = False) -> str:
        """Sanitize email content"""
        if not is_html:
            # For plain text, just escape HTML
            return html.escape(content)
        else:
            # For HTML, implement basic sanitization
            # Remove script tags and dangerous attributes
            content = re.sub(r'<script[^>]*>.*?</script>', '', content, flags=re.IGNORECASE | re.DOTALL)
            content = re.sub(r'on\w+\s*=\s*["\'][^"\']*["\']', '', content, flags=re.IGNORECASE)
            return content
    
    @staticmethod
    def validate_sender_email(email: str, allowed_domains: list) -> bool:
        """Validate sender email against allowed domains"""
        if not EmailInputValidator.validate_email(email):
            return False
        
        domain = email.split('@')[1].lower()
        return domain in [d.lower() for d in allowed_domains]

# Usage in EmailNotificationService
class SecureEmailNotificationService(EmailNotificationService):
    def __init__(self, region='ap-southeast-1', allowed_sender_domains=None):
        super().__init__(region)
        self.allowed_sender_domains = allowed_sender_domains or ['marina.gov.ph']
        self.validator = EmailInputValidator()
    
    @email_rate_limit(max_emails=20, time_window=300)  # 20 emails per 5 minutes
    def send_email(self, to_email: str, subject: str, body_text: str, 
                   body_html: str = None, from_email: str = 'noreply@marina.gov.ph',
                   reply_to: str = None) -> Dict[str, Any]:
        
        # Validate inputs
        if not self.validator.validate_email(to_email):
            return {'success': False, 'error': 'Invalid recipient email', 'to_email': to_email}
        
        if not self.validator.validate_sender_email(from_email, self.allowed_sender_domains):
            return {'success': False, 'error': 'Invalid sender email', 'to_email': to_email}
        
        # Sanitize inputs
        subject = self.validator.sanitize_subject(subject)
        body_text = self.validator.sanitize_content(body_text, is_html=False)
        
        if body_html:
            body_html = self.validator.sanitize_content(body_html, is_html=True)
        
        # Call parent method with sanitized inputs
        return super().send_email(to_email, subject, body_text, body_html, from_email, reply_to)
```

### 5. Email Security Headers
```python
def send_secure_email(self, to_email: str, subject: str, body_text: str, body_html: str = None):
    """Send email with security headers"""
    
    # Create raw email with security headers
    from email.mime.multipart import MIMEMultipart
    from email.mime.text import MIMEText
    
    msg = MIMEMultipart('alternative')
    msg['Subject'] = subject
    msg['From'] = 'noreply@marina.gov.ph'
    msg['To'] = to_email
    
    # Security headers
    msg['X-Mailer'] = 'Marina Email Service'
    msg['X-Priority'] = '3'
    msg['X-MSMail-Priority'] = 'Normal'
    
    # Add text part
    text_part = MIMEText(body_text, 'plain', 'utf-8')
    msg.attach(text_part)
    
    # Add HTML part if provided
    if body_html:
        html_part = MIMEText(body_html, 'html', 'utf-8')
        msg.attach(html_part)
    
    try:
        response = self.ses.send_raw_email(
            Source='noreply@marina.gov.ph',
            Destinations=[to_email],
            RawMessage={'Data': msg.as_string()}
        )
        
        return {
            'success': True,
            'message_id': response['MessageId'],
            'to_email': to_email
        }
    except Exception as e:
        return {
            'success': False,
            'error': str(e),
            'to_email': to_email
        }
```

### 6. Bounce and Complaint Handling
```python
class EmailReputationManager:
    def __init__(self):
        self.bounce_list = set()
        self.complaint_list = set()
    
    def handle_bounce(self, email: str, bounce_type: str):
        """Handle email bounce"""
        if bounce_type == 'Permanent':
            self.bounce_list.add(email.lower())
            self.logger.warning(f"Added {email} to bounce list (permanent bounce)")
    
    def handle_complaint(self, email: str):
        """Handle spam complaint"""
        self.complaint_list.add(email.lower())
        self.logger.warning(f"Added {email} to complaint list")
    
    def is_email_blocked(self, email: str) -> bool:
        """Check if email should be blocked"""
        return email.lower() in self.bounce_list or email.lower() in self.complaint_list
    
    def get_reputation_stats(self) -> dict:
        """Get reputation statistics"""
        return {
            'bounced_emails': len(self.bounce_list),
            'complaint_emails': len(self.complaint_list)
        }
```

---

## Troubleshooting

### Common Email Issues & Solutions

#### Email Delivery Issues
```bash
# Check SES sending statistics
aws ses get-send-statistics

# Check current sending quota
aws ses get-send-quota

# Check account sending status
aws ses get-account-sending-enabled
```

**Common Issues:**
1. **Sandbox Mode**: Account limited to verified emails only
2. **High Bounce Rate**: >5% bounce rate can affect reputation
3. **High Complaint Rate**: >0.1% complaint rate can affect reputation
4. **Sending Quota Exceeded**: Daily/per-second limits reached
5. **Unverified Domain/Email**: Sender not verified

**Solutions:**
- **Sandbox Mode**: Request production access via AWS Console
- **High Bounce/Complaint**: Implement proper list hygiene and bounce handling
- **Quota Issues**: Request limit increase via AWS Support
- **Verification**: Complete domain/email verification process

#### Specific Error Messages

**Error: "Email address not verified"**
```bash
# Check verification status
aws ses get-identity-verification-attributes --identities yourdomain.com

# Verify email address
aws ses verify-email-identity --email-address noreply@marina.gov.ph
```

**Error: "Daily sending quota exceeded"**
```bash
# Check current quota
aws ses get-send-quota

# Monitor usage
aws ses get-send-statistics
```

**Error: "Account is in sandbox mode"**
- Go to AWS SES Console → Account dashboard
- Click "Request production access"
- Fill out the form with use case details

#### Delivery Issues
```python
# Implement retry logic for transient failures
import time
import random
from botocore.exceptions import ClientError

def send_email_with_retry(email_service, max_retries=3, backoff_factor=2):
    def retry_wrapper(func):
        def wrapper(*args, **kwargs):
            for attempt in range(max_retries):
                try:
                    result = func(*args, **kwargs)
                    if result['success']:
                        return result
                    
                    # If it's a permanent failure, don't retry
                    if 'not verified' in result.get('error', '').lower():
                        return result
                        
                except ClientError as e:
                    error_code = e.response['Error']['Code']
                    
                    # Don't retry for these errors
                    if error_code in ['MessageRejected', 'InvalidParameterValue']:
                        raise e
                    
                    if attempt == max_retries - 1:
                        raise e
                
                # Exponential backoff with jitter
                delay = backoff_factor ** attempt + random.uniform(0, 1)
                time.sleep(delay)
            
            return {'success': False, 'error': 'Max retries exceeded'}
        return wrapper
    return retry_wrapper

# Usage
@send_email_with_retry(email_service)
def send_important_email(to_email, subject, body):
    return email_service.send_email(to_email, subject, body)
```

#### Monitoring Commands
```bash
# Check SES metrics for last 24 hours
aws cloudwatch get-metric-statistics \
    --namespace AWS/SES \
    --metric-name Send \
    --start-time $(date -u -d '24 hours ago' +%Y-%m-%dT%H:%M:%S) \
    --end-time $(date -u +%Y-%m-%dT%H:%M:%S) \
    --period 3600 \
    --statistics Sum

# Check bounce rate
aws cloudwatch get-metric-statistics \
    --namespace AWS/SES \
    --metric-name Bounce \
    --start-time $(date -u -d '24 hours ago' +%Y-%m-%dT%H:%M:%S) \
    --end-time $(date -u +%Y-%m-%dT%H:%M:%S) \
    --period 3600 \
    --statistics Sum

# Check complaint rate
aws cloudwatch get-metric-statistics \
    --namespace AWS/SES \
    --metric-name Complaint \
    --start-time $(date -u -d '24 hours ago' +%Y-%m-%dT%H:%M:%S) \
    --end-time $(date -u +%Y-%m-%dT%H:%M:%S) \
    --period 3600 \
    --statistics Sum
```

#### Email Deliverability Issues

**Low Inbox Placement:**
1. **Setup DKIM**: Authenticate your domain
2. **SPF Records**: Add proper SPF records
3. **DMARC Policy**: Implement DMARC
4. **Content Quality**: Avoid spam trigger words
5. **List Hygiene**: Remove bounced/inactive emails

```bash
# Setup DKIM
aws ses put-identity-dkim-attributes --identity yourdomain.com --dkim-enabled

# Get DKIM tokens for DNS
aws ses get-identity-dkim-attributes --identities yourdomain.com
```

**DNS Records to Add:**
```
# SPF Record
TXT: "v=spf1 include:amazonses.com ~all"

# DKIM Records (get tokens from AWS)
CNAME: token1._domainkey.marina.gov.ph → token1.dkim.amazonses.com
CNAME: token2._domainkey.marina.gov.ph → token2.dkim.amazonses.com
CNAME: token3._domainkey.marina.gov.ph → token3.dkim.amazonses.com

# DMARC Record
TXT: "v=DMARC1; p=quarantine; rua=mailto:dmarc@marina.gov.ph"
```

#### Performance Issues
```python
# Optimize email sending performance
import asyncio
import aiohttp
from concurrent.futures import ThreadPoolExecutor

class OptimizedEmailService:
    def __init__(self, max_workers=10):
        self.email_service = EmailNotificationService()
        self.executor = ThreadPoolExecutor(max_workers=max_workers)
    
    async def send_bulk_emails(self, email_list):
        """Send emails in parallel with rate limiting"""
        loop = asyncio.get_event_loop()
        
        # Batch emails to respect SES rate limits
        batch_size = 14  # SES default: 14 emails per second
        results = []
        
        for i in range(0, len(email_list), batch_size):
            batch = email_list[i:i + batch_size]
            
            # Send batch in parallel
            tasks = [
                loop.run_in_executor(
                    self.executor,
                    self.email_service.send_email,
                    email['to'],
                    email['subject'],
                    email['body']
                )
                for email in batch
            ]
            
            batch_results = await asyncio.gather(*tasks)
            results.extend(batch_results)
            
            # Wait 1 second between batches to respect rate limits
            if i + batch_size < len(email_list):
                await asyncio.sleep(1)
        
        return results
```

### Debugging Checklist

**Before Sending:**
- [ ] Sender email/domain verified
- [ ] Recipient email format valid
- [ ] Account not in sandbox mode (for production)
- [ ] Within sending quota limits
- [ ] Proper IAM permissions

**After Sending Issues:**
- [ ] Check CloudWatch metrics
- [ ] Review bounce/complaint rates
- [ ] Verify DNS records (SPF, DKIM, DMARC)
- [ ] Check email content for spam triggers
- [ ] Review application logs

**Performance Issues:**
- [ ] Implement proper rate limiting
- [ ] Use batch sending for bulk emails
- [ ] Monitor SES quotas and limits
- [ ] Optimize email templates
- [ ] Use connection pooling

---

## Production Deployment Checklist

### Pre-Deployment
- [ ] AWS account configured with appropriate permissions
- [ ] SES production access approved
- [ ] Domain/email verification completed
- [ ] DKIM configured for email authentication
- [ ] SPF and DMARC records configured
- [ ] Rate limiting implemented
- [ ] Input validation and sanitization in place
- [ ] Logging and monitoring configured
- [ ] Error handling and retry logic implemented
- [ ] Bounce and complaint handling setup

### Post-Deployment
- [ ] Test email delivery to various providers (Gmail, Yahoo, Outlook)
- [ ] Test email deliverability and inbox placement
- [ ] Monitor bounce and complaint rates
- [ ] Set up CloudWatch alarms
- [ ] Verify logging is working correctly
- [ ] Test rate limiting functionality
- [ ] Conduct load testing with realistic volumes
- [ ] Test all email templates (OTP, alerts, marketing)
- [ ] Verify email security headers

### Ongoing Maintenance
- [ ] Monitor delivery rates and reputation metrics
- [ ] Review bounce/complaint handling weekly
- [ ] Regular security reviews
- [ ] Performance optimization
- [ ] Update email templates as needed
- [ ] Monitor costs and optimize usage
- [ ] Backup configuration and templates

---

## Cost Optimization for Email-Only System

### SES Pricing (Philippines Market)
```
Free Tier:
- First 62,000 emails/month: FREE (if sent from EC2)
- First 1,000 emails/month: FREE (if sent from outside AWS)

Paid Tier:
- $0.10 per 1,000 emails after free tier
- No additional charges for attachments up to 10MB

Example Monthly Costs:
- 5,000 emails: FREE (under free tier)
- 50,000 emails: FREE (if from EC2) or $4.90 (if external)
- 100,000 emails: $3.80 (from EC2) or $9.90 (external)
- 500,000 emails: $43.80 (from EC2) or $49.90 (external)
```

### Cost Optimization Strategies

**1. Use EC2 for Sending (if applicable)**
- Deploy application on EC2 to get 62,000 free emails/month
- Significant savings for high-volume applications

**2. Email Template Optimization**
```python
# Optimize email content to reduce size
class OptimizedEmailTemplates:
    @staticmethod
    def create_minimal_otp_template(otp_code, company_name):
        """Minimal OTP template to reduce size"""
        subject = f"{company_name} Code: {otp_code}"
        
        text = f"Your code: {otp_code}\nValid: 5 min\nDo not share."
        
        html = f"""<div style="font-family:Arial">
        <h2>Code: {otp_code}</h2>
        <p>Valid: 5 minutes</p></div>"""
        
        return subject, text, html
    
    @staticmethod
    def create_efficient_alert_template(alert_data):
        """Efficient alert template"""
        subject = f"Alert: {alert_data['type']}"
        
        text = f"{alert_data['type']}: {alert_data['amount']}\nRef: {alert_data['ref']}"
        
        html = f"""<div><b>{alert_data['type']}</b><br>
        {alert_data['amount']}<br>Ref: {alert_data['ref']}</div>"""
        
        return subject, text, html
```

**3. Smart Email Routing**
```python
class SmartEmailRouter:
    def __init__(self):
        self.priority_queue = []
        self.batch_queue = []
    
    def route_email(self, email_data):
        """Route emails based on priority and batching"""
        if email_data['type'] in ['otp', 'security_alert']:
            # Send immediately for critical emails
            return self.send_immediate(email_data)
        else:
            # Batch non-critical emails
            self.batch_queue.append(email_data)
            if len(self.batch_queue) >= 10:
                return self.send_batch()
    
    def send_batch(self):
        """Send emails in batches to optimize costs"""
        # Process batch queue
        results = []
        for email in self.batch_queue:
            result = self.send_email(email)
            results.append(result)
        
        self.batch_queue.clear()
        return results
```

**4. Monitoring and Alerts for Cost Control**
```bash
# Set up billing alarm for SES costs
aws cloudwatch put-metric-alarm \
    --alarm-name "SES-Monthly-Cost-Alert" \
    --alarm-description "Alert when SES costs exceed budget" \
    --metric-name "EstimatedCharges" \
    --namespace "AWS/Billing" \
    --statistic "Maximum" \
    --period 86400 \
    --threshold 50.00 \
    --comparison-operator "GreaterThanThreshold" \
    --evaluation-periods 1 \
    --dimensions Name=Currency,Value=USD Name=ServiceName,Value=AmazonSES
```

### Monthly Cost Examples for Different Use Cases

**Startup (Low Volume)**
```
- 5,000 OTP emails
- 2,000 transaction alerts  
- 1,000 welcome emails
Total: 8,000 emails/month
Cost: FREE (under free tier)
```

**Growing Business (Medium Volume)**
```
- 20,000 OTP emails
- 15,000 transaction alerts
- 5,000 marketing emails
- 10,000 welcome/notification emails
Total: 50,000 emails/month
Cost: FREE (if from EC2) or $4.90/month
```

**Enterprise (High Volume)**
```
- 100,000 OTP emails
- 80,000 transaction alerts
- 50,000 marketing emails
- 20,000 other notifications
Total: 250,000 emails/month
Cost: $18.80/month (from EC2) or $24.90/month
```

### Additional Cost Considerations

**CloudWatch Monitoring (Optional)**
```
- Basic metrics: FREE
- Custom metrics: $0.30 per metric per month
- Log ingestion: $0.50 per GB
- Estimated monitoring cost: $5-10/month
```

**Total Monthly Cost Estimate**
```
For 100,000 emails/month:
- SES: $3.80-9.90
- CloudWatch: $5-10
- Total: $8.80-19.90/month
```

---

This updated guide now focuses exclusively on email notifications using Amazon SES, which will significantly reduce your costs and complexity while still providing a robust, production-ready notification system for the Philippines market.

## 🎯 Implementation Progress Tracker

Use this checklist to track your setup progress:

### Phase 1: AWS SES Foundation
- [ ] **Domain Verification**: marina.gov.ph verified in SES
- [ ] **DKIM Setup**: 3 CNAME records added to DNS
- [ ] **Production Access**: Request submitted and approved
- [ ] **Bounce Handling**: SNS topic configured for notifications

> **Milestone:** Basic SES functionality ready

### Phase 2: Security & Access
- [ ] **IAM Policy**: EmailNotificationServicePolicy created
- [ ] **Service User**: email-notification-service-user created
- [ ] **API Credentials**: Access keys generated and stored securely
- [ ] **Permissions**: Policy attached to user

> **Milestone:** Secure API access configured

### Phase 3: Application Integration
- [ ] **Code Implementation**: Email service class integrated
- [ ] **Template Testing**: All email templates working
- [ ] **Error Handling**: Retry logic and validation implemented
- [ ] **Rate Limiting**: Protection against abuse configured

> **Milestone:** Application ready for production

### Phase 4: Monitoring & Operations
- [ ] **CloudWatch Dashboard**: Email metrics visible
- [ ] **Automated Alerts**: Bounce/complaint alarms configured
- [ ] **Application Logging**: Detailed logs for troubleshooting
- [ ] **Load Testing**: System tested under expected volume

> **Milestone:** Production monitoring active

### Phase 5: Production Deployment
- [ ] **DNS Records**: All records propagated and verified
- [ ] **End-to-End Testing**: Full user journey tested
- [ ] **Documentation**: Operational procedures documented
- [ ] **Team Training**: Staff trained on monitoring and troubleshooting

> **🚀 Milestone:** Production-ready email notification system**

## 📊 Expected Outcomes

**After completing this guide, you will have:**

✅ **Reliable Email Delivery**: 99%+ delivery rate to major email providers
✅ **Cost-Effective Solution**: ~$5-20/month for 100k emails vs $450+ for SMS
✅ **Government-Grade Security**: Proper authentication and access controls
✅ **Professional Appearance**: DKIM-signed emails from marina.gov.ph
✅ **Comprehensive Monitoring**: Real-time visibility into email performance
✅ **Scalable Architecture**: Handles growth from hundreds to millions of emails
✅ **Audit Compliance**: Detailed logging for government requirements

## 🔄 Next Steps After Implementation

1. **Monitor Daily**: Check CloudWatch dashboard for delivery metrics
2. **Weekly Reviews**: Analyze bounce/complaint trends
3. **Monthly Optimization**: Review costs and performance
4. **Quarterly Updates**: Update templates and security policies
5. **Annual Audits**: Full security and compliance review

## 📞 Support Resources

**AWS Support**: For technical issues with SES service
**Documentation**: AWS SES Developer Guide
**Community**: AWS Forums and Stack Overflow
**Emergency**: CloudWatch alarms will notify of critical issues

Your Marina email notification system is now ready to serve citizens reliably and cost-effectively!
