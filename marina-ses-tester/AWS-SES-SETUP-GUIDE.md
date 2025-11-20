# 📧 AWS SES Setup Guide for Marina

**Complete guide for configuring Amazon Simple Email Service (SES) for Marina's email notification system**

---

## 📋 Overview

This guide will help you set up AWS SES for Marina's email testing application, including domain verification, SMTP credentials, and production access.

---

## 🎯 Prerequisites

- **AWS Account** with administrative access
- **Domain Access** to marina.gov.ph DNS settings
- **Email Access** to admin@marina.gov.ph or similar
- **Basic AWS Console** familiarity

### 📧 **Required Email Accounts**

**IMPORTANT**: You must have access to real marina.gov.ph email accounts for verification.

#### **Email Accounts Needed:**
1. **admin@marina.gov.ph** - Administrative emails
2. **noreply@marina.gov.ph** - System notifications (primary sender)
3. **support@marina.gov.ph** - Customer support emails
4. **alerts@marina.gov.ph** - System alerts and warnings

#### **Why Real Accounts Required:**
- ✅ **AWS verification** - Must receive and click verification links
- ✅ **Professional appearance** - Citizens expect official marina.gov.ph emails
- ✅ **Government compliance** - Official domain required for government communications
- ✅ **Security standards** - Proper domain authentication
- ❌ **Don't use** personal Gmail/Yahoo accounts for production

#### **Setup Requirements:**
- **Create accounts** through Marina's email system
- **Ensure access** to receive verification emails
- **Set up forwarding** if needed for management
- **Test email delivery** before SES setup

---

## 🚀 Step-by-Step Setup

### Step 1: Access AWS SES Console

1. **Login to AWS Console**: https://console.aws.amazon.com/
2. **Select Region**: Choose **Asia Pacific (Singapore) - ap-southeast-1**
   - This is closest to the Philippines for better performance
3. **Navigate to SES**: Search for "SES" or "Simple Email Service"
4. **Open SES Dashboard**

### Step 2: Verify Domain (marina.gov.ph)

#### 2.1 Start Domain Verification
1. **Click "Verified identities"** in the left sidebar
2. **Click "Create identity"**
3. **Select "Domain"**
4. **Enter domain**: `marina.gov.ph`
5. **Check "Assign a default configuration set"** (optional)
6. **Click "Create identity"**

#### 2.2 Configure DNS Records
AWS will provide DNS records that need to be added to marina.gov.ph:

**DKIM Records** (for authentication):
```
Type: CNAME
Name: [random-string]._domainkey.marina.gov.ph
Value: [random-string].dkim.amazonses.com
```

**Verification Record**:
```
Type: TXT
Name: _amazonses.marina.gov.ph
Value: [verification-token]
```

#### 2.3 Add DNS Records
1. **Contact Marina's DNS administrator**
2. **Add the provided DNS records**
3. **Wait for propagation** (can take up to 72 hours)
4. **Verify in SES console** - status should change to "Verified"

### Step 3: Verify Email Addresses (For Testing)

**⚠️ IMPORTANT**: Only verify email addresses you actually have access to.

**📧 For Sandbox Testing**: See **[SES Sandbox Testing Guide](./SES-SANDBOX-TESTING-GUIDE.md)** for complete testing instructions.

While domain verification is pending, verify individual email addresses:

1. **Click "Create identity"**
2. **Select "Email address"**
3. **Enter email**: `admin@marina.gov.ph`
4. **Click "Create identity"**
5. **Check email** for verification link
6. **Click verification link**

**Verify these Marina email addresses**:
- `admin@marina.gov.ph` ✅ (Must have access)
- `noreply@marina.gov.ph` ✅ (Primary sender - must have access)
- `support@marina.gov.ph` ✅ (Must have access)
- `alerts@marina.gov.ph` ✅ (Must have access)
- Your personal email for testing ✅ (Backup only)

**❌ Do NOT verify emails you don't have access to** - you won't be able to complete verification.

**✅ Coordinate with Marina IT** to ensure all required email accounts exist and are accessible.

**🧪 For immediate testing**: Verify your personal Gmail/Yahoo account to test the Marina application in sandbox mode.

### Step 4: Create SMTP Credentials

1. **Go to "SMTP settings"** in left sidebar
2. **Click "Create SMTP credentials"**
3. **Enter IAM user name**: `marina-ses-smtp-user`
4. **Click "Create user"**
5. **Download credentials** - save securely!

**Credentials will look like**:
```
SMTP Username: AKIAIOSFODNN7EXAMPLE
SMTP Password: wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY
```

### Step 5: Configure SMTP Settings

**Regional SMTP Endpoints**:
- **Singapore**: `email-smtp.ap-southeast-1.amazonaws.com`
- **US East**: `email-smtp.us-east-1.amazonaws.com`
- **EU West**: `email-smtp.eu-west-1.amazonaws.com`

**Ports**:
- **587** (STARTTLS) - Recommended
- **465** (SSL/TLS)
- **25** (Plain) - Often blocked by ISPs

### Step 6: Request Production Access

**⚠️ CRITICAL**: By default, SES is in "sandbox mode" - you can only send to verified addresses.

**🧪 For Testing First**: Before requesting production access, test your Marina application thoroughly using the **[SES Sandbox Testing Guide](./SES-SANDBOX-TESTING-GUIDE.md)**.

#### 6.1 Production Access for POC vs Full Production

**AWS Generally Approves Both:**
- ✅ **POC/Demo Projects** - For testing and client demonstrations
- ✅ **Full Production** - For live government operations
- ✅ **Government Use Cases** - High approval rate for official agencies

#### 6.2 Submit Production Access Request

**For POC/Demo Projects:**
1. **Go to "Account dashboard"**
2. **Click "Request production access"**
3. **Fill out the form for POC**:

```
Mail Type: Transactional
Website URL: https://marina.gov.ph
Use Case Description:
"Marina (Maritime Industry Authority) is developing a Proof of Concept (POC) for official government email communications system including:

- OTP verification codes for citizen portal access
- Transaction confirmation emails for maritime services  
- Official notices and announcements to stakeholders
- System notifications for internal operations

This POC will demonstrate email functionality to government stakeholders and requires testing with real email addresses to validate delivery rates and citizen experience. 

Upon successful POC completion, this will become the official email system serving Filipino citizens and maritime industry stakeholders.

Expected volume during POC: 100-500 emails for testing and demonstration purposes."

Additional Contact Info: [Your contact details]
```

**For Full Production:**
```
Mail Type: Transactional
Website URL: https://marina.gov.ph
Use Case Description:
"Marina (Maritime Industry Authority) requires SES for sending official government communications including:
- OTP verification codes for citizen portal access
- Transaction confirmation emails for maritime services
- Official notices and announcements to stakeholders
- System notifications for internal operations

This is for official government use serving Filipino citizens and maritime industry stakeholders.

Expected volume: 10,000-50,000 emails per month for government operations."

Additional Contact Info: [Your contact details]
```

#### 6.3 Why AWS Approves POC Requests

**Strong Approval Factors:**
- ✅ **Government agency** - Official .gov.ph domain
- ✅ **Legitimate use case** - Public service communications
- ✅ **Clear business purpose** - Citizen services improvement
- ✅ **Professional presentation** - Well-documented request
- ✅ **Reasonable volume** - Appropriate for testing/demo

**Approval Rate**: **Very High** for government POC projects

#### 6.4 Expected Timeline

**POC Requests:**
- **Submission**: Same day
- **Review Time**: 12-24 hours (faster than production)
- **Approval**: Usually within 24 hours
- **Reason**: Lower risk, testing purpose

**Full Production Requests:**
- **Submission**: Same day
- **Review Time**: 24-48 hours
- **Approval**: Usually granted for government use
- **Follow-up**: AWS may request additional information

#### 6.5 Benefits of Production Access for POC

**Demo Advantages:**
- ✅ **Send to client emails** during presentation
- ✅ **Test with real addresses** (not just verified ones)
- ✅ **Show actual delivery rates** to major providers
- ✅ **Demonstrate production capabilities** 
- ✅ **More impressive client presentation**
- ✅ **Validate deliverability** across different email providers

**Technical Benefits:**
- ✅ **Higher sending limits** (starts at 200/day, increases automatically)
- ✅ **Better rate limits** (starts at 1/second, increases)
- ✅ **Full monitoring capabilities**
- ✅ **Complete SES feature access**

#### 6.6 What to Include in Your Request

**Essential Information:**
1. **Clear POC purpose** - Explain it's for demonstration/testing
2. **Government context** - Emphasize official agency status
3. **Citizen benefit** - How this improves public services
4. **Volume estimates** - Realistic numbers for POC phase
5. **Timeline** - When you need access for demo/testing
6. **Contact information** - Professional government contact

**Sample Timeline Statement:**
```
"Production access needed by [DATE] for stakeholder demonstration and POC validation. 
This will enable testing with real email addresses to validate system performance 
before full deployment to serve Filipino citizens."
```

#### 6.7 After Approval

**Immediate Actions:**
1. **Verify approval** in SES console
2. **Test sending** to unverified addresses
3. **Monitor sending limits** and reputation
4. **Update Marina app** configuration if needed
5. **Conduct thorough testing** before client demo

**Ongoing Monitoring:**
- **Bounce rates** (keep < 5%)
- **Complaint rates** (keep < 0.1%)
- **Sending volume** (within limits)
- **Reputation metrics** (maintain good standing)

#### 6.8 Troubleshooting Production Access

**If Request is Denied:**
1. **Review feedback** from AWS
2. **Provide additional information** if requested
3. **Clarify government use case** more clearly
4. **Resubmit with improvements**
5. **Contact AWS support** for guidance

**Common Issues:**
- **Insufficient detail** - Add more specific use case information
- **Volume concerns** - Justify expected email volume
- **Domain verification** - Ensure marina.gov.ph is properly verified
- **Contact information** - Use official government contact details

**💡 Tip**: Complete testing in sandbox mode first to ensure everything works before requesting production access.

### Step 7: Configuration Sets (Optional but Recommended)

Configuration Sets help organize and monitor different types of emails.

#### 7.1 Create Configuration Sets
1. **Go to "Configuration sets"** in SES console
2. **Click "Create set"**
3. **Create these sets for Marina**:

**marina-transactional:**
```
Name: marina-transactional
Description: OTP and transaction confirmation emails
Use for: Critical government communications
```

**marina-notifications:**
```
Name: marina-notifications  
Description: General announcements and notices
Use for: Non-critical communications
```

**marina-alerts:**
```
Name: marina-alerts
Description: System and security alerts
Use for: Emergency notifications
```

#### 7.2 Configure Event Publishing
For each configuration set:
1. **Click on configuration set name**
2. **Go to "Event publishing"**
3. **Add destination** (CloudWatch recommended):
   ```
   Event types: Send, Bounce, Complaint, Delivery
   Destination: CloudWatch
   ```

#### 7.3 Use in Marina App
Update your application to specify configuration sets:
```php
// In SESMailer.php, add:
$mail->addCustomHeader('X-SES-CONFIGURATION-SET', 'marina-transactional');
```

#### 7.4 Benefits for Marina
- **Separate tracking** per email type
- **Better compliance reporting**
- **Reputation monitoring** by category
- **Troubleshooting** specific email types

---

## ⚙️ Configuration for Marina App

### Settings Page Configuration

Once you have SES set up, configure the Marina app:

```
SMTP Host: email-smtp.ap-southeast-1.amazonaws.com
SMTP Port: 587
SMTP Username: [Your SMTP Username from Step 4]
SMTP Password: [Your SMTP Password from Step 4]
From Email: noreply@marina.gov.ph
From Name: Marina Portal
```

### Environment Variables (Alternative)

You can also set these as environment variables in docker-compose.yml:

#### **Add to docker-compose.yml:**
```yaml
services:
  web:
    environment:
      - DB_HOST=db
      - DB_NAME=marina_ses
      - DB_USER=marina_user
      - DB_PASS=marina_pass123
      - SES_SMTP_HOST=email-smtp.ap-southeast-1.amazonaws.com
      - SES_SMTP_PORT=587
      - SES_SMTP_USERNAME=your_actual_smtp_username
      - SES_SMTP_PASSWORD=your_actual_smtp_password
      - SES_FROM_EMAIL=noreply@marina.gov.ph
      - SES_FROM_NAME=Marina Portal
```

#### **Steps to Configure:**
1. **Open** `/marina-ses-tester/docker-compose.yml`
2. **Find** the `web` service `environment` section
3. **Add** the SES environment variables above
4. **Replace** `your_actual_smtp_username` and `your_actual_smtp_password` with real credentials
5. **Restart** containers:
   ```bash
   docker-compose down
   docker-compose up -d
   ```

#### **Benefits of Environment Variables:**
- ✅ **Secure** - credentials not stored in database
- ✅ **Version control safe** - can exclude from git
- ✅ **Easy deployment** - different configs per environment
- ✅ **Docker best practice** - configuration via environment

#### **Security Note:**
Consider using `.env` file for sensitive credentials:
```bash
# Create .env file
echo "SES_SMTP_USERNAME=your_username" >> .env
echo "SES_SMTP_PASSWORD=your_password" >> .env
```

---

## 🔍 Testing and Verification

### Test 1: Basic Connectivity

```bash
# Test SMTP connection using telnet
telnet email-smtp.ap-southeast-1.amazonaws.com 587

# Expected response:
220 email-smtp.amazonaws.com ESMTP SimpleEmailService-d-XXXXXXXX
```

### Test 2: Send Test Email

1. **Login to Marina app**: http://localhost:8080
2. **Go to Basic Test**
3. **Send to verified email address**
4. **Check delivery in SES console**

### Test 3: Template Testing

1. **Go to Template Test**
2. **Select OTP template**
3. **Fill in variables**
4. **Send to verified address**
5. **Verify formatting and delivery**

---

## 📊 Monitoring and Limits

### Sending Limits

**Sandbox Mode**:
- **Recipients**: Only verified addresses
- **Daily Limit**: 200 emails per day
- **Rate Limit**: 1 email per second

**Production Mode**:
- **Recipients**: Any valid email address
- **Daily Limit**: Starts at 200, increases automatically
- **Rate Limit**: Starts at 1/second, increases automatically

### Monitoring Dashboard

1. **Go to "Reputation metrics"**
2. **Monitor**:
   - Bounce rate (should be < 5%)
   - Complaint rate (should be < 0.1%)
   - Delivery rate (should be > 95%)

### CloudWatch Integration

SES automatically sends metrics to CloudWatch:
- **Send**: Number of emails sent
- **Bounce**: Number of bounced emails
- **Complaint**: Number of spam complaints
- **Delivery**: Number of successful deliveries

---

## 🔒 Security Best Practices

### IAM Permissions

Create dedicated IAM user for SES:

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "ses:SendEmail",
                "ses:SendRawEmail"
            ],
            "Resource": "*"
        }
    ]
}
```

### SMTP Credentials Security

- **Store securely**: Never commit to code repositories
- **Rotate regularly**: Change credentials every 90 days
- **Limit access**: Only authorized personnel
- **Monitor usage**: Check for unusual activity

### Domain Authentication

Ensure proper SPF, DKIM, and DMARC records:

**SPF Record**:
```
Type: TXT
Name: marina.gov.ph
Value: "v=spf1 include:amazonses.com ~all"
```

**DMARC Record**:
```
Type: TXT
Name: _dmarc.marina.gov.ph
Value: "v=DMARC1; p=quarantine; rua=mailto:dmarc@marina.gov.ph"
```

---

## 🚨 Troubleshooting

### Common Issues

#### Issue 1: Domain Verification Pending
**Symptoms**: Domain shows "Pending verification"
**Solutions**:
- Check DNS records are correctly added
- Wait up to 72 hours for propagation
- Use DNS checker tools to verify records

#### Issue 2: SMTP Authentication Failed
**Symptoms**: "Authentication failed" errors
**Solutions**:
- Verify SMTP credentials are correct
- Check region matches (Singapore = ap-southeast-1)
- Ensure credentials have SES permissions

#### Issue 3: Emails Going to Spam
**Symptoms**: Emails delivered to spam folder
**Solutions**:
- Complete domain verification with DKIM
- Add SPF and DMARC records
- Improve email content and formatting
- Monitor reputation metrics

#### Issue 4: Sending Limits Exceeded
**Symptoms**: "Sending quota exceeded" errors
**Solutions**:
- Request limit increase in SES console
- Monitor daily sending volume
- Implement rate limiting in application

### Debug Commands

```bash
# Test DNS records
nslookup -type=TXT _amazonses.marina.gov.ph
nslookup -type=CNAME [dkim-key]._domainkey.marina.gov.ph

# Test SMTP connectivity
telnet email-smtp.ap-southeast-1.amazonaws.com 587

# Check email headers for authentication
# Look for DKIM-Signature and SPF results
```

---

## 📈 Scaling and Optimization

### Increasing Limits

1. **Monitor usage** in SES dashboard
2. **Request increases** before hitting limits
3. **Maintain good reputation** (low bounce/complaint rates)
4. **Document business justification**

### Performance Optimization

- **Use appropriate region** (Singapore for Philippines)
- **Implement connection pooling**
- **Batch email sending** for bulk operations
- **Monitor and optimize** email content

### Cost Optimization

**SES Pricing** (Singapore region):
- **First 62,000 emails/month**: $0.10 per 1,000 emails
- **Additional emails**: $0.10 per 1,000 emails
- **Data transfer**: $0.12 per GB

**Cost Comparison**:
- **100,000 emails/month**: ~$10 USD
- **SMS alternative**: ~$450 USD (significant savings!)

---

## 📞 Support Resources

### AWS Support

- **SES Documentation**: https://docs.aws.amazon.com/ses/
- **AWS Support Console**: https://console.aws.amazon.com/support/
- **SES Forum**: https://forums.aws.amazon.com/forum.jspa?forumID=90

### Marina Contacts

- **IT Department**: [Contact Information]
- **DNS Administrator**: [Contact Information]
- **Security Team**: [Contact Information]

---

## ✅ Setup Checklist

**Before going live, ensure:**

- [ ] **Domain verified** in SES console
- [ ] **DKIM records** added to DNS
- [ ] **SPF record** configured
- [ ] **SMTP credentials** created and secured
- [ ] **Production access** approved
- [ ] **Test emails** sending successfully
- [ ] **Templates** working correctly
- [ ] **Monitoring** configured
- [ ] **Limits** appropriate for usage
- [ ] **Security** best practices implemented

---

**🎯 Success Criteria**: When you can send emails to any address (not just verified ones), your SES setup is complete and ready for production!

**Maritime Industry Authority**  
Republic of the Philippines
