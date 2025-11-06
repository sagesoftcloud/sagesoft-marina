# 🧪 Testing & Validation
## Comprehensive Testing for Marina Email System

> **Phase 5**: Validate email functionality, deliverability, and performance before production deployment.

---

## 🎯 Testing Objectives

**Goals:**
- Verify email delivery to major providers (Gmail, Yahoo, Outlook)
- Test all email templates with real-world scenarios
- Validate rate limiting and security measures
- Ensure performance meets government service requirements
- Confirm DKIM authentication and reputation metrics

**Testing Strategy**: Progressive testing from basic functionality to full load scenarios

---

## 📧 Step 1: Basic Email Delivery Testing

> **Purpose**: Verify core SES functionality and domain configuration
> **What to Test**: Basic email sending, DKIM signatures, deliverability
> **Success Criteria**: Emails deliver to inbox with proper authentication

### Console-Based Testing
1. **Navigate to SES Console**:
   - Go to AWS SES Console → Identities
   - Click on `marina.gov.ph` domain
   - Click **"Send test email"**

2. **Test Configuration**:
   ```
   From: noreply@marina.gov.ph
   To: your-test-email@gmail.com
   Subject: Marina SES Configuration Test
   Body: 
   This is a test email from Marina's AWS SES setup.
   
   Testing checklist:
   ✓ Domain verification: marina.gov.ph
   ✓ DKIM authentication: Enabled
   ✓ Email delivery: Working
   
   If you receive this email with proper sender authentication,
   the Marina email system is configured correctly.
   
   Maritime Industry Authority
   Republic of the Philippines
   ```

3. **Verification Steps**:
   - Check email arrives in inbox (not spam)
   - Verify sender shows as "noreply@marina.gov.ph"
   - Check email headers for DKIM signature
   - Confirm no security warnings

### CLI Testing
```bash
# Test basic email delivery
aws ses send-email \
    --source "noreply@marina.gov.ph" \
    --destination "ToAddresses=test@gmail.com" \
    --message "Subject={Data=Marina Basic Test},Body={Text={Data=Basic email delivery test from Marina SES}}" \
    --region ap-southeast-1

# Test HTML email
cat > test-html-email.json << 'EOF'
{
    "Subject": {
        "Data": "Marina HTML Email Test",
        "Charset": "UTF-8"
    },
    "Body": {
        "Text": {
            "Data": "This is the text version of Marina's test email.",
            "Charset": "UTF-8"
        },
        "Html": {
            "Data": "<html><body><h1>Marina Email Test</h1><p>This is an <strong>HTML test email</strong> from Marina's SES configuration.</p><p>Maritime Industry Authority<br>Republic of the Philippines</p></body></html>",
            "Charset": "UTF-8"
        }
    }
}
EOF

aws ses send-email \
    --source "noreply@marina.gov.ph" \
    --destination "ToAddresses=test@gmail.com" \
    --message file://test-html-email.json \
    --region ap-southeast-1
```

### ✅ Basic Test Verification
**Expected Results:**
- ✅ Email delivered within 30 seconds
- ✅ Sender authentication passes
- ✅ DKIM signature present in headers
- ✅ No spam folder placement
- ✅ Professional appearance

> **If Tests Fail**: Check domain verification, DKIM setup, and production access status
> **Next**: Test application email templates

---

## 🏛️ Step 2: Government Template Testing

> **Purpose**: Validate Marina-specific email templates work correctly
> **Focus**: OTP emails, transaction alerts, official communications
> **Government Standards**: Professional appearance, clear messaging, proper branding

### OTP Email Testing
```python
# Test OTP email functionality
from marina_email_service import MarinaEmailService

def test_otp_templates():
    """Test OTP email templates for different Marina services"""
    service = MarinaEmailService()
    
    test_cases = [
        {
            'service': 'Marina Vessel Registration',
            'otp': '123456',
            'email': 'test@gmail.com'
        },
        {
            'service': 'Port Clearance System', 
            'otp': '789012',
            'email': 'test@yahoo.com'
        },
        {
            'service': 'Maritime License Renewal',
            'otp': '345678', 
            'email': 'test@outlook.com'
        }
    ]
    
    results = []
    for case in test_cases:
        result = service.send_otp_email(
            to_email=case['email'],
            otp_code=case['otp'],
            service_name=case['service']
        )
        results.append({
            'service': case['service'],
            'success': result['success'],
            'message_id': result.get('message_id'),
            'error': result.get('error')
        })
        
        # Wait between sends to respect rate limits
        time.sleep(2)
    
    # Print results
    print("OTP Email Test Results:")
    for result in results:
        status = "✅ PASS" if result['success'] else "❌ FAIL"
        print(f"{result['service']}: {status}")
        if not result['success']:
            print(f"  Error: {result['error']}")
    
    return all(r['success'] for r in results)

# Run OTP tests
if __name__ == "__main__":
    otp_success = test_otp_templates()
    print(f"\nOTP Template Tests: {'PASSED' if otp_success else 'FAILED'}")
```

### Transaction Alert Testing
```python
def test_transaction_alerts():
    """Test transaction alert emails for Marina services"""
    service = MarinaEmailService()
    
    test_transactions = [
        {
            'type': 'Vessel Registration Fee',
            'amount': 'PHP 2,500.00',
            'reference': 'MRN-VR-2024-001',
            'date': 'January 6, 2024 at 2:30 PM',
            'status': 'Completed',
            'email': 'vessel.owner@gmail.com'
        },
        {
            'type': 'Port Clearance Fee',
            'amount': 'PHP 1,200.00', 
            'reference': 'MRN-PC-2024-002',
            'date': 'January 6, 2024 at 3:15 PM',
            'status': 'Completed',
            'email': 'shipping.company@yahoo.com'
        },
        {
            'type': 'Maritime License Renewal',
            'amount': 'PHP 3,000.00',
            'reference': 'MRN-LR-2024-003', 
            'date': 'January 6, 2024 at 4:00 PM',
            'status': 'Processing',
            'email': 'seafarer@outlook.com'
        }
    ]
    
    results = []
    for transaction in test_transactions:
        email = transaction.pop('email')  # Remove email from transaction data
        
        result = service.send_transaction_alert(
            to_email=email,
            transaction_data=transaction
        )
        
        results.append({
            'type': transaction['type'],
            'success': result['success'],
            'message_id': result.get('message_id'),
            'error': result.get('error')
        })
        
        time.sleep(2)  # Rate limit compliance
    
    # Print results
    print("Transaction Alert Test Results:")
    for result in results:
        status = "✅ PASS" if result['success'] else "❌ FAIL"
        print(f"{result['type']}: {status}")
        if not result['success']:
            print(f"  Error: {result['error']}")
    
    return all(r['success'] for r in results)

# Run transaction tests
transaction_success = test_transaction_alerts()
print(f"Transaction Alert Tests: {'PASSED' if transaction_success else 'FAILED'}")
```

### ✅ Template Test Verification
**Check Each Email For:**
- ✅ Professional Marina branding and colors
- ✅ Clear, government-appropriate language
- ✅ Proper contact information (support@marina.gov.ph)
- ✅ Mobile-friendly responsive design
- ✅ All dynamic content renders correctly
- ✅ Security warnings for OTP emails
- ✅ Transaction details formatted clearly

> **Manual Review**: Have Marina staff review email appearance and tone
> **Feedback Integration**: Update templates based on government communication standards

---

## 🚀 Step 3: Performance & Load Testing

> **Purpose**: Ensure system handles expected email volumes without issues
> **Government Requirement**: Reliable service during peak citizen usage
> **Test Scenarios**: Normal load, peak load, rate limiting validation

### Load Testing Script
```python
import asyncio
import time
from concurrent.futures import ThreadPoolExecutor
from marina_email_service import MarinaEmailService

async def load_test_marina_emails():
    """Load test Marina email system with realistic scenarios"""
    service = MarinaEmailService()
    
    # Test parameters based on Marina's expected usage
    test_scenarios = {
        'otp_emails': {
            'count': 100,
            'max_workers': 5,  # Respect rate limits
            'delay_between_batches': 1
        },
        'transaction_alerts': {
            'count': 50,
            'max_workers': 3,
            'delay_between_batches': 2
        }
    }
    
    print("Starting Marina Email Load Test...")
    print(f"Simulating peak citizen usage scenarios")
    
    start_time = time.time()
    all_results = []
    
    # Test OTP email load
    print("\n1. Testing OTP Email Load (Citizen Login Peak)...")
    with ThreadPoolExecutor(max_workers=test_scenarios['otp_emails']['max_workers']) as executor:
        otp_futures = []
        
        for i in range(test_scenarios['otp_emails']['count']):
            future = executor.submit(
                service.send_otp_email,
                f"citizen{i}@testdomain.com",  # Use test domain
                f"{100000 + i}",  # Sequential OTP codes
                "Marina Citizen Portal"
            )
            otp_futures.append(future)
            
            # Add delay every 5 emails to respect rate limits
            if (i + 1) % 5 == 0:
                time.sleep(test_scenarios['otp_emails']['delay_between_batches'])
        
        otp_results = [future.result() for future in otp_futures]
        all_results.extend(otp_results)
    
    # Test transaction alert load
    print("\n2. Testing Transaction Alert Load (Business Hours Peak)...")
    with ThreadPoolExecutor(max_workers=test_scenarios['transaction_alerts']['max_workers']) as executor:
        alert_futures = []
        
        for i in range(test_scenarios['transaction_alerts']['count']):
            transaction_data = {
                'type': 'Vessel Registration Fee',
                'amount': f'PHP {2500 + (i * 100)}.00',
                'reference': f'MRN-LOAD-{i:04d}',
                'date': 'January 6, 2024 at 2:30 PM',
                'status': 'Completed'
            }
            
            future = executor.submit(
                service.send_transaction_alert,
                f"business{i}@testdomain.com",
                transaction_data
            )
            alert_futures.append(future)
            
            # Add delay every 3 emails
            if (i + 1) % 3 == 0:
                time.sleep(test_scenarios['transaction_alerts']['delay_between_batches'])
        
        alert_results = [future.result() for future in alert_futures]
        all_results.extend(alert_results)
    
    end_time = time.time()
    
    # Analyze results
    total_emails = len(all_results)
    successful_emails = sum(1 for r in all_results if r['success'])
    failed_emails = total_emails - successful_emails
    success_rate = (successful_emails / total_emails) * 100
    duration = end_time - start_time
    emails_per_second = successful_emails / duration
    
    print(f"\n📊 Marina Email Load Test Results:")
    print(f"Duration: {duration:.2f} seconds")
    print(f"Total Emails: {total_emails}")
    print(f"Successful: {successful_emails}")
    print(f"Failed: {failed_emails}")
    print(f"Success Rate: {success_rate:.1f}%")
    print(f"Throughput: {emails_per_second:.2f} emails/second")
    
    # Performance benchmarks for government service
    print(f"\n🎯 Performance Assessment:")
    if success_rate >= 95:
        print("✅ Success Rate: EXCELLENT (≥95%)")
    elif success_rate >= 90:
        print("⚠️ Success Rate: ACCEPTABLE (90-95%)")
    else:
        print("❌ Success Rate: NEEDS IMPROVEMENT (<90%)")
    
    if emails_per_second <= 10:  # Well within SES limits
        print("✅ Rate Compliance: EXCELLENT (within SES limits)")
    else:
        print("⚠️ Rate Compliance: CHECK (approaching SES limits)")
    
    # Check for rate limit errors
    rate_limit_errors = sum(1 for r in all_results if not r['success'] and 'rate limit' in r.get('error', '').lower())
    if rate_limit_errors == 0:
        print("✅ Rate Limiting: WORKING (no rate limit errors)")
    else:
        print(f"⚠️ Rate Limiting: {rate_limit_errors} rate limit errors detected")
    
    return {
        'success_rate': success_rate,
        'emails_per_second': emails_per_second,
        'total_duration': duration,
        'rate_limit_errors': rate_limit_errors
    }

# Run load test
if __name__ == "__main__":
    results = asyncio.run(load_test_marina_emails())
```

### Rate Limiting Validation
```python
def test_rate_limiting():
    """Test that rate limiting protects against abuse"""
    service = MarinaEmailService()
    
    print("Testing Rate Limiting Protection...")
    
    # Test OTP rate limiting (5 per 5 minutes per email)
    test_email = "ratelimit.test@example.com"
    otp_results = []
    
    print("Sending 7 OTP emails rapidly (should block after 5)...")
    for i in range(7):
        result = service.send_otp_email(test_email, f"12345{i}")
        otp_results.append(result)
        print(f"OTP {i+1}: {'✅ Sent' if result['success'] else '❌ Blocked'}")
    
    # Count successful vs blocked
    successful_otps = sum(1 for r in otp_results if r['success'])
    blocked_otps = len(otp_results) - successful_otps
    
    print(f"\nRate Limiting Results:")
    print(f"Successful OTPs: {successful_otps}/7 (should be 5)")
    print(f"Blocked OTPs: {blocked_otps}/7 (should be 2)")
    
    # Verify rate limiting is working
    if successful_otps == 5 and blocked_otps == 2:
        print("✅ Rate Limiting: WORKING CORRECTLY")
        return True
    else:
        print("❌ Rate Limiting: NOT WORKING PROPERLY")
        return False

# Test rate limiting
rate_limit_success = test_rate_limiting()
```

### ✅ Performance Test Verification
**Success Criteria:**
- ✅ Success rate ≥95% for all email types
- ✅ No rate limit violations (stays under 14 emails/second)
- ✅ Rate limiting blocks excessive requests correctly
- ✅ Average delivery time <30 seconds for OTP emails
- ✅ System handles 150+ emails in test scenario

> **Government Standards**: System must handle peak citizen usage reliably
> **Scalability**: Results indicate capacity for Marina's expected growth

---

## 🌐 Step 4: Multi-Provider Deliverability Testing

> **Purpose**: Ensure emails reach citizens using different email providers
> **Critical**: Filipino citizens use various email services
> **Test Coverage**: Gmail, Yahoo, Outlook, local Philippine providers

### Provider-Specific Testing
```python
def test_email_providers():
    """Test email delivery across major providers used in Philippines"""
    service = MarinaEmailService()
    
    # Major email providers used by Filipino citizens
    test_providers = [
        {'provider': 'Gmail', 'email': 'test.marina@gmail.com'},
        {'provider': 'Yahoo', 'email': 'test.marina@yahoo.com'},
        {'provider': 'Outlook', 'email': 'test.marina@outlook.com'},
        {'provider': 'Hotmail', 'email': 'test.marina@hotmail.com'},
    ]
    
    results = []
    
    print("Testing Email Delivery Across Providers...")
    
    for provider_info in test_providers:
        provider = provider_info['provider']
        email = provider_info['email']
        
        print(f"\nTesting {provider}...")
        
        # Test OTP email
        otp_result = service.send_otp_email(
            to_email=email,
            otp_code="123456",
            service_name="Marina Provider Test"
        )
        
        # Test transaction alert
        transaction_data = {
            'type': 'Provider Test Transaction',
            'amount': 'PHP 1,000.00',
            'reference': f'TEST-{provider.upper()}-001',
            'date': 'January 6, 2024 at 3:00 PM',
            'status': 'Completed'
        }
        
        alert_result = service.send_transaction_alert(
            to_email=email,
            transaction_data=transaction_data
        )
        
        results.append({
            'provider': provider,
            'otp_success': otp_result['success'],
            'alert_success': alert_result['success'],
            'otp_message_id': otp_result.get('message_id'),
            'alert_message_id': alert_result.get('message_id')
        })
        
        time.sleep(3)  # Delay between providers
    
    # Print results
    print(f"\n📊 Provider Deliverability Results:")
    print(f"{'Provider':<10} {'OTP':<8} {'Alert':<8} {'Status'}")
    print("-" * 40)
    
    for result in results:
        otp_status = "✅ Pass" if result['otp_success'] else "❌ Fail"
        alert_status = "✅ Pass" if result['alert_success'] else "❌ Fail"
        overall = "✅ Good" if result['otp_success'] and result['alert_success'] else "⚠️ Issue"
        
        print(f"{result['provider']:<10} {otp_status:<8} {alert_status:<8} {overall}")
    
    # Overall assessment
    total_tests = len(results) * 2  # OTP + Alert per provider
    successful_tests = sum(
        (1 if r['otp_success'] else 0) + (1 if r['alert_success'] else 0) 
        for r in results
    )
    
    success_rate = (successful_tests / total_tests) * 100
    print(f"\nOverall Provider Success Rate: {success_rate:.1f}%")
    
    return success_rate >= 90  # 90% minimum for government service

# Run provider tests
provider_success = test_email_providers()
print(f"Provider Deliverability: {'PASSED' if provider_success else 'NEEDS ATTENTION'}")
```

### Manual Deliverability Checks
**For Each Provider, Verify:**
1. **Inbox Placement**: Email arrives in inbox, not spam
2. **Sender Authentication**: No security warnings
3. **Template Rendering**: HTML displays correctly
4. **Mobile Compatibility**: Readable on mobile devices
5. **Link Functionality**: All links work properly
6. **Unsubscribe Compliance**: Marketing emails have unsubscribe (if applicable)

---

## 📋 Step 5: Complete System Integration Test

> **Purpose**: End-to-end testing simulating real Marina citizen interactions
> **Scenario**: Complete user journey from registration to transaction completion
> **Validation**: All email touchpoints work seamlessly

### Integration Test Scenario
```python
def test_citizen_journey():
    """Simulate complete citizen interaction with Marina services"""
    service = MarinaEmailService()
    
    print("🏛️ Testing Complete Citizen Journey...")
    
    # Scenario: Citizen registering vessel and completing payment
    citizen_email = "juan.delacruz@gmail.com"
    
    journey_steps = []
    
    # Step 1: Account verification OTP
    print("Step 1: Citizen creates account - OTP verification")
    otp1_result = service.send_otp_email(
        to_email=citizen_email,
        otp_code="123456",
        service_name="Marina Account Registration"
    )
    journey_steps.append(("Account OTP", otp1_result['success']))
    time.sleep(2)
    
    # Step 2: Vessel registration OTP
    print("Step 2: Citizen starts vessel registration - OTP verification")
    otp2_result = service.send_otp_email(
        to_email=citizen_email,
        otp_code="789012", 
        service_name="Marina Vessel Registration"
    )
    journey_steps.append(("Registration OTP", otp2_result['success']))
    time.sleep(2)
    
    # Step 3: Payment confirmation
    print("Step 3: Citizen completes payment - Transaction alert")
    payment_data = {
        'type': 'Vessel Registration Fee',
        'amount': 'PHP 2,500.00',
        'reference': 'MRN-VR-2024-JDC001',
        'date': 'January 6, 2024 at 2:30 PM',
        'status': 'Completed'
    }
    
    payment_result = service.send_transaction_alert(
        to_email=citizen_email,
        transaction_data=payment_data
    )
    journey_steps.append(("Payment Confirmation", payment_result['success']))
    time.sleep(2)
    
    # Step 4: Document ready notification (using basic email)
    print("Step 4: Documents ready - Notification email")
    doc_result = service.send_email(
        to_email=citizen_email,
        subject="Marina - Your Vessel Registration Documents are Ready",
        body_text="""
Dear Vessel Owner,

Your vessel registration documents are now ready for download.

Registration Reference: MRN-VR-2024-JDC001
Vessel Name: M/V Juan's Pride
Registration Type: Commercial Vessel

You can download your documents at:
https://marina.gov.ph/documents/download

Documents expire 30 days from this notice.

Maritime Industry Authority
Republic of the Philippines
        """,
        body_html="""
        <html>
        <body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <div style="background-color: #059669; color: white; padding: 20px; text-align: center;">
                <h1 style="margin: 0;">MARITIME INDUSTRY AUTHORITY</h1>
                <p style="margin: 5px 0 0 0;">Document Ready Notification</p>
            </div>
            <div style="padding: 30px 20px;">
                <h2 style="color: #059669;">Your Documents are Ready!</h2>
                <p>Your vessel registration documents are now ready for download.</p>
                <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; margin: 20px 0;">
                    <p><strong>Registration Reference:</strong> MRN-VR-2024-JDC001</p>
                    <p><strong>Vessel Name:</strong> M/V Juan's Pride</p>
                    <p><strong>Registration Type:</strong> Commercial Vessel</p>
                </div>
                <p><a href="https://marina.gov.ph/documents/download" style="background-color: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;">Download Documents</a></p>
                <p><small>Documents expire 30 days from this notice.</small></p>
            </div>
        </body>
        </html>
        """
    )
    journey_steps.append(("Document Notification", doc_result['success']))
    
    # Analyze journey results
    print(f"\n📊 Citizen Journey Test Results:")
    print(f"{'Step':<25} {'Status'}")
    print("-" * 35)
    
    for step_name, success in journey_steps:
        status = "✅ Success" if success else "❌ Failed"
        print(f"{step_name:<25} {status}")
    
    successful_steps = sum(1 for _, success in journey_steps if success)
    total_steps = len(journey_steps)
    journey_success_rate = (successful_steps / total_steps) * 100
    
    print(f"\nJourney Success Rate: {journey_success_rate:.1f}%")
    
    if journey_success_rate == 100:
        print("✅ EXCELLENT: Complete citizen journey works perfectly")
    elif journey_success_rate >= 75:
        print("⚠️ ACCEPTABLE: Most journey steps work, minor issues to resolve")
    else:
        print("❌ NEEDS WORK: Significant issues in citizen journey")
    
    return journey_success_rate >= 75

# Run integration test
integration_success = test_citizen_journey()
```

---

## ✅ Testing Summary & Validation

### Complete Testing Checklist
- [ ] **Basic Delivery**: Emails reach inbox with proper authentication
- [ ] **Template Testing**: All Marina email templates work correctly
- [ ] **Performance**: System handles expected load (95%+ success rate)
- [ ] **Rate Limiting**: Protection against abuse works correctly
- [ ] **Multi-Provider**: Delivery works across Gmail, Yahoo, Outlook
- [ ] **Integration**: Complete citizen journey flows work end-to-end
- [ ] **Mobile Testing**: Emails display correctly on mobile devices
- [ ] **Security**: DKIM signatures present, no security warnings

### Final Validation Script
```python
def run_complete_test_suite():
    """Run all Marina email system tests"""
    print("🧪 Marina Email System - Complete Test Suite")
    print("=" * 50)
    
    test_results = {}
    
    # Run all test categories
    test_results['basic_delivery'] = test_basic_delivery()
    test_results['otp_templates'] = test_otp_templates()
    test_results['transaction_alerts'] = test_transaction_alerts()
    test_results['rate_limiting'] = test_rate_limiting()
    test_results['provider_delivery'] = test_email_providers()
    test_results['citizen_journey'] = test_citizen_journey()
    
    # Calculate overall system readiness
    passed_tests = sum(1 for result in test_results.values() if result)
    total_tests = len(test_results)
    system_readiness = (passed_tests / total_tests) * 100
    
    print(f"\n🎯 MARINA EMAIL SYSTEM TEST SUMMARY")
    print("=" * 50)
    print(f"Tests Passed: {passed_tests}/{total_tests}")
    print(f"System Readiness: {system_readiness:.1f}%")
    
    if system_readiness == 100:
        print("🚀 READY FOR PRODUCTION: All tests passed!")
    elif system_readiness >= 80:
        print("⚠️ MOSTLY READY: Minor issues to resolve before production")
    else:
        print("❌ NOT READY: Significant issues must be resolved")
    
    return system_readiness >= 80

# Run complete test suite
if __name__ == "__main__":
    system_ready = run_complete_test_suite()
```

---

## 🎯 Success Criteria

**Phase 5 Complete When:**
- ✅ All email templates tested and working
- ✅ Performance meets government service standards (95%+ success)
- ✅ Rate limiting protects against abuse
- ✅ Multi-provider delivery confirmed
- ✅ Complete citizen journey flows validated
- ✅ System ready for production deployment

**Ready for Next Phase:**
- [Monitoring & Logging Setup](./06-monitoring.md)
- Production deployment preparation
- Staff training on system operation

---

**📅 Estimated Time**: 2 hours  
**👥 Key Roles**: Developer, QA Tester, Marina Staff (template review)  
**🎯 Outcome**: Fully validated email system ready for citizen use
