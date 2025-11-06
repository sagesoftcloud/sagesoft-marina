# AWS Multi-Channel Notification System Guide
## Production-Ready Setup for Philippines Market

### Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Prerequisites](#prerequisites)
4. [AWS SNS Setup (SMS)](#aws-sns-setup-sms)
5. [AWS SES Setup (Email)](#aws-ses-setup-email)
6. [IAM Configuration](#iam-configuration)
7. [Implementation Examples](#implementation-examples)
8. [Testing Procedures](#testing-procedures)
9. [Monitoring & Logging](#monitoring--logging)
10. [Security Considerations](#security-considerations)
11. [Troubleshooting](#troubleshooting)

---

## Overview

This guide provides step-by-step instructions to implement a production-ready multi-channel notification system using AWS SNS (SMS) and AWS SES (Email) for the Philippines market.

**Supported Use Cases:**
- OTP Authentication
- Transaction Alerts
- Marketing Messages
- System Notifications
- Email Verification

---

## Architecture

```
[Web App/Mobile App] → [API Service] → [Notification Router]
                                            ↓
                                    [Decision Logic]
                                    ↙            ↘
                            [Amazon SNS]    [Amazon SES]
                                ↓              ↓
                          [SMS to Phone]  [Email to Inbox]
```

---

## Prerequisites

### AWS Account Requirements
- ✅ AWS Account (Already available as AWS Partner)
- ✅ AWS CLI installed and configured
- ✅ Appropriate billing setup

### Philippines-Specific Considerations
- SMS delivery to Philippine mobile networks (+63 country code)
- Email deliverability for .ph domains
- Local compliance requirements

---

## AWS SNS Setup (SMS)

### Step 1: Configure SNS for Philippines

#### Using AWS Console:
1. Navigate to Amazon SNS Console
2. Go to "Text messaging (SMS)" → "Mobile text messaging (SMS)"
3. Configure SMS preferences:

#### Using AWS CLI:
```bash
# Set SMS preferences for Philippines
aws sns set-sms-attributes \
    --attributes \
    DefaultSMSType=Transactional,\
    DefaultSenderID=YourBrand,\
    MonthlySpendLimit=100,\
    DeliveryStatusLogging=true,\
    DeliveryStatusSuccessSamplingRate=100

# Verify current settings
aws sns get-sms-attributes
```

### Step 2: Request SMS Spending Limit Increase
```bash
# Check current spending limit
aws sns get-sms-attributes --attribute-names MonthlySpendLimit

# Note: For production, request limit increase via AWS Support
# Default limit is $1.00 USD per month
```

### Step 3: Test SMS Delivery to Philippines
```bash
# Test SMS to Philippine number
aws sns publish \
    --phone-number "+639171234567" \
    --message "Test message from AWS SNS"
```

---

## AWS SES Setup (Email)

### Step 1: Verify Email Domain/Address

#### Verify Single Email Address:
```bash
# Verify sender email
aws ses verify-email-identity \
    --email-address noreply@yourdomain.com

# Check verification status
aws ses get-identity-verification-attributes \
    --identities noreply@yourdomain.com
```

#### Verify Domain (Recommended for Production):
```bash
# Verify domain
aws ses verify-domain-identity \
    --domain yourdomain.com

# Get domain verification token
aws ses get-identity-verification-attributes \
    --identities yourdomain.com
```

### Step 2: Configure DKIM (Domain Authentication)
```bash
# Enable DKIM for domain
aws ses put-identity-dkim-attributes \
    --identity yourdomain.com \
    --dkim-enabled

# Get DKIM tokens for DNS setup
aws ses get-identity-dkim-attributes \
    --identities yourdomain.com
```

### Step 3: Request Production Access
```bash
# Check current sending quota (sandbox = 200 emails/day)
aws ses get-send-quota

# Note: Submit production access request via AWS Console
# Go to SES → Account dashboard → Request production access
```

### Step 4: Configure Bounce and Complaint Handling
```bash
# Create SNS topic for bounce notifications
aws sns create-topic --name ses-bounces

# Set bounce notifications
aws ses put-identity-notification-attributes \
    --identity yourdomain.com \
    --notification-type Bounce \
    --sns-topic arn:aws:sns:ap-southeast-1:ACCOUNT-ID:ses-bounces
```

---

## IAM Configuration

### Step 1: Create IAM Policy
```bash
# Create policy file
cat > notification-policy.json << 'EOF'
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "sns:Publish",
                "sns:GetSMSAttributes",
                "sns:SetSMSAttributes"
            ],
            "Resource": "*"
        },
        {
            "Effect": "Allow",
            "Action": [
                "ses:SendEmail",
                "ses:SendRawEmail",
                "ses:GetSendQuota",
                "ses:GetSendStatistics"
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
    --policy-name NotificationServicePolicy \
    --policy-document file://notification-policy.json
```

### Step 2: Create IAM Role/User
```bash
# Create IAM user for application
aws iam create-user --user-name notification-service-user

# Attach policy to user
aws iam attach-user-policy \
    --user-name notification-service-user \
    --policy-arn arn:aws:iam::ACCOUNT-ID:policy/NotificationServicePolicy

# Create access keys
aws iam create-access-key --user-name notification-service-user
```

---

## Implementation Examples

### Python Implementation
```python
import boto3
import json
import logging
from datetime import datetime
from typing import Dict, Any

class NotificationService:
    def __init__(self, region='ap-southeast-1'):
        self.sns = boto3.client('sns', region_name=region)
        self.ses = boto3.client('ses', region_name=region)
        self.logger = logging.getLogger(__name__)
        
    def send_sms(self, phone_number: str, message: str, message_type: str = 'Transactional') -> Dict[str, Any]:
        """Send SMS via Amazon SNS"""
        try:
            # Ensure Philippine format (+63)
            if not phone_number.startswith('+63'):
                phone_number = f"+63{phone_number.lstrip('0')}"
            
            response = self.sns.publish(
                PhoneNumber=phone_number,
                Message=message,
                MessageAttributes={
                    'AWS.SNS.SMS.SMSType': {
                        'DataType': 'String',
                        'StringValue': message_type
                    }
                }
            )
            
            self.logger.info(f"SMS sent successfully: {response['MessageId']}")
            return {
                'success': True,
                'message_id': response['MessageId'],
                'phone_number': phone_number
            }
            
        except Exception as e:
            self.logger.error(f"SMS sending failed: {str(e)}")
            return {
                'success': False,
                'error': str(e),
                'phone_number': phone_number
            }
    
    def send_email(self, to_email: str, subject: str, body_text: str, 
                   body_html: str = None, from_email: str = 'noreply@yourdomain.com') -> Dict[str, Any]:
        """Send Email via Amazon SES"""
        try:
            message = {
                'Subject': {'Data': subject, 'Charset': 'UTF-8'},
                'Body': {'Text': {'Data': body_text, 'Charset': 'UTF-8'}}
            }
            
            if body_html:
                message['Body']['Html'] = {'Data': body_html, 'Charset': 'UTF-8'}
            
            response = self.ses.send_email(
                Source=from_email,
                Destination={'ToAddresses': [to_email]},
                Message=message
            )
            
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
    
    def send_otp(self, contact: str, otp_code: str, method: str = 'sms') -> Dict[str, Any]:
        """Send OTP via specified method"""
        if method == 'sms':
            message = f"Your OTP code is: {otp_code}. Valid for 5 minutes. Do not share this code."
            return self.send_sms(contact, message)
        elif method == 'email':
            subject = "Your OTP Code"
            body = f"Your OTP code is: {otp_code}\n\nThis code is valid for 5 minutes.\nDo not share this code with anyone."
            return self.send_email(contact, subject, body)
    
    def send_notification(self, user_preferences: Dict[str, Any], 
                         notification_data: Dict[str, Any]) -> Dict[str, Any]:
        """Route notification based on user preferences"""
        results = []
        
        if user_preferences.get('sms_enabled') and user_preferences.get('phone'):
            sms_result = self.send_sms(
                user_preferences['phone'],
                notification_data['sms_message']
            )
            results.append(('sms', sms_result))
        
        if user_preferences.get('email_enabled') and user_preferences.get('email'):
            email_result = self.send_email(
                user_preferences['email'],
                notification_data['email_subject'],
                notification_data['email_body']
            )
            results.append(('email', email_result))
        
        return {
            'timestamp': datetime.now().isoformat(),
            'results': results
        }

# Usage Examples
if __name__ == "__main__":
    # Initialize service
    notifier = NotificationService()
    
    # Send OTP via SMS
    otp_result = notifier.send_otp("+639171234567", "123456", "sms")
    print(f"OTP SMS Result: {otp_result}")
    
    # Send OTP via Email
    email_otp_result = notifier.send_otp("user@example.com", "123456", "email")
    print(f"OTP Email Result: {email_otp_result}")
    
    # Send multi-channel notification
    user_prefs = {
        'sms_enabled': True,
        'email_enabled': True,
        'phone': '+639171234567',
        'email': 'user@example.com'
    }
    
    notification = {
        'sms_message': 'Transaction completed: PHP 1,000.00',
        'email_subject': 'Transaction Alert',
        'email_body': 'Your transaction of PHP 1,000.00 has been completed successfully.'
    }
    
    multi_result = notifier.send_notification(user_prefs, notification)
    print(f"Multi-channel Result: {multi_result}")
```

### Node.js Implementation
```javascript
const AWS = require('aws-sdk');

class NotificationService {
    constructor(region = 'ap-southeast-1') {
        this.sns = new AWS.SNS({ region });
        this.ses = new AWS.SES({ region });
    }
    
    async sendSMS(phoneNumber, message, messageType = 'Transactional') {
        try {
            // Ensure Philippine format
            if (!phoneNumber.startsWith('+63')) {
                phoneNumber = `+63${phoneNumber.replace(/^0/, '')}`;
            }
            
            const params = {
                PhoneNumber: phoneNumber,
                Message: message,
                MessageAttributes: {
                    'AWS.SNS.SMS.SMSType': {
                        DataType: 'String',
                        StringValue: messageType
                    }
                }
            };
            
            const result = await this.sns.publish(params).promise();
            return {
                success: true,
                messageId: result.MessageId,
                phoneNumber
            };
        } catch (error) {
            return {
                success: false,
                error: error.message,
                phoneNumber
            };
        }
    }
    
    async sendEmail(toEmail, subject, bodyText, bodyHtml = null, fromEmail = 'noreply@yourdomain.com') {
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
}

module.exports = NotificationService;
```

---

## Testing Procedures

### Step 1: SMS Testing
```bash
# Test SMS delivery to Philippine numbers
aws sns publish \
    --phone-number "+639171234567" \
    --message "Test OTP: 123456. Valid for 5 minutes."

# Test with message attributes
aws sns publish \
    --phone-number "+639171234567" \
    --message "Marketing: Special offer available!" \
    --message-attributes '{"AWS.SNS.SMS.SMSType":{"DataType":"String","StringValue":"Promotional"}}'
```

### Step 2: Email Testing
```bash
# Test basic email
aws ses send-email \
    --source "noreply@yourdomain.com" \
    --destination "ToAddresses=test@example.com" \
    --message "Subject={Data=Test Email},Body={Text={Data=This is a test email from AWS SES}}"

# Test HTML email
aws ses send-email \
    --source "noreply@yourdomain.com" \
    --destination "ToAddresses=test@example.com" \
    --message file://test-email.json
```

### Step 3: Load Testing Script
```python
import asyncio
import time
from concurrent.futures import ThreadPoolExecutor
from notification_service import NotificationService

async def load_test_notifications():
    notifier = NotificationService()
    
    # Test parameters
    test_phone = "+639171234567"
    test_email = "test@yourdomain.com"
    num_messages = 100
    
    start_time = time.time()
    
    # SMS Load Test
    with ThreadPoolExecutor(max_workers=10) as executor:
        sms_futures = [
            executor.submit(notifier.send_sms, test_phone, f"Load test SMS {i}")
            for i in range(num_messages)
        ]
        
        sms_results = [future.result() for future in sms_futures]
    
    # Email Load Test
    with ThreadPoolExecutor(max_workers=10) as executor:
        email_futures = [
            executor.submit(notifier.send_email, test_email, f"Load Test {i}", f"Load test email {i}")
            for i in range(num_messages)
        ]
        
        email_results = [future.result() for future in email_futures]
    
    end_time = time.time()
    
    # Results
    sms_success = sum(1 for r in sms_results if r['success'])
    email_success = sum(1 for r in email_results if r['success'])
    
    print(f"Load Test Results:")
    print(f"Duration: {end_time - start_time:.2f} seconds")
    print(f"SMS Success Rate: {sms_success}/{num_messages} ({sms_success/num_messages*100:.1f}%)")
    print(f"Email Success Rate: {email_success}/{num_messages} ({email_success/num_messages*100:.1f}%)")

if __name__ == "__main__":
    asyncio.run(load_test_notifications())
```

---

## Monitoring & Logging

### Step 1: CloudWatch Metrics Setup
```bash
# Create CloudWatch dashboard
aws cloudwatch put-dashboard \
    --dashboard-name "NotificationSystem" \
    --dashboard-body file://dashboard-config.json
```

### Step 2: CloudWatch Dashboard Configuration
```json
{
    "widgets": [
        {
            "type": "metric",
            "properties": {
                "metrics": [
                    ["AWS/SNS", "NumberOfMessagesSent"],
                    ["AWS/SNS", "NumberOfMessagesPublished"],
                    ["AWS/SES", "Send"],
                    ["AWS/SES", "Bounce"],
                    ["AWS/SES", "Complaint"]
                ],
                "period": 300,
                "stat": "Sum",
                "region": "ap-southeast-1",
                "title": "Notification Metrics"
            }
        }
    ]
}
```

### Step 3: CloudWatch Alarms
```bash
# SMS failure alarm
aws cloudwatch put-metric-alarm \
    --alarm-name "SNS-SMS-Failures" \
    --alarm-description "Alert when SMS failures exceed threshold" \
    --metric-name "NumberOfMessagesFailed" \
    --namespace "AWS/SNS" \
    --statistic "Sum" \
    --period 300 \
    --threshold 10 \
    --comparison-operator "GreaterThanThreshold" \
    --evaluation-periods 2

# Email bounce alarm
aws cloudwatch put-metric-alarm \
    --alarm-name "SES-High-Bounce-Rate" \
    --alarm-description "Alert when email bounce rate is high" \
    --metric-name "Bounce" \
    --namespace "AWS/SES" \
    --statistic "Sum" \
    --period 300 \
    --threshold 5 \
    --comparison-operator "GreaterThanThreshold" \
    --evaluation-periods 1
```

### Step 4: Application Logging
```python
import logging
import json
from datetime import datetime

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('notification_service.log'),
        logging.StreamHandler()
    ]
)

class NotificationLogger:
    def __init__(self):
        self.logger = logging.getLogger('NotificationService')
    
    def log_notification_attempt(self, method, recipient, message_type, success, message_id=None, error=None):
        log_data = {
            'timestamp': datetime.now().isoformat(),
            'method': method,
            'recipient': recipient,
            'message_type': message_type,
            'success': success,
            'message_id': message_id,
            'error': error
        }
        
        if success:
            self.logger.info(f"Notification sent successfully: {json.dumps(log_data)}")
        else:
            self.logger.error(f"Notification failed: {json.dumps(log_data)}")
```

---

## Security Considerations

### 1. Data Protection
- **PII Handling**: Never log full phone numbers or email addresses
- **Message Content**: Avoid logging sensitive message content
- **Encryption**: Use TLS for all API communications (enabled by default)

### 2. Access Control
```bash
# Principle of least privilege IAM policy
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "sns:Publish"
            ],
            "Resource": "arn:aws:sns:ap-southeast-1:ACCOUNT-ID:*",
            "Condition": {
                "StringEquals": {
                    "sns:Protocol": "sms"
                }
            }
        },
        {
            "Effect": "Allow",
            "Action": [
                "ses:SendEmail"
            ],
            "Resource": "arn:aws:ses:ap-southeast-1:ACCOUNT-ID:identity/yourdomain.com"
        }
    ]
}
```

### 3. Rate Limiting & Abuse Prevention
```python
from functools import wraps
import time
from collections import defaultdict

class RateLimiter:
    def __init__(self):
        self.requests = defaultdict(list)
    
    def is_allowed(self, identifier, max_requests=5, time_window=60):
        now = time.time()
        # Clean old requests
        self.requests[identifier] = [
            req_time for req_time in self.requests[identifier]
            if now - req_time < time_window
        ]
        
        if len(self.requests[identifier]) >= max_requests:
            return False
        
        self.requests[identifier].append(now)
        return True

def rate_limit(max_requests=5, time_window=60):
    limiter = RateLimiter()
    
    def decorator(func):
        @wraps(func)
        def wrapper(*args, **kwargs):
            # Use phone/email as identifier
            identifier = kwargs.get('phone_number') or kwargs.get('to_email')
            
            if not limiter.is_allowed(identifier, max_requests, time_window):
                raise Exception(f"Rate limit exceeded for {identifier}")
            
            return func(*args, **kwargs)
        return wrapper
    return decorator
```

### 4. Input Validation
```python
import re
from typing import Optional

class InputValidator:
    @staticmethod
    def validate_philippine_phone(phone: str) -> Optional[str]:
        # Remove spaces and special characters
        clean_phone = re.sub(r'[^\d+]', '', phone)
        
        # Philippine mobile patterns
        patterns = [
            r'^\+639\d{9}$',  # +639XXXXXXXXX
            r'^09\d{9}$',     # 09XXXXXXXXX
            r'^9\d{9}$'       # 9XXXXXXXXX
        ]
        
        for pattern in patterns:
            if re.match(pattern, clean_phone):
                # Normalize to +63 format
                if clean_phone.startswith('+63'):
                    return clean_phone
                elif clean_phone.startswith('09'):
                    return f"+63{clean_phone[1:]}"
                elif clean_phone.startswith('9'):
                    return f"+63{clean_phone}"
        
        return None
    
    @staticmethod
    def validate_email(email: str) -> bool:
        pattern = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
        return re.match(pattern, email) is not None
    
    @staticmethod
    def sanitize_message(message: str) -> str:
        # Remove potential malicious content
        # Limit message length
        return message[:1600]  # SMS limit consideration
```

---

## Troubleshooting

### Common Issues & Solutions

#### SMS Issues
```bash
# Check SMS attributes
aws sns get-sms-attributes

# Common issues:
# 1. Spending limit reached
# 2. Invalid phone number format
# 3. Unsupported destination country
# 4. Message too long (>1600 characters)
```

**Solutions:**
- Verify phone number format: `+639XXXXXXXXX`
- Check spending limits: Request increase via AWS Support
- Verify message length and content

#### Email Issues
```bash
# Check SES sending statistics
aws ses get-send-statistics

# Check bounce/complaint rates
aws ses get-account-sending-enabled
```

**Solutions:**
- Verify sender domain/email
- Check bounce/complaint rates (<5% bounce, <0.1% complaint)
- Ensure proper DKIM setup
- Request production access if in sandbox

#### Delivery Issues
```python
# Implement retry logic
import time
import random

def send_with_retry(send_function, max_retries=3, backoff_factor=2):
    for attempt in range(max_retries):
        try:
            result = send_function()
            if result['success']:
                return result
        except Exception as e:
            if attempt == max_retries - 1:
                raise e
            
            # Exponential backoff with jitter
            delay = backoff_factor ** attempt + random.uniform(0, 1)
            time.sleep(delay)
    
    return {'success': False, 'error': 'Max retries exceeded'}
```

### Monitoring Commands
```bash
# Check SNS metrics
aws cloudwatch get-metric-statistics \
    --namespace AWS/SNS \
    --metric-name NumberOfMessagesSent \
    --start-time 2024-01-01T00:00:00Z \
    --end-time 2024-01-02T00:00:00Z \
    --period 3600 \
    --statistics Sum

# Check SES metrics
aws cloudwatch get-metric-statistics \
    --namespace AWS/SES \
    --metric-name Send \
    --start-time 2024-01-01T00:00:00Z \
    --end-time 2024-01-02T00:00:00Z \
    --period 3600 \
    --statistics Sum
```

---

## Production Deployment Checklist

### Pre-Deployment
- [ ] AWS account configured with appropriate permissions
- [ ] SNS spending limits increased for production volume
- [ ] SES production access approved
- [ ] Domain/email verification completed
- [ ] DKIM configured for email authentication
- [ ] Rate limiting implemented
- [ ] Input validation in place
- [ ] Logging and monitoring configured
- [ ] Error handling and retry logic implemented

### Post-Deployment
- [ ] Test SMS delivery to Philippine numbers
- [ ] Test email delivery and deliverability
- [ ] Monitor bounce and complaint rates
- [ ] Set up CloudWatch alarms
- [ ] Verify logging is working
- [ ] Test rate limiting functionality
- [ ] Conduct load testing
- [ ] Document operational procedures

### Ongoing Maintenance
- [ ] Monitor delivery rates and costs
- [ ] Review bounce/complaint handling
- [ ] Update spending limits as needed
- [ ] Regular security reviews
- [ ] Performance optimization
- [ ] Backup and disaster recovery procedures

---

## Cost Optimization Tips

1. **SMS Costs (Philippines)**
   - Transactional SMS: ~$0.045 per message
   - Promotional SMS: ~$0.035 per message
   - Use promotional type for marketing messages

2. **Email Costs**
   - First 62,000 emails/month: Free (if sent from EC2)
   - Additional emails: $0.10 per 1,000 emails

3. **Optimization Strategies**
   - Implement user preferences for channel selection
   - Use email for detailed notifications, SMS for urgent alerts
   - Monitor and optimize delivery success rates
   - Implement proper bounce handling to maintain reputation

---

This guide provides a comprehensive foundation for implementing a production-ready multi-channel notification system using AWS services specifically optimized for the Philippines market.
