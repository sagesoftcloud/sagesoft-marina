# 🔐 Security & IAM Configuration
## Secure Access Setup for Marina Email System

> **Phase 3**: Create secure, limited-privilege access for Marina's email application with government-grade security practices.

---

## 🎯 Security Objectives

**Goals:**
- Create dedicated service account for email application
- Implement principle of least privilege
- Secure credential management for government compliance
- Audit trail for all email operations

**Security Approach**: Minimal permissions, dedicated user, secure credential storage

---

## 🛡️ Step 1: Create IAM Policy

> **Purpose**: Define exactly what permissions the email service needs
> **Principle**: Grant minimum required permissions only
> **Scope**: SES email sending + basic monitoring + logging

### Policy Design Rationale
**Allowed Actions:**
- `ses:SendEmail` - Core email sending functionality
- `ses:SendRawEmail` - Advanced email with attachments
- `ses:GetSendQuota` - Monitor usage limits
- `ses:GetSendStatistics` - Track delivery metrics
- `logs:*` - Application logging for audit trail

**Denied Actions:**
- No IAM management
- No billing access
- No other AWS services
- No SES configuration changes

### Create Policy via Console
1. **Navigate to IAM**:
   - Go to [IAM Console](https://console.aws.amazon.com/iam/)
   - Click **"Policies"** in left menu
   - Click **"Create policy"**

2. **Define Policy**:
   - Select **"JSON"** tab
   - Replace content with:
   ```json
   {
       "Version": "2012-10-17",
       "Statement": [
           {
               "Sid": "SESEmailSending",
               "Effect": "Allow",
               "Action": [
                   "ses:SendEmail",
                   "ses:SendRawEmail"
               ],
               "Resource": "arn:aws:ses:ap-southeast-1:*:identity/marina.gov.ph"
           },
           {
               "Sid": "SESMonitoring", 
               "Effect": "Allow",
               "Action": [
                   "ses:GetSendQuota",
                   "ses:GetSendStatistics",
                   "ses:GetIdentityVerificationAttributes",
                   "ses:GetIdentityDkimAttributes"
               ],
               "Resource": "*"
           },
           {
               "Sid": "CloudWatchLogs",
               "Effect": "Allow", 
               "Action": [
                   "logs:CreateLogGroup",
                   "logs:CreateLogStream", 
                   "logs:PutLogEvents"
               ],
               "Resource": "arn:aws:logs:ap-southeast-1:*:log-group:marina-email-*"
           }
       ]
   }
   ```

3. **Review and Create**:
   - Click **"Next: Tags"** (optional)
   - Click **"Next: Review"**
   - Policy name: `MarinaEmailServicePolicy`
   - Description: `Minimal permissions for Marina email notification service`
   - Click **"Create policy"**

### Create Policy via CLI
```bash
# Create policy file
cat > marina-email-policy.json << 'EOF'
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "SESEmailSending",
            "Effect": "Allow",
            "Action": [
                "ses:SendEmail",
                "ses:SendRawEmail"
            ],
            "Resource": "arn:aws:ses:ap-southeast-1:*:identity/marina.gov.ph"
        },
        {
            "Sid": "SESMonitoring",
            "Effect": "Allow",
            "Action": [
                "ses:GetSendQuota",
                "ses:GetSendStatistics",
                "ses:GetIdentityVerificationAttributes",
                "ses:GetIdentityDkimAttributes"
            ],
            "Resource": "*"
        },
        {
            "Sid": "CloudWatchLogs",
            "Effect": "Allow",
            "Action": [
                "logs:CreateLogGroup",
                "logs:CreateLogStream",
                "logs:PutLogEvents"
            ],
            "Resource": "arn:aws:logs:ap-southeast-1:*:log-group:marina-email-*"
        }
    ]
}
EOF

# Create IAM policy
aws iam create-policy \
    --policy-name MarinaEmailServicePolicy \
    --policy-document file://marina-email-policy.json \
    --description "Minimal permissions for Marina email notification service"
```

### ✅ Policy Verification
```bash
# Verify policy creation
aws iam get-policy \
    --policy-arn arn:aws:iam::ACCOUNT-ID:policy/MarinaEmailServicePolicy

# List policy versions
aws iam list-policy-versions \
    --policy-arn arn:aws:iam::ACCOUNT-ID:policy/MarinaEmailServicePolicy
```

> **Status Check**: Policy appears in IAM console with "Customer managed" type
> **Security Note**: Policy restricts access to marina.gov.ph domain only
> **Next**: Create dedicated service user

---

## 👤 Step 2: Create Service User

> **Purpose**: Dedicated account for email application (not human user)
> **Security**: Programmatic access only, no console access
> **Naming**: Clear identification for audit purposes

### Create User via Console
1. **Navigate to Users**:
   - Go to IAM Console → **"Users"**
   - Click **"Create user"**

2. **User Configuration**:
   - User name: `marina-email-service`
   - Access type: **"Programmatic access"** only
   - ❌ Do NOT enable console access
   - Click **"Next: Permissions"**

3. **Attach Policy**:
   - Select **"Attach existing policies directly"**
   - Search for: `MarinaEmailServicePolicy`
   - Check the policy checkbox
   - Click **"Next: Tags"**

4. **Add Tags** (Recommended):
   ```
   Key: Purpose        Value: EmailNotificationService
   Key: Department     Value: Marina-IT
   Key: Environment    Value: Production
   Key: Owner          Value: marina-admin@marina.gov.ph
   ```

5. **Review and Create**:
   - Verify configuration
   - Click **"Create user"**

### Create User via CLI
```bash
# Create IAM user
aws iam create-user \
    --user-name marina-email-service \
    --tags Key=Purpose,Value=EmailNotificationService \
           Key=Department,Value=Marina-IT \
           Key=Environment,Value=Production

# Attach policy to user
aws iam attach-user-policy \
    --user-name marina-email-service \
    --policy-arn arn:aws:iam::ACCOUNT-ID:policy/MarinaEmailServicePolicy
```

### ✅ User Verification
```bash
# Verify user creation
aws iam get-user --user-name marina-email-service

# Verify policy attachment
aws iam list-attached-user-policies --user-name marina-email-service
```

> **Status Check**: User appears in IAM console with policy attached
> **Security Note**: User has no console access, programmatic only
> **Next**: Generate secure access credentials

---

## 🔑 Step 3: Generate Access Credentials

> **⚠️ CRITICAL SECURITY STEP**: These credentials provide access to send emails from marina.gov.ph
> **One-Time Access**: Secret key cannot be retrieved again after creation
> **Storage**: Must be stored securely per government security policies

### Generate Credentials via Console
1. **Access User Settings**:
   - Go to IAM Console → Users → `marina-email-service`
   - Click **"Security credentials"** tab
   - Click **"Create access key"**

2. **Select Use Case**:
   - Choose **"Application running outside AWS"**
   - Add description: `Marina Email Service Production Credentials`
   - Click **"Create access key"**

3. **⚠️ SECURE CREDENTIAL STORAGE**:
   - **Access Key ID**: Copy immediately
   - **Secret Access Key**: Copy immediately  
   - **Download CSV**: Save to secure location
   - **Never commit to code**: Use environment variables

### Generate Credentials via CLI
```bash
# Create access key
aws iam create-access-key --user-name marina-email-service

# Expected output:
# {
#     "AccessKey": {
#         "UserName": "marina-email-service",
#         "AccessKeyId": "AKIA...",
#         "Status": "Active",
#         "SecretAccessKey": "...",
#         "CreateDate": "2024-01-06T..."
#     }
# }
```

### 🔒 Secure Credential Management

**Government Security Requirements:**
```bash
# Store in environment variables (recommended)
export AWS_ACCESS_KEY_ID="AKIA..."
export AWS_SECRET_ACCESS_KEY="..."
export AWS_DEFAULT_REGION="ap-southeast-1"

# Or use AWS credentials file
mkdir -p ~/.aws
cat > ~/.aws/credentials << 'EOF'
[marina-email]
aws_access_key_id = AKIA...
aws_secret_access_key = ...
region = ap-southeast-1
EOF

# Secure file permissions
chmod 600 ~/.aws/credentials
```

**Application Configuration:**
```python
# Python - Use environment variables
import os
import boto3

# Secure credential loading
ses_client = boto3.client(
    'ses',
    region_name=os.getenv('AWS_DEFAULT_REGION', 'ap-southeast-1'),
    aws_access_key_id=os.getenv('AWS_ACCESS_KEY_ID'),
    aws_secret_access_key=os.getenv('AWS_SECRET_ACCESS_KEY')
)
```

```javascript
// Node.js - Use environment variables
const AWS = require('aws-sdk');

AWS.config.update({
    region: process.env.AWS_DEFAULT_REGION || 'ap-southeast-1',
    accessKeyId: process.env.AWS_ACCESS_KEY_ID,
    secretAccessKey: process.env.AWS_SECRET_ACCESS_KEY
});

const ses = new AWS.SES();
```

### ✅ Credential Testing
```bash
# Test credentials work
AWS_PROFILE=marina-email aws sts get-caller-identity

# Expected output:
# {
#     "UserId": "AIDA...",
#     "Account": "123456789012", 
#     "Arn": "arn:aws:iam::123456789012:user/marina-email-service"
# }

# Test SES permissions
AWS_PROFILE=marina-email aws ses get-send-quota --region ap-southeast-1
```

> **Status Check**: Credentials work and return proper user identity
> **Security Verification**: User can access SES but not other services
> **Next**: Implement credential rotation policy

---

## 🔄 Step 4: Security Best Practices

> **Government Compliance**: Additional security measures for government email system
> **Audit Requirements**: Logging and monitoring for compliance
> **Incident Response**: Procedures for credential compromise

### Credential Rotation Policy
```bash
# Create rotation reminder
cat > credential-rotation-policy.md << 'EOF'
# Marina Email Service Credential Rotation Policy

## Schedule
- Rotate credentials every 90 days
- Emergency rotation if compromise suspected
- Document all rotations in security log

## Process
1. Generate new access key for marina-email-service user
2. Update application configuration with new credentials
3. Test email functionality with new credentials
4. Delete old access key after verification
5. Update security documentation

## Next Rotation Due: [DATE + 90 days]
EOF
```

### Access Monitoring Setup
```bash
# Create CloudTrail for API monitoring (optional but recommended)
aws cloudtrail create-trail \
    --name marina-email-audit-trail \
    --s3-bucket-name marina-audit-logs-bucket \
    --include-global-service-events \
    --is-multi-region-trail

# Enable logging
aws cloudtrail start-logging --name marina-email-audit-trail
```

### Security Alerts
```bash
# Create alarm for unusual API activity
aws cloudwatch put-metric-alarm \
    --alarm-name "Marina-Email-Unusual-Activity" \
    --alarm-description "Alert on high SES API usage" \
    --metric-name "CallCount" \
    --namespace "AWS/SES" \
    --statistic "Sum" \
    --period 300 \
    --threshold 1000 \
    --comparison-operator "GreaterThanThreshold" \
    --evaluation-periods 2
```

### ✅ Security Checklist
- [ ] **Minimal Permissions**: Policy grants only required SES access
- [ ] **No Console Access**: Service user cannot access AWS Console
- [ ] **Secure Storage**: Credentials stored in environment variables
- [ ] **Access Monitoring**: CloudTrail logging enabled (optional)
- [ ] **Rotation Policy**: 90-day rotation schedule documented
- [ ] **Emergency Procedures**: Credential compromise response plan

---

## 🧪 Step 5: Permission Testing

> **Purpose**: Verify security configuration works correctly
> **Test Strategy**: Confirm allowed actions work, denied actions fail
> **Validation**: Ensure principle of least privilege is enforced

### Test Allowed Actions
```bash
# Test SES email sending permission
aws ses send-email \
    --source "noreply@marina.gov.ph" \
    --destination "ToAddresses=admin@marina.gov.ph" \
    --message "Subject={Data=Permission Test},Body={Text={Data=IAM permissions working correctly}}" \
    --region ap-southeast-1 \
    --profile marina-email

# Test SES monitoring permission
aws ses get-send-quota \
    --region ap-southeast-1 \
    --profile marina-email

# Test SES statistics permission
aws ses get-send-statistics \
    --region ap-southeast-1 \
    --profile marina-email
```

### Test Denied Actions (Should Fail)
```bash
# These should return "Access Denied" errors:

# Try to create IAM user (should fail)
aws iam create-user \
    --user-name test-user \
    --profile marina-email

# Try to access EC2 (should fail)
aws ec2 describe-instances \
    --profile marina-email

# Try to modify SES configuration (should fail)
aws ses put-identity-dkim-attributes \
    --identity marina.gov.ph \
    --dkim-enabled \
    --profile marina-email
```

### ✅ Expected Test Results
**Should Succeed:**
- ✅ Send email from marina.gov.ph
- ✅ Get sending quota and statistics
- ✅ Create CloudWatch logs

**Should Fail (Access Denied):**
- ❌ IAM operations
- ❌ EC2 access
- ❌ SES configuration changes
- ❌ Billing information

> **Security Validation**: Permissions work as designed
> **Compliance**: Audit trail shows only authorized actions
> **Next**: Application integration with secure credentials

---

## 📋 Security Configuration Summary

### ✅ Completed Security Setup
- [ ] **IAM Policy Created**: MarinaEmailServicePolicy with minimal permissions
- [ ] **Service User Created**: marina-email-service with programmatic access only
- [ ] **Credentials Generated**: Access keys created and stored securely
- [ ] **Permissions Tested**: Allowed actions work, denied actions fail
- [ ] **Security Monitoring**: CloudTrail and alarms configured (optional)
- [ ] **Rotation Policy**: 90-day credential rotation schedule documented

### 🔍 Security Verification Commands
```bash
# Complete security status check
aws iam get-user --user-name marina-email-service
aws iam list-attached-user-policies --user-name marina-email-service
aws sts get-caller-identity --profile marina-email
aws ses get-send-quota --region ap-southeast-1 --profile marina-email
```

### 📊 Security Posture
```
✅ Principle of Least Privilege: Enforced
✅ Credential Security: Environment variables
✅ Access Monitoring: CloudTrail enabled
✅ Audit Trail: All actions logged
✅ Rotation Policy: 90-day schedule
✅ Emergency Response: Procedures documented
```

---

## 🚨 Security Incident Response

### Credential Compromise Procedure
1. **Immediate Actions**:
   ```bash
   # Disable compromised access key
   aws iam update-access-key \
       --user-name marina-email-service \
       --access-key-id AKIA... \
       --status Inactive
   
   # Generate new access key
   aws iam create-access-key --user-name marina-email-service
   ```

2. **Investigation**:
   - Review CloudTrail logs for unauthorized activity
   - Check SES sending statistics for unusual patterns
   - Verify email reputation metrics

3. **Recovery**:
   - Update application with new credentials
   - Test email functionality
   - Delete compromised access key
   - Document incident in security log

### Emergency Contacts
- **AWS Support**: For account security issues
- **Marina IT Security**: Internal incident response
- **DNS Team**: If domain security affected

---

## 🎯 Success Criteria

**Phase 3 Complete When:**
- ✅ IAM policy created with minimal permissions
- ✅ Service user created with secure access
- ✅ Credentials generated and stored securely
- ✅ Permissions tested and validated
- ✅ Security monitoring configured
- ✅ Incident response procedures documented

**Ready for Next Phase:**
- [Application Implementation](./04-implementation.md)
- Secure credentials available for development
- Government-grade security practices in place

---

**📅 Estimated Time**: 1 hour  
**👥 Key Roles**: AWS Admin, Security Officer  
**🎯 Outcome**: Secure, compliant access configuration for Marina email system
