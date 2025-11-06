# 🚨 AWS SES Sandbox vs Production Mode
## Critical Information for Marina Email System

> **⚠️ MANDATORY READ**: Understanding sandbox limitations is crucial for planning your email system deployment.

---

## 🔒 What is Sandbox Mode?

**Default State**: All new AWS SES accounts start in sandbox mode for security and spam prevention.

**Sandbox Restrictions:**
- ❌ Can only send TO verified email addresses
- ❌ Maximum 200 emails per 24-hour period  
- ❌ Maximum 1 email per second sending rate
- ❌ Cannot send to citizens/public (unverified addresses)

---

## 🚫 Why You CANNOT Bypass Sandbox

**No Workarounds Available:**
- Cannot be disabled via CLI/API
- No configuration setting to bypass
- No "developer mode" or testing override
- AWS security policy - no exceptions

**Security Reasoning:**
- Prevents spam and abuse from new accounts
- Protects AWS reputation with email providers
- Ensures legitimate use cases only
- Government accounts get same treatment initially

---

## 📝 Production Access Request (MANDATORY)

### 🏛️ For Marina Government Use Case

**Request Details to Use:**
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

### ⏱️ Timeline Expectations

**Government Advantage:**
- Typical approval: 24-48 hours (faster than commercial)
- Maritime safety use case: Strong justification
- Government domain (.gov.ph): Credibility boost
- Clear public service purpose: Quick approval

**Approval Process:**
1. Submit via AWS Console (only method)
2. AWS reviews use case description
3. Email notification of approval
4. Immediate production access activation

---

## ✅ What You Can Do During Sandbox

### 🧪 Complete Setup & Testing

**Verify Test Addresses:**
```bash
# Verify your team's email addresses for testing
aws ses verify-email-identity --email-address admin@marina.gov.ph
aws ses verify-email-identity --email-address developer@marina.gov.ph
aws ses verify-email-identity --email-address test@marina.gov.ph
```

**Full System Setup:**
- ✅ Domain verification (marina.gov.ph)
- ✅ DKIM configuration and DNS records
- ✅ IAM policies and service users
- ✅ Application code development
- ✅ Email template creation and testing
- ✅ Monitoring and alerting setup
- ✅ Load testing (to verified addresses)

**Test All Functionality:**
```python
# Test with verified addresses during sandbox
service = EmailNotificationService()

# Test OTP email
result = service.send_otp_email("test@marina.gov.ph", "123456")

# Test transaction alert  
alert_data = {'type': 'Test', 'amount': 'PHP 100', 'reference': 'TEST123'}
result = service.send_transaction_alert("admin@marina.gov.ph", alert_data)
```

---

## 🚀 After Production Approval

### 📈 New Capabilities

**Sending Limits:**
- ✅ Send to ANY email address (citizens, businesses, etc.)
- ✅ 200 emails per second (can request increase)
- ✅ 200,000 emails per day (can request increase)
- ✅ No recipient verification required

**Immediate Benefits:**
- Real citizen testing possible
- Full OTP functionality for public
- Marketing emails to stakeholders
- Emergency alerts to all maritime operators

### 🔧 Limit Increases (If Needed)

**Request Higher Limits:**
```bash
# Check current limits after production approval
aws ses get-send-quota
aws ses get-send-statistics

# Request increases via AWS Support if needed:
# - Up to 1,000 emails/second
# - Up to 1,000,000 emails/day
```

---

## 📋 Action Plan for Marina

### Phase 1: Immediate (During Sandbox)
1. **Submit Production Request**: Use provided description above
2. **Complete Technical Setup**: Domain, DKIM, IAM, code
3. **Test with Verified Addresses**: Ensure everything works
4. **Prepare for Production**: Documentation, monitoring

### Phase 2: After Approval (24-48 hours)
1. **Verify Production Status**: Check sending quota
2. **Test Real Citizen Emails**: Send to unverified addresses
3. **Deploy to Production**: Enable for live traffic
4. **Monitor Closely**: Watch bounce/complaint rates

### Phase 3: Optimization
1. **Request Limit Increases**: If volume exceeds defaults
2. **Optimize Delivery**: Monitor reputation metrics
3. **Scale as Needed**: Add more email types/templates

---

## 🚨 Critical Reminders

**Cannot Launch Without Production Access:**
- Sandbox mode = testing only
- Citizens cannot receive emails in sandbox
- OTP system won't work for public users
- Government services will be non-functional

**Plan Timeline Accordingly:**
- Submit request early in project
- Continue development during review
- Don't schedule public launch until approved
- Have backup communication plan ready

**Success Metrics After Approval:**
- 99%+ delivery rate to major providers
- <5% bounce rate (excellent reputation)
- <0.1% complaint rate (AWS requirement)
- Sub-second delivery for OTP emails

---

**📞 Need Help?**
- AWS Support: Technical issues with request
- [AWS SES Console](https://console.aws.amazon.com/ses/): Submit production request
- [Troubleshooting Guide](./troubleshooting.md): Common issues

**🎯 Goal**: Production-ready email system serving Filipino citizens and maritime industry stakeholders reliably and securely.
