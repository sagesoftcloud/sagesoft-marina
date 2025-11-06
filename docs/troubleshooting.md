# 🔧 Troubleshooting Guide
## Marina Email System Issue Resolution

> **Purpose**: Comprehensive troubleshooting guide for common issues, error resolution, and system maintenance procedures.

---

## 🎯 Troubleshooting Approach

**Philosophy**: Systematic diagnosis from basic connectivity to complex configuration issues
**Priority**: Citizen-facing services first, then internal operations
**Documentation**: Log all issues and resolutions for knowledge base

---

## 🚨 Critical Issues (Immediate Response Required)

### Issue: Citizens Not Receiving OTP Emails

**Symptoms:**
- Citizens report not receiving OTP codes
- Login/registration processes failing
- High support ticket volume

**Immediate Diagnosis:**
```bash
# Check SES sending quota and status
aws ses get-send-quota --region ap-southeast-1
aws ses get-account-sending-enabled --region ap-southeast-1

# Check recent email statistics
aws ses get-send-statistics --region ap-southeast-1

# Check CloudWatch for recent sends
aws cloudwatch get-metric-statistics \
    --namespace AWS/SES \
    --metric-name Send \
    --start-time $(date -u -d '1 hour ago' +%Y-%m-%dT%H:%M:%S) \
    --end-time $(date -u +%Y-%m-%dT%H:%M:%S) \
    --period 300 \
    --statistics Sum
```

**Common Causes & Solutions:**

1. **Sandbox Mode Restriction**
   ```bash
   # Check if account is still in sandbox
   QUOTA=$(aws ses get-send-quota --query 'Max24HourSend' --output text)
   if [ "$QUOTA" -eq 200 ]; then
       echo "❌ Account in sandbox mode - production access required"
       # Solution: Request production access via AWS Console
   fi
   ```

2. **High Bounce/Complaint Rate**
   ```bash
   # Check reputation metrics
   aws cloudwatch get-metric-statistics \
       --namespace AWS/SES \
       --metric-name Reputation.BounceRate \
       --start-time $(date -u -d '24 hours ago' +%Y-%m-%dT%H:%M:%S) \
       --end-time $(date -u +%Y-%m-%dT%H:%M:%S) \
       --period 3600 \
       --statistics Average
   ```
   **Solution**: If bounce rate >5%, implement email validation and list cleaning

3. **Rate Limiting**
   ```python
   # Check application logs for rate limit errors
   grep -i "rate limit" /var/log/marina-email/application.log
   
   # Solution: Implement proper rate limiting in application
   # Reduce concurrent email sending
   ```

4. **DNS/DKIM Issues**
   ```bash
   # Verify DKIM records
   dig TXT token1._domainkey.marina.gov.ph
   dig TXT token2._domainkey.marina.gov.ph  
   dig TXT token3._domainkey.marina.gov.ph
   
   # Check domain verification
   aws ses get-identity-verification-attributes \
       --identities marina.gov.ph --region ap-southeast-1
   ```

**Emergency Response Procedure:**
1. **Immediate**: Switch to backup communication method (SMS, phone)
2. **Diagnose**: Run diagnostic commands above
3. **Escalate**: Contact AWS Support if SES service issue
4. **Communicate**: Notify stakeholders and update status page
5. **Resolve**: Apply appropriate solution based on diagnosis
6. **Monitor**: Verify resolution and prevent recurrence

---

## ⚠️ Common Issues & Solutions

### Email Delivery Problems

#### Issue: Emails Going to Spam Folder
**Symptoms**: Citizens report emails in spam/junk folder

**Diagnosis:**
```bash
# Check DKIM status
aws ses get-identity-dkim-attributes \
    --identities marina.gov.ph --region ap-southeast-1

# Verify SPF record
dig TXT marina.gov.ph | grep spf
```

**Solutions:**
1. **Verify DKIM Setup**:
   ```bash
   # Ensure all 3 DKIM records are properly configured
   for token in $(aws ses get-identity-dkim-attributes --identities marina.gov.ph --query 'DkimAttributes."marina.gov.ph".DkimTokens[]' --output text); do
       echo "Checking DKIM token: $token"
       dig CNAME ${token}._domainkey.marina.gov.ph
   done
   ```

2. **Add SPF Record**:
   ```
   TXT record for marina.gov.ph:
   "v=spf1 include:amazonses.com ~all"
   ```

3. **Implement DMARC**:
   ```
   TXT record for _dmarc.marina.gov.ph:
   "v=DMARC1; p=quarantine; rua=mailto:dmarc@marina.gov.ph"
   ```

#### Issue: Slow Email Delivery
**Symptoms**: OTP emails taking >2 minutes to arrive

**Diagnosis:**
```python
# Check application performance logs
import boto3
from datetime import datetime, timedelta

def check_email_latency():
    cloudwatch = boto3.client('cloudwatch', region_name='ap-southeast-1')
    
    # Get custom latency metrics if implemented
    response = cloudwatch.get_metric_statistics(
        Namespace='Marina/EmailService',
        MetricName='EmailLatency',
        StartTime=datetime.now() - timedelta(hours=1),
        EndTime=datetime.now(),
        Period=300,
        Statistics=['Average', 'Maximum']
    )
    
    for point in response['Datapoints']:
        print(f"Average latency: {point['Average']:.2f}ms")
        print(f"Max latency: {point['Maximum']:.2f}ms")
```

**Solutions:**
1. **Optimize Application Code**:
   ```python
   # Use connection pooling
   import boto3
   from botocore.config import Config
   
   config = Config(
       retries={'max_attempts': 3},
       max_pool_connections=50
   )
   ses = boto3.client('ses', config=config)
   ```

2. **Check Network Connectivity**:
   ```bash
   # Test connectivity to SES endpoints
   curl -I https://email.ap-southeast-1.amazonaws.com
   ```

3. **Review Rate Limiting**:
   ```python
   # Ensure not hitting SES rate limits
   # Default: 14 emails/second, adjust application accordingly
   ```

### Authentication & Access Issues

#### Issue: "Access Denied" Errors
**Symptoms**: Application cannot send emails, IAM permission errors

**Diagnosis:**
```bash
# Check IAM user permissions
aws iam list-attached-user-policies --user-name marina-email-service

# Test SES permissions
aws ses get-send-quota --region ap-southeast-1 --profile marina-email

# Check access key status
aws iam list-access-keys --user-name marina-email-service
```

**Solutions:**
1. **Verify IAM Policy**:
   ```bash
   # Check policy document
   aws iam get-policy-version \
       --policy-arn arn:aws:iam::ACCOUNT-ID:policy/MarinaEmailServicePolicy \
       --version-id v1
   ```

2. **Rotate Access Keys** (if compromised):
   ```bash
   # Create new access key
   aws iam create-access-key --user-name marina-email-service
   
   # Update application configuration
   # Delete old access key after verification
   aws iam delete-access-key \
       --user-name marina-email-service \
       --access-key-id OLD-ACCESS-KEY-ID
   ```

#### Issue: Domain Verification Failed
**Symptoms**: Cannot send from marina.gov.ph, domain shows "Pending"

**Diagnosis:**
```bash
# Check domain verification status
aws ses get-identity-verification-attributes \
    --identities marina.gov.ph --region ap-southeast-1

# Check DNS record
dig TXT _amazonses.marina.gov.ph
```

**Solutions:**
1. **Verify DNS Record**:
   ```bash
   # Get verification token
   TOKEN=$(aws ses get-identity-verification-attributes \
       --identities marina.gov.ph \
       --query 'VerificationAttributes."marina.gov.ph".VerificationToken' \
       --output text)
   
   echo "Required DNS record:"
   echo "Name: _amazonses.marina.gov.ph"
   echo "Value: $TOKEN"
   ```

2. **Wait for DNS Propagation**:
   ```bash
   # Check propagation status
   nslookup -type=TXT _amazonses.marina.gov.ph 8.8.8.8
   ```

### Performance Issues

#### Issue: High Memory/CPU Usage
**Symptoms**: Application server performance degradation

**Diagnosis:**
```bash
# Check system resources
top -p $(pgrep -f marina-email)
free -h
df -h

# Check application logs for memory leaks
grep -i "memory\|heap" /var/log/marina-email/application.log
```

**Solutions:**
1. **Optimize Email Batching**:
   ```python
   # Process emails in smaller batches
   def send_batch_emails(email_list, batch_size=10):
       for i in range(0, len(email_list), batch_size):
           batch = email_list[i:i + batch_size]
           process_batch(batch)
           time.sleep(1)  # Rate limiting
   ```

2. **Implement Connection Pooling**:
   ```python
   # Reuse SES connections
   class EmailService:
       def __init__(self):
           self.ses = boto3.client('ses', region_name='ap-southeast-1')
           # Connection will be reused
   ```

---

## 🔍 Diagnostic Tools & Commands

### SES Health Check Script
```bash
#!/bin/bash
# marina_ses_health_check.sh

echo "🔍 Marina SES Health Check"
echo "========================="

# Check account status
echo "1. Account Status:"
aws ses get-account-sending-enabled --region ap-southeast-1

# Check sending quota
echo -e "\n2. Sending Quota:"
aws ses get-send-quota --region ap-southeast-1

# Check recent statistics
echo -e "\n3. Recent Statistics:"
aws ses get-send-statistics --region ap-southeast-1

# Check domain verification
echo -e "\n4. Domain Verification:"
aws ses get-identity-verification-attributes \
    --identities marina.gov.ph --region ap-southeast-1

# Check DKIM status
echo -e "\n5. DKIM Status:"
aws ses get-identity-dkim-attributes \
    --identities marina.gov.ph --region ap-southeast-1

# Check reputation
echo -e "\n6. Reputation Metrics (Last 24h):"
aws cloudwatch get-metric-statistics \
    --namespace AWS/SES \
    --metric-name Reputation.BounceRate \
    --start-time $(date -u -d '24 hours ago' +%Y-%m-%dT%H:%M:%S) \
    --end-time $(date -u +%Y-%m-%dT%H:%M:%S) \
    --period 3600 \
    --statistics Average \
    --region ap-southeast-1

echo -e "\n✅ Health check complete"
```

### Email Test Script
```python
#!/usr/bin/env python3
# marina_email_test.py

import boto3
import sys
from datetime import datetime

def test_email_functionality():
    """Test basic email functionality"""
    print("🧪 Marina Email Functionality Test")
    print("=" * 40)
    
    try:
        # Initialize SES client
        ses = boto3.client('ses', region_name='ap-southeast-1')
        
        # Test 1: Check sending quota
        print("1. Checking sending quota...")
        quota = ses.get_send_quota()
        print(f"   Max 24h send: {quota['Max24HourSend']}")
        print(f"   Sent last 24h: {quota['SentLast24Hours']}")
        print(f"   Max send rate: {quota['MaxSendRate']}/sec")
        
        # Test 2: Send test email
        print("\n2. Sending test email...")
        test_email = input("Enter test email address: ")
        
        response = ses.send_email(
            Source='noreply@marina.gov.ph',
            Destination={'ToAddresses': [test_email]},
            Message={
                'Subject': {'Data': 'Marina Email Test'},
                'Body': {
                    'Text': {
                        'Data': f'Test email sent at {datetime.now()}\n\nMarina Email System is operational.'
                    }
                }
            }
        )
        
        print(f"   ✅ Email sent successfully!")
        print(f"   Message ID: {response['MessageId']}")
        
        return True
        
    except Exception as e:
        print(f"   ❌ Test failed: {str(e)}")
        return False

if __name__ == "__main__":
    success = test_email_functionality()
    sys.exit(0 if success else 1)
```

---

## 📊 Monitoring & Alerting Issues

### Issue: CloudWatch Alarms Not Triggering
**Symptoms**: No alerts received despite system issues

**Diagnosis:**
```bash
# Check alarm status
aws cloudwatch describe-alarms \
    --alarm-names "Marina-Email-High-Bounce-Rate" \
    --region ap-southeast-1

# Check SNS topic subscriptions
aws sns list-subscriptions-by-topic \
    --topic-arn arn:aws:sns:ap-southeast-1:ACCOUNT-ID:marina-email-alerts
```

**Solutions:**
1. **Verify Alarm Configuration**:
   ```bash
   # Check alarm thresholds and evaluation periods
   aws cloudwatch describe-alarms \
       --alarm-name-prefix "Marina-Email" \
       --region ap-southeast-1
   ```

2. **Test SNS Notifications**:
   ```bash
   # Send test notification
   aws sns publish \
       --topic-arn arn:aws:sns:ap-southeast-1:ACCOUNT-ID:marina-email-alerts \
       --message "Test notification from Marina Email System"
   ```

### Issue: Dashboard Not Showing Data
**Symptoms**: CloudWatch dashboard empty or showing no metrics

**Diagnosis:**
```bash
# Check if metrics are being generated
aws cloudwatch list-metrics \
    --namespace AWS/SES \
    --region ap-southeast-1

# Check dashboard configuration
aws cloudwatch get-dashboard \
    --dashboard-name "Marina-Email-System-Operations" \
    --region ap-southeast-1
```

**Solutions:**
1. **Verify Metric Generation**:
   ```bash
   # Send test email to generate metrics
   aws ses send-email \
       --source "noreply@marina.gov.ph" \
       --destination "ToAddresses=admin@marina.gov.ph" \
       --message "Subject={Data=Metric Test},Body={Text={Data=Testing metrics generation}}"
   ```

2. **Update Dashboard Configuration**:
   ```bash
   # Recreate dashboard with correct configuration
   aws cloudwatch put-dashboard \
       --dashboard-name "Marina-Email-System-Operations" \
       --dashboard-body file://dashboard-config.json
   ```

---

## 🛠️ Maintenance Procedures

### Regular Maintenance Tasks

#### Weekly Health Check
```bash
#!/bin/bash
# weekly_maintenance.sh

echo "📅 Marina Email System - Weekly Maintenance"
echo "==========================================="

# 1. Check system health
./marina_ses_health_check.sh

# 2. Review bounce/complaint rates
echo -e "\n📊 Weekly Performance Review:"
python scripts/generate_weekly_report.py

# 3. Check for security updates
echo -e "\n🔒 Security Check:"
pip list --outdated | grep -E "(boto3|botocore)"

# 4. Verify backup procedures
echo -e "\n💾 Backup Verification:"
ls -la /backup/marina-email/

# 5. Test disaster recovery
echo -e "\n🚨 DR Test (Simulation):"
python scripts/test_failover.py --simulate

echo -e "\n✅ Weekly maintenance complete"
```

#### Monthly Optimization
```python
# monthly_optimization.py
import boto3
from datetime import datetime, timedelta

def monthly_optimization():
    """Perform monthly system optimization"""
    print("🔧 Marina Email System - Monthly Optimization")
    
    # 1. Analyze email patterns
    analyze_email_patterns()
    
    # 2. Optimize rate limits based on usage
    optimize_rate_limits()
    
    # 3. Review and clean email templates
    review_email_templates()
    
    # 4. Update monitoring thresholds
    update_monitoring_thresholds()
    
    # 5. Generate cost optimization report
    generate_cost_report()

def analyze_email_patterns():
    """Analyze email sending patterns for optimization"""
    cloudwatch = boto3.client('cloudwatch', region_name='ap-southeast-1')
    
    # Get last 30 days of data
    end_time = datetime.now()
    start_time = end_time - timedelta(days=30)
    
    # Analyze peak usage times
    response = cloudwatch.get_metric_statistics(
        Namespace='AWS/SES',
        MetricName='Send',
        StartTime=start_time,
        EndTime=end_time,
        Period=3600,  # Hourly data
        Statistics=['Sum']
    )
    
    # Process data and generate recommendations
    hourly_data = {}
    for point in response['Datapoints']:
        hour = point['Timestamp'].hour
        hourly_data[hour] = hourly_data.get(hour, 0) + point['Sum']
    
    peak_hour = max(hourly_data, key=hourly_data.get)
    print(f"Peak usage hour: {peak_hour}:00 ({hourly_data[peak_hour]} emails)")
    
    return hourly_data

if __name__ == "__main__":
    monthly_optimization()
```

---

## 📞 Escalation Procedures

### Level 1: Application Issues
**Contact**: Marina IT Operations Team
**Response Time**: 15 minutes during business hours
**Scope**: Application errors, configuration issues, minor performance problems

### Level 2: AWS Service Issues  
**Contact**: AWS Support (Business/Enterprise)
**Response Time**: 1-4 hours depending on severity
**Scope**: SES service problems, quota issues, AWS infrastructure

### Level 3: Critical System Failure
**Contact**: Emergency response team + AWS Premium Support
**Response Time**: Immediate
**Scope**: Complete system outage, security incidents, data breaches

### Emergency Contact Template
```
MARINA EMAIL SYSTEM EMERGENCY

Incident ID: [AUTO-GENERATED]
Severity: [CRITICAL/HIGH/MEDIUM/LOW]
Time: [TIMESTAMP]
Reporter: [NAME/ROLE]

Issue Description:
[Detailed description of the problem]

Impact:
[Number of affected citizens/services]

Steps Taken:
[What has been tried so far]

Current Status:
[System status and workarounds in place]

Next Actions:
[Planned resolution steps]

Contact: [Phone/Email for updates]
```

---

## 📚 Knowledge Base

### Common Error Messages

#### "MessageRejected: Email address not verified"
**Cause**: Trying to send from unverified email address
**Solution**: Verify sender email or domain in SES console

#### "Throttling: Rate exceeded"  
**Cause**: Sending emails too fast
**Solution**: Implement rate limiting in application (max 14/second)

#### "InvalidParameterValue: Missing final '@domain'"
**Cause**: Malformed email address
**Solution**: Validate email format before sending

#### "AccessDenied: User is not authorized to perform: ses:SendEmail"
**Cause**: Insufficient IAM permissions
**Solution**: Check and update IAM policy for user

### Best Practices Reminders

1. **Always validate email addresses** before sending
2. **Implement proper rate limiting** (5-10 emails/second max)
3. **Monitor bounce and complaint rates** daily
4. **Use appropriate email types** (Transactional vs Promotional)
5. **Keep email content relevant** and professional
6. **Maintain clean email lists** to reduce bounces
7. **Test in sandbox first** before production deployment
8. **Log all email activities** for audit compliance
9. **Rotate access keys regularly** (every 90 days)
10. **Keep DNS records updated** and monitored

---

## 🎯 Quick Reference

### Essential Commands
```bash
# Check SES status
aws ses get-send-quota --region ap-southeast-1

# Send test email
aws ses send-email --source noreply@marina.gov.ph --destination ToAddresses=test@example.com --message "Subject={Data=Test},Body={Text={Data=Test message}}"

# Check domain verification
aws ses get-identity-verification-attributes --identities marina.gov.ph

# View recent statistics
aws ses get-send-statistics --region ap-southeast-1

# Check CloudWatch metrics
aws cloudwatch get-metric-statistics --namespace AWS/SES --metric-name Send --start-time 2024-01-01T00:00:00Z --end-time 2024-01-02T00:00:00Z --period 3600 --statistics Sum
```

### Emergency Contacts
- **Marina IT Operations**: [Phone/Email]
- **AWS Support**: [Support Case URL]
- **DNS Team**: [Contact for DNS issues]
- **Security Team**: [Contact for security incidents]

---

**📅 Last Updated**: [Current Date]  
**👥 Maintained By**: Marina IT Operations Team  
**🎯 Purpose**: Ensure reliable email service for Filipino citizens
