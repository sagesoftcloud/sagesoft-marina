# 🔧 AWS SES Setup & Configuration
## Marina Domain Verification & Authentication

> **Phase 2**: Core SES configuration for marina.gov.ph email system with production-ready authentication.

---

## 🎯 What We're Accomplishing

**Objectives:**
- Verify marina.gov.ph domain ownership with AWS
- Enable DKIM email authentication for deliverability
- Request production access for citizen communications
- Configure bounce/complaint monitoring

**Expected Outcome**: Fully configured SES ready for email sending

---

## 📋 Step 1: Domain Verification

> **Purpose**: Prove to AWS that you own marina.gov.ph domain
> **Why Critical**: Cannot send emails from unverified domains
> **Timeline**: DNS propagation takes up to 72 hours

### Via AWS Console (Recommended)
1. **Navigate to SES**:
   - Go to [AWS SES Console](https://console.aws.amazon.com/ses/)
   - Ensure region is **ap-southeast-1** (Singapore)

2. **Create Domain Identity**:
   - Click **"Identities"** in left menu
   - Click **"Create identity"**
   - Select **"Domain"**
   - Enter: `marina.gov.ph`
   - Click **"Create identity"**

3. **Get Verification Token**:
   - Copy the TXT record value displayed
   - Record format: `_amazonses.marina.gov.ph`

4. **Add DNS Record**:
   ```
   Type: TXT
   Name: _amazonses.marina.gov.ph
   Value: "h1bGVhc2UgcmVwbGFjZSB3aXRoIGFjdHVhbCB0b2tlbg=="
   TTL: 300 (5 minutes)
   ```

### Via AWS CLI
```bash
# Verify domain
aws ses verify-domain-identity \
    --domain marina.gov.ph \
    --region ap-southeast-1

# Get verification token
aws ses get-identity-verification-attributes \
    --identities marina.gov.ph \
    --region ap-southeast-1
```

### ✅ Verification Status Check
```bash
# Check verification status (repeat until verified)
aws ses get-identity-verification-attributes \
    --identities marina.gov.ph \
    --region ap-southeast-1

# Expected output when verified:
# "VerificationStatus": "Success"
```

> **⏱️ Timeline**: DNS propagation typically takes 15 minutes to 72 hours
> **Status Check**: Domain shows "Verified" in SES console
> **Next**: Once verified, proceed to DKIM setup

---

## 🔐 Step 2: DKIM Authentication Setup

> **Purpose**: Add digital signatures to emails for authentication
> **Benefits**: Improved deliverability, reduced spam classification
> **Government Advantage**: Enhanced trust for .gov.ph emails

### Via AWS Console
1. **Access Domain Settings**:
   - Go to SES Console → Identities
   - Click on `marina.gov.ph` domain
   - Navigate to **"Authentication"** tab

2. **Enable DKIM**:
   - In "DomainKeys Identified Mail (DKIM)" section
   - Click **"Edit"**
   - Check **"Enable DKIM signing"**
   - Select **"Easy DKIM"** (recommended)
   - RSA key length: **2048-bit**
   - Click **"Save changes"**

3. **Get DKIM Records**:
   - Copy the 3 CNAME records displayed
   - Format: `token._domainkey.marina.gov.ph`

4. **Add DNS Records**:
   ```
   Type: CNAME
   Name: abc123._domainkey.marina.gov.ph
   Value: abc123.dkim.amazonses.com
   TTL: 300

   Type: CNAME  
   Name: def456._domainkey.marina.gov.ph
   Value: def456.dkim.amazonses.com
   TTL: 300

   Type: CNAME
   Name: ghi789._domainkey.marina.gov.ph  
   Value: ghi789.dkim.amazonses.com
   TTL: 300
   ```

### Via AWS CLI
```bash
# Enable DKIM
aws ses put-identity-dkim-attributes \
    --identity marina.gov.ph \
    --dkim-enabled \
    --region ap-southeast-1

# Get DKIM tokens
aws ses get-identity-dkim-attributes \
    --identities marina.gov.ph \
    --region ap-southeast-1
```

### ✅ DKIM Status Check
```bash
# Verify DKIM status
aws ses get-identity-dkim-attributes \
    --identities marina.gov.ph \
    --region ap-southeast-1

# Expected output when successful:
# "DkimEnabled": true,
# "DkimVerificationStatus": "Success"
```

> **Status Check**: DKIM shows "Successful" in Authentication tab
> **What's Accomplished**: Emails will now have digital signatures
> **Next**: Request production access for citizen communications

---

## 🚀 Step 3: Production Access Request

> **Current Limitation**: Account in sandbox mode - can only email verified addresses
> **What We Need**: Production access to email any citizen/stakeholder
> **Government Advantage**: Faster approval for legitimate government use

### Submit Production Request (Console Only)
1. **Navigate to Account Dashboard**:
   - Go to SES Console → **"Account dashboard"**
   - Click **"Request production access"** button

2. **Fill Request Form**:
   ```
   Mail Type: Transactional
   Website URL: https://marina.gov.ph
   
   Use Case Description:
   Government email notifications for Marina (Maritime Industry Authority) Philippines including:
   
   • OTP authentication for citizen portal access and maritime services
   • Transaction confirmations for vessel registration and permits  
   • Regulatory notifications to vessel operators and maritime businesses
   • Emergency alerts for maritime safety and weather warnings
   • Official communications to stakeholders and industry partners
   • System notifications for online government services
   • Password reset and account verification emails
   
   As a government agency, we need to communicate with Filipino citizens and maritime industry stakeholders who cannot pre-verify their email addresses with AWS. Our email communications are essential for:
   - Public safety in maritime operations
   - Regulatory compliance in the shipping industry
   - Citizen access to government services  
   - Emergency response coordination
   
   Expected Volume: 10,000-50,000 emails per month
   Primary Use: Critical transactional notifications for government services
   Compliance: Data Privacy Act of 2012 (Philippines)
   ```

3. **Additional Information**:
   - **Contact Email**: Add backup government email
   - **Preferred Region**: ap-southeast-1 (Singapore)
   - **Process Description**: Describe email sending process

4. **Submit Request**:
   - Review all information
   - Click **"Submit request"**
   - Note ticket number for reference

### ⏱️ Approval Timeline
- **Government Requests**: 24-48 hours typical
- **Approval Notification**: Email to account owner
- **Status Check**: Account dashboard shows "Production"

### Check Current Status
```bash
# Check sending quota (sandbox = 200/day, production = much higher)
aws ses get-send-quota --region ap-southeast-1

# Check account status
aws ses get-account-sending-enabled --region ap-southeast-1
```

> **While Waiting**: Continue with setup - you can test with verified addresses
> **After Approval**: Can send to any email address immediately
> **Next**: Set up bounce and complaint monitoring

---

## 📊 Step 4: Bounce & Complaint Monitoring

> **Purpose**: Track email delivery issues to maintain sender reputation
> **Critical**: High bounce/complaint rates can blacklist your domain
> **Requirement**: AWS monitors these metrics for all accounts

### Create SNS Topic (Console)
1. **Navigate to SNS**:
   - Go to [Amazon SNS Console](https://console.aws.amazon.com/sns/)
   - Ensure region is **ap-southeast-1**

2. **Create Topic**:
   - Click **"Create topic"**
   - Type: **Standard**
   - Name: `ses-bounces-marina`
   - Display name: `Marina SES Notifications`
   - Click **"Create topic"**

3. **Copy Topic ARN**:
   - Note the ARN: `arn:aws:sns:ap-southeast-1:ACCOUNT-ID:ses-bounces-marina`

### Configure SES Notifications (Console)
1. **Return to SES Console**:
   - Go to Identities → `marina.gov.ph`
   - Click **"Notifications"** tab

2. **Configure Bounce Notifications**:
   - Click **"Edit"** for Bounce notifications
   - Enable: ✅
   - SNS topic: Select `ses-bounces-marina`
   - Include original headers: ✅
   - Click **"Save changes"**

3. **Configure Complaint Notifications**:
   - Click **"Edit"** for Complaint notifications  
   - Enable: ✅
   - SNS topic: Select `ses-bounces-marina`
   - Include original headers: ✅
   - Click **"Save changes"**

### Via AWS CLI
```bash
# Create SNS topic
aws sns create-topic \
    --name ses-bounces-marina \
    --region ap-southeast-1

# Configure bounce notifications
aws ses put-identity-notification-attributes \
    --identity marina.gov.ph \
    --notification-type Bounce \
    --sns-topic arn:aws:sns:ap-southeast-1:ACCOUNT-ID:ses-bounces-marina \
    --region ap-southeast-1

# Configure complaint notifications  
aws ses put-identity-notification-attributes \
    --identity marina.gov.ph \
    --notification-type Complaint \
    --sns-topic arn:aws:sns:ap-southeast-1:ACCOUNT-ID:ses-bounces-marina \
    --region ap-southeast-1
```

### ✅ Monitoring Status Check
```bash
# Verify notification configuration
aws ses get-identity-notification-attributes \
    --identities marina.gov.ph \
    --region ap-southeast-1
```

> **Status Check**: Notifications tab shows SNS topics configured
> **What's Accomplished**: Automated monitoring of email reputation
> **Next**: Set up IAM permissions for application access

---

## 📧 Step 5: Test Email Sending (Sandbox)

> **Current State**: Can test with verified email addresses only
> **Purpose**: Verify configuration works before production approval
> **Test Strategy**: Use team email addresses for validation

### Verify Test Email Address
```bash
# Verify your test email for sandbox testing
aws ses verify-email-identity \
    --email-address admin@marina.gov.ph \
    --region ap-southeast-1

# Check verification status
aws ses get-identity-verification-attributes \
    --identities admin@marina.gov.ph \
    --region ap-southeast-1
```

### Send Test Email (Console)
1. **Navigate to SES Console**:
   - Go to Identities → `marina.gov.ph`
   - Click **"Send test email"**

2. **Configure Test Email**:
   - From address: `noreply@marina.gov.ph`
   - To address: `admin@marina.gov.ph` (verified)
   - Subject: `Marina SES Test - Configuration Verification`
   - Body:
     ```
     This is a test email from Marina's AWS SES configuration.
     
     If you receive this email:
     ✅ Domain verification is working
     ✅ DKIM authentication is active
     ✅ Email sending functionality is operational
     
     Next steps:
     - Await production access approval
     - Complete application integration
     - Begin citizen testing
     
     Marina IT Department
     ```

3. **Send and Verify**:
   - Click **"Send test email"**
   - Check recipient inbox (including spam folder)
   - Verify sender shows as marina.gov.ph
   - Check email headers for DKIM signature

### Send Test Email (CLI)
```bash
# Send basic test email
aws ses send-email \
    --source "noreply@marina.gov.ph" \
    --destination "ToAddresses=admin@marina.gov.ph" \
    --message "Subject={Data=Marina SES Test},Body={Text={Data=Configuration test successful}}" \
    --region ap-southeast-1
```

### ✅ Test Results Verification
**Email Should Show:**
- ✅ Sender: noreply@marina.gov.ph
- ✅ No security warnings
- ✅ DKIM signature in headers
- ✅ Professional appearance
- ✅ Delivered to inbox (not spam)

> **If Test Fails**: Check domain verification and DKIM status
> **If Goes to Spam**: Verify DKIM setup and DNS records
> **Success Criteria**: Clean delivery with proper authentication

---

## 📋 Configuration Summary

### ✅ Completed Setup Checklist
- [ ] **Domain Verified**: marina.gov.ph shows "Verified" status
- [ ] **DKIM Enabled**: 3 CNAME records added and verified
- [ ] **Production Requested**: Government use case submitted
- [ ] **Monitoring Configured**: Bounce/complaint notifications active
- [ ] **Test Email Sent**: Successful delivery to verified address

### 🔍 Status Verification Commands
```bash
# Complete status check
aws ses get-identity-verification-attributes --identities marina.gov.ph --region ap-southeast-1
aws ses get-identity-dkim-attributes --identities marina.gov.ph --region ap-southeast-1  
aws ses get-send-quota --region ap-southeast-1
aws ses get-account-sending-enabled --region ap-southeast-1
```

### 📊 Expected Results
```json
{
  "VerificationAttributes": {
    "marina.gov.ph": {
      "VerificationStatus": "Success",
      "VerificationToken": "..."
    }
  },
  "DkimAttributes": {
    "marina.gov.ph": {
      "DkimEnabled": true,
      "DkimVerificationStatus": "Success"
    }
  }
}
```

---

## 🚨 Troubleshooting Common Issues

### Domain Verification Fails
**Symptoms**: Domain stuck in "Pending" status
**Solutions**:
- Verify DNS record syntax exactly matches AWS requirements
- Check TTL is not too high (use 300 seconds)
- Wait up to 72 hours for DNS propagation
- Use DNS lookup tools to verify record exists

### DKIM Not Verifying  
**Symptoms**: DKIM shows "Failed" or "Pending"
**Solutions**:
- Ensure all 3 CNAME records are added correctly
- Verify no typos in record names or values
- Check DNS propagation with online tools
- Contact DNS team to verify record creation

### Production Request Delayed
**Symptoms**: No response after 48 hours
**Solutions**:
- Check spam folder for AWS emails
- Review use case description for completeness
- Contact AWS Support with ticket number
- Ensure government domain is clearly identified

---

## 🎯 Success Criteria

**Phase 2 Complete When:**
- ✅ Domain verification successful
- ✅ DKIM authentication active  
- ✅ Production access approved (or pending)
- ✅ Monitoring configured
- ✅ Test email delivered successfully

**Ready for Next Phase:**
- [Security & IAM Setup](./03-security-iam.md)
- Application development can begin
- Team can test with verified addresses

---

**📅 Estimated Time**: 2-3 hours (plus DNS propagation wait)  
**👥 Key Roles**: AWS Admin, DNS Team  
**🎯 Outcome**: Production-ready SES configuration for Marina email system
