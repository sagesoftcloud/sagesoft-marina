# 📧 SES Sandbox Testing Guide for Marina

**Complete guide for testing Marina SES Tester in AWS SES Sandbox Mode**

---

## 🎯 **What is SES Sandbox Mode?**

AWS SES starts in **sandbox mode** by default, which means:
- ❌ **Can ONLY send** to verified email addresses
- ❌ **Cannot send** to random/unverified emails
- ✅ **Perfect for testing** without affecting real users
- ✅ **Free to use** for development and testing

---

## 🚀 **Quick Testing Setup**

### **Step 1: Verify Your Test Email Address**

1. **Login to AWS SES Console**
2. **Navigate to "Verified identities"**
3. **Click "Create identity"**
4. **Select "Email address"**
5. **Enter your personal email** (Gmail, Yahoo, Outlook, etc.)
6. **Click "Create identity"**
7. **Check your email inbox**
8. **Click the verification link** from AWS
9. **Status should change to "Verified"** ✅

### **Step 2: Configure Marina SES Tester**

1. **Open Marina app**: http://localhost:8080
2. **Login**: admin/marina123
3. **Go to Settings page**
4. **Enter your SES SMTP credentials**:
   ```
   SMTP Host: email-smtp.ap-southeast-1.amazonaws.com
   SMTP Port: 587
   SMTP Username: [Your SES SMTP Username]
   SMTP Password: [Your SES SMTP Password]
   From Email: [Your verified email address]
   From Name: Marina Portal - Testing
   ```
5. **Click "Save Configuration"**

### **Step 3: Test Email Functionality**

#### **Basic Email Test:**
1. **Go to "Basic Test" page**
2. **Enter recipient**: Your verified email address
3. **Subject**: Marina SES Test
4. **Body**: Test message from Marina SES Tester
5. **Click "Send Test Email"**
6. **Check your email inbox** ✅

#### **Template Email Test:**
1. **Go to "Template Test" page**
2. **Select**: OTP Verification template
3. **Enter recipient**: Your verified email address
4. **Fill template variables**:
   - OTP_CODE: 123456
   - CUSTOMER_NAME: Test User
5. **Click "Send Template Email"**
6. **Check your email inbox** ✅

#### **Bulk Email Test:**
1. **Go to "Bulk Test" page**
2. **Enter multiple verified emails** (one per line):
   ```
   your-email@gmail.com
   another-verified@email.com
   ```
3. **Subject**: Marina Bulk Test
4. **Body**: Bulk email test message
5. **Click "Send Bulk Emails"**
6. **Check all email inboxes** ✅

---

## 📋 **Recommended Test Email Addresses**

### **Personal Emails to Verify:**
- Your Gmail account: `yourname@gmail.com`
- Your Yahoo account: `yourname@yahoo.com`
- Your Outlook account: `yourname@outlook.com`
- Team member emails for testing

### **Marina Emails (if accessible):**
- `admin@marina.gov.ph` (if you have access)
- `test@marina.gov.ph` (if exists and accessible)
- Any Marina email you can access

---

## 🎯 **Testing Scenarios**

### **1. Basic Functionality Test**
```
✅ Send simple text email
✅ Send HTML email
✅ Verify delivery to inbox
✅ Check email formatting
```

### **2. Marina Template Test**
```
✅ OTP email with variables
✅ Transaction confirmation
✅ Official government notice
✅ Template variable replacement
```

### **3. Bulk Email Test**
```
✅ Multiple recipients
✅ Success/failure tracking
✅ Delivery status monitoring
✅ Error handling
```

### **4. Error Handling Test**
```
✅ Send to unverified email (should fail)
✅ Invalid SMTP credentials (should fail)
✅ Network connectivity issues
✅ Error message display
```

---

## 🔍 **Troubleshooting Sandbox Issues**

### **Issue 1: "Email address not verified"**
**Error**: MessageRejected: Email address not verified
**Solution**:
- Verify the recipient email in SES console
- Use only verified email addresses
- Check verification status in AWS console

### **Issue 2: "Authentication failed"**
**Error**: SMTP authentication failed
**Solution**:
- Check SMTP username/password are correct
- Verify credentials in AWS SES console
- Ensure using correct region endpoint

### **Issue 3: "From address not verified"**
**Error**: From address must be verified
**Solution**:
- Verify the "From Email" address in SES
- Use verified email as sender
- Update Marina app settings

### **Issue 4: "Daily sending quota exceeded"**
**Error**: Sending quota exceeded
**Solution**:
- Sandbox limit: 200 emails/day
- Wait 24 hours for quota reset
- Request production access for higher limits

---

## 📊 **Sandbox Limitations**

### **Current Limits:**
- **Recipients**: Only verified email addresses
- **Daily Limit**: 200 emails per day
- **Rate Limit**: 1 email per second
- **Regions**: Must use correct regional endpoint

### **What You CAN Test:**
- ✅ Email delivery functionality
- ✅ Template rendering
- ✅ SMTP connectivity
- ✅ Application interface
- ✅ Error handling
- ✅ Logging and monitoring

### **What You CANNOT Test:**
- ❌ Sending to random citizen emails
- ❌ High-volume email campaigns
- ❌ Real production scenarios
- ❌ Deliverability to all providers

---

## 🚀 **Moving to Production**

### **When Ready for Production:**
1. **Request production access** in AWS SES console
2. **Verify marina.gov.ph domain** completely
3. **Set up proper DNS records** (SPF, DKIM, DMARC)
4. **Update Marina app** to use production settings
5. **Test with real citizen emails**

### **Production Benefits:**
- ✅ Send to ANY email address
- ✅ Higher sending limits
- ✅ Better deliverability
- ✅ Full monitoring capabilities

---

## ✅ **Sandbox Testing Checklist**

**Before Testing:**
- [ ] AWS SES account created
- [ ] Personal email address verified in SES
- [ ] SMTP credentials generated
- [ ] Marina app configured with credentials
- [ ] Docker containers running

**During Testing:**
- [ ] Basic email test successful
- [ ] Template email test successful
- [ ] Bulk email test successful
- [ ] Error handling works correctly
- [ ] Logs show email activity

**After Testing:**
- [ ] All test emails received
- [ ] Templates display correctly
- [ ] Application interface works smoothly
- [ ] Ready for client demonstration
- [ ] Production access requested (if needed)

---

## 📞 **Support**

### **If You Need Help:**
1. **Check AWS SES console** for verification status
2. **Review Marina app logs** for error messages
3. **Verify SMTP credentials** are correct
4. **Test with different email addresses**
5. **Contact AWS support** for SES issues

### **Common Test Emails:**
- **Gmail**: Works well for testing
- **Yahoo**: Good for testing different providers
- **Outlook**: Microsoft email testing
- **Corporate emails**: If accessible

---

**🎯 Sandbox mode is perfect for testing all Marina SES Tester functionality safely!**

**Maritime Industry Authority**  
Republic of the Philippines

---

**📅 Created**: November 2024  
**👥 For**: Marina IT Department  
**🎯 Purpose**: Safe SES testing in sandbox environment
