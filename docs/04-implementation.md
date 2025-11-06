# 💻 Application Implementation
## Marina Email Service Code Integration

> **Phase 4**: Implement production-ready email service with professional templates and error handling for Marina's citizen communications.

---

## 🎯 Implementation Objectives

**Goals:**
- Create robust email service class for Marina applications
- Implement professional email templates for government communications
- Add comprehensive error handling and retry logic
- Ensure rate limiting and security best practices

**Architecture**: Modular design supporting OTP, alerts, marketing, and system notifications

---

## 🐍 Python Implementation

> **Purpose**: Complete email service implementation for Python-based Marina applications
> **Features**: Professional templates, error handling, logging, rate limiting
> **Government Focus**: Appropriate tone and branding for official communications

### Core Email Service Class

```python
import boto3
import json
import logging
from datetime import datetime
from typing import Dict, Any, Optional
from functools import wraps
import time
from collections import defaultdict

class MarinaEmailService:
    def __init__(self, region='ap-southeast-1'):
        """Initialize Marina Email Service with AWS SES client"""
        self.ses = boto3.client('ses', region_name=region)
        self.logger = logging.getLogger(__name__)
        self._setup_logging()
        
    def _setup_logging(self):
        """Configure logging for audit trail"""
        logging.basicConfig(
            level=logging.INFO,
            format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
            handlers=[
                logging.FileHandler('marina_email_service.log'),
                logging.StreamHandler()
            ]
        )
        
    def send_email(self, to_email: str, subject: str, body_text: str, 
                   body_html: str = None, from_email: str = 'noreply@marina.gov.ph',
                   reply_to: str = None) -> Dict[str, Any]:
        """Send email via Amazon SES with comprehensive error handling"""
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
            
            # Log successful send (mask email for privacy)
            masked_email = self._mask_email(to_email)
            self.logger.info(f"Email sent successfully to {masked_email}: {response['MessageId']}")
            
            return {
                'success': True,
                'message_id': response['MessageId'],
                'to_email': to_email,
                'timestamp': datetime.now().isoformat()
            }
            
        except Exception as e:
            # Log error (mask email for privacy)
            masked_email = self._mask_email(to_email)
            self.logger.error(f"Email sending failed to {masked_email}: {str(e)}")
            
            return {
                'success': False,
                'error': str(e),
                'to_email': to_email,
                'timestamp': datetime.now().isoformat()
            }
    
    def _mask_email(self, email: str) -> str:
        """Mask email for privacy in logs"""
        if '@' in email:
            local, domain = email.split('@', 1)
            if len(local) > 1:
                masked_local = local[0] + '*' * (len(local) - 1)
                return f"{masked_local}@{domain}"
        return email
```

### Government Email Templates

```python
    def send_otp_email(self, to_email: str, otp_code: str, 
                       service_name: str = "Marina Online Services") -> Dict[str, Any]:
        """Send OTP email with government-appropriate template"""
        subject = f"Your {service_name} Verification Code"
        
        body_text = f"""
MARITIME INDUSTRY AUTHORITY
{service_name}

Your verification code is: {otp_code}

This code is valid for 5 minutes only.
Do not share this code with anyone.

If you did not request this code, please ignore this email or contact our support team.

For assistance, visit: https://marina.gov.ph/support
Email: support@marina.gov.ph

Maritime Industry Authority
Republic of the Philippines
        """
        
        body_html = f"""
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;">
            <div style="background-color: #1e3a8a; color: white; padding: 20px; text-align: center;">
                <h1 style="margin: 0; font-size: 24px;">MARITIME INDUSTRY AUTHORITY</h1>
                <p style="margin: 5px 0 0 0; font-size: 14px;">Republic of the Philippines</p>
            </div>
            
            <div style="padding: 30px 20px;">
                <h2 style="color: #1e3a8a; margin-bottom: 20px;">Verification Code</h2>
                
                <p>Your verification code for <strong>{service_name}</strong> is:</p>
                
                <div style="background-color: #f0f9ff; border: 2px solid #1e3a8a; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0;">
                    <h1 style="color: #1e3a8a; font-size: 36px; margin: 0; letter-spacing: 8px; font-family: monospace;">{otp_code}</h1>
                </div>
                
                <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0;">
                    <p style="margin: 0;"><strong>Important:</strong></p>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li>This code is valid for <strong>5 minutes only</strong></li>
                        <li>Do not share this code with anyone</li>
                        <li>Marina will never ask for your verification code</li>
                    </ul>
                </div>
                
                <p>If you did not request this code, please ignore this email or contact our support team.</p>
            </div>
            
            <div style="background-color: #f8fafc; padding: 20px; border-top: 1px solid #e2e8f0;">
                <p style="margin: 0; font-size: 12px; color: #64748b;">
                    <strong>Maritime Industry Authority</strong><br>
                    For assistance: <a href="https://marina.gov.ph/support" style="color: #1e3a8a;">marina.gov.ph/support</a><br>
                    Email: <a href="mailto:support@marina.gov.ph" style="color: #1e3a8a;">support@marina.gov.ph</a>
                </p>
            </div>
        </body>
        </html>
        """
        
        return self.send_email(to_email, subject, body_text, body_html)
    
    def send_transaction_alert(self, to_email: str, transaction_data: Dict[str, Any]) -> Dict[str, Any]:
        """Send transaction confirmation/alert email"""
        transaction_type = transaction_data.get('type', 'Transaction')
        subject = f"Marina Transaction Confirmation - {transaction_type}"
        
        body_text = f"""
MARITIME INDUSTRY AUTHORITY
Transaction Confirmation

Transaction Details:
Type: {transaction_data.get('type', 'N/A')}
Amount: {transaction_data.get('amount', 'N/A')}
Reference Number: {transaction_data.get('reference', 'N/A')}
Date: {transaction_data.get('date', datetime.now().strftime('%B %d, %Y at %I:%M %p'))}
Status: {transaction_data.get('status', 'Completed')}

This is an automated confirmation of your transaction with Marina Online Services.

For inquiries about this transaction, please contact:
Email: support@marina.gov.ph
Phone: (02) 8527-8537

Maritime Industry Authority
Republic of the Philippines
        """
        
        body_html = f"""
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;">
            <div style="background-color: #059669; color: white; padding: 20px; text-align: center;">
                <h1 style="margin: 0; font-size: 24px;">MARITIME INDUSTRY AUTHORITY</h1>
                <p style="margin: 5px 0 0 0; font-size: 14px;">Transaction Confirmation</p>
            </div>
            
            <div style="padding: 30px 20px;">
                <h2 style="color: #059669; margin-bottom: 20px;">Transaction Completed Successfully</h2>
                
                <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; margin: 20px 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold; color: #374151;">Type:</td>
                            <td style="padding: 8px 0; color: #059669;">{transaction_data.get('type', 'N/A')}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold; color: #374151;">Amount:</td>
                            <td style="padding: 8px 0; color: #059669;">{transaction_data.get('amount', 'N/A')}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold; color: #374151;">Reference:</td>
                            <td style="padding: 8px 0; color: #059669;">{transaction_data.get('reference', 'N/A')}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold; color: #374151;">Date:</td>
                            <td style="padding: 8px 0; color: #059669;">{transaction_data.get('date', datetime.now().strftime('%B %d, %Y at %I:%M %p'))}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold; color: #374151;">Status:</td>
                            <td style="padding: 8px 0; color: #059669; font-weight: bold;">{transaction_data.get('status', 'Completed')}</td>
                        </tr>
                    </table>
                </div>
                
                <p>This is an automated confirmation of your transaction with Marina Online Services.</p>
                
                <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; margin: 20px 0;">
                    <p style="margin: 0;"><strong>Need Help?</strong></p>
                    <p style="margin: 10px 0 0 0;">For inquiries about this transaction:</p>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li>Email: <a href="mailto:support@marina.gov.ph" style="color: #1e3a8a;">support@marina.gov.ph</a></li>
                        <li>Phone: (02) 8527-8537</li>
                        <li>Visit: <a href="https://marina.gov.ph" style="color: #1e3a8a;">marina.gov.ph</a></li>
                    </ul>
                </div>
            </div>
            
            <div style="background-color: #f8fafc; padding: 20px; border-top: 1px solid #e2e8f0;">
                <p style="margin: 0; font-size: 12px; color: #64748b; text-align: center;">
                    Maritime Industry Authority - Republic of the Philippines<br>
                    This is an automated message. Please do not reply to this email.
                </p>
            </div>
        </body>
        </html>
        """
        
        return self.send_email(to_email, subject, body_text, body_html)
```

### Rate Limiting and Security

```python
class RateLimiter:
    def __init__(self):
        self.requests = defaultdict(list)
    
    def is_allowed(self, identifier: str, max_requests: int = 10, time_window: int = 300) -> bool:
        """Check if request is within rate limits (10 emails per 5 minutes per user)"""
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

def rate_limit(max_requests: int = 10, time_window: int = 300):
    """Decorator for rate limiting email sends"""
    limiter = RateLimiter()
    
    def decorator(func):
        @wraps(func)
        def wrapper(self, to_email: str, *args, **kwargs):
            if not limiter.is_allowed(to_email, max_requests, time_window):
                return {
                    'success': False,
                    'error': f'Rate limit exceeded for {to_email}. Max {max_requests} emails per {time_window//60} minutes.',
                    'to_email': to_email,
                    'timestamp': datetime.now().isoformat()
                }
            return func(self, to_email, *args, **kwargs)
        return wrapper
    return decorator

# Apply rate limiting to email methods
MarinaEmailService.send_otp_email = rate_limit(5, 300)(MarinaEmailService.send_otp_email)  # 5 OTPs per 5 min
MarinaEmailService.send_transaction_alert = rate_limit(10, 300)(MarinaEmailService.send_transaction_alert)
```

---

## 📗 Node.js Implementation

```javascript
const AWS = require('aws-sdk');
const winston = require('winston');

class MarinaEmailService {
    constructor(region = 'ap-southeast-1') {
        this.ses = new AWS.SES({ region });
        this.setupLogging();
        this.rateLimiter = new Map();
    }
    
    setupLogging() {
        this.logger = winston.createLogger({
            level: 'info',
            format: winston.format.combine(
                winston.format.timestamp(),
                winston.format.json()
            ),
            transports: [
                new winston.transports.File({ filename: 'marina_email_service.log' }),
                new winston.transports.Console()
            ]
        });
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
            
            // Log success (mask email)
            const maskedEmail = this.maskEmail(toEmail);
            this.logger.info(`Email sent successfully to ${maskedEmail}: ${result.MessageId}`);
            
            return {
                success: true,
                messageId: result.MessageId,
                toEmail,
                timestamp: new Date().toISOString()
            };
            
        } catch (error) {
            const maskedEmail = this.maskEmail(toEmail);
            this.logger.error(`Email sending failed to ${maskedEmail}: ${error.message}`);
            
            return {
                success: false,
                error: error.message,
                toEmail,
                timestamp: new Date().toISOString()
            };
        }
    }
    
    maskEmail(email) {
        if (email.includes('@')) {
            const [local, domain] = email.split('@');
            const masked = local.length > 1 ? local[0] + '*'.repeat(local.length - 1) : local;
            return `${masked}@${domain}`;
        }
        return email;
    }
    
    async sendOTPEmail(toEmail, otpCode, serviceName = 'Marina Online Services') {
        // Check rate limit
        if (!this.checkRateLimit(toEmail, 5, 300000)) { // 5 OTPs per 5 minutes
            return {
                success: false,
                error: 'Rate limit exceeded for OTP emails',
                toEmail,
                timestamp: new Date().toISOString()
            };
        }
        
        const subject = `Your ${serviceName} Verification Code`;
        
        const bodyText = `
MARITIME INDUSTRY AUTHORITY
${serviceName}

Your verification code is: ${otpCode}

This code is valid for 5 minutes only.
Do not share this code with anyone.

Maritime Industry Authority
Republic of the Philippines
        `;
        
        const bodyHtml = `
        <html>
        <body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <div style="background-color: #1e3a8a; color: white; padding: 20px; text-align: center;">
                <h1 style="margin: 0;">MARITIME INDUSTRY AUTHORITY</h1>
            </div>
            <div style="padding: 30px 20px;">
                <h2 style="color: #1e3a8a;">Verification Code</h2>
                <div style="background-color: #f0f9ff; border: 2px solid #1e3a8a; border-radius: 8px; padding: 20px; text-align: center;">
                    <h1 style="color: #1e3a8a; font-size: 36px; margin: 0; letter-spacing: 8px;">${otpCode}</h1>
                </div>
                <p><strong>Valid for 5 minutes only. Do not share this code.</strong></p>
            </div>
        </body>
        </html>
        `;
        
        return await this.sendEmail(toEmail, subject, bodyText, bodyHtml);
    }
    
    checkRateLimit(identifier, maxRequests, timeWindow) {
        const now = Date.now();
        const requests = this.rateLimiter.get(identifier) || [];
        
        // Clean old requests
        const validRequests = requests.filter(time => now - time < timeWindow);
        
        if (validRequests.length >= maxRequests) {
            return false;
        }
        
        validRequests.push(now);
        this.rateLimiter.set(identifier, validRequests);
        return true;
    }
}

module.exports = MarinaEmailService;
```

---

## 🧪 Usage Examples

### Python Usage
```python
# Initialize service
email_service = MarinaEmailService()

# Send OTP for citizen login
otp_result = email_service.send_otp_email(
    to_email="citizen@example.com",
    otp_code="123456",
    service_name="Marina Vessel Registration"
)

# Send transaction confirmation
transaction_data = {
    'type': 'Vessel Registration Fee',
    'amount': 'PHP 2,500.00',
    'reference': 'MRN-2024-001234',
    'date': 'January 6, 2024 at 2:30 PM',
    'status': 'Completed'
}

alert_result = email_service.send_transaction_alert(
    to_email="vessel.owner@example.com",
    transaction_data=transaction_data
)

print(f"OTP Result: {otp_result}")
print(f"Alert Result: {alert_result}")
```

### Node.js Usage
```javascript
const MarinaEmailService = require('./marina-email-service');

async function sendNotifications() {
    const emailService = new MarinaEmailService();
    
    // Send OTP
    const otpResult = await emailService.sendOTPEmail(
        'citizen@example.com',
        '123456',
        'Marina Port Clearance System'
    );
    
    console.log('OTP Result:', otpResult);
}

sendNotifications();
```

---

## ✅ Implementation Checklist

- [ ] **Core Service Class**: Email service implemented with error handling
- [ ] **Government Templates**: Professional templates for Marina communications
- [ ] **Rate Limiting**: Protection against abuse (5 OTPs, 10 alerts per 5 min)
- [ ] **Logging**: Comprehensive audit trail with privacy protection
- [ ] **Error Handling**: Graceful failure handling with detailed error messages
- [ ] **Security**: Email masking in logs, input validation
- [ ] **Testing**: Unit tests for all email types

---

**📅 Estimated Time**: 4-6 hours  
**👥 Key Roles**: Developer, Designer (for templates)  
**🎯 Outcome**: Production-ready email service for Marina applications
