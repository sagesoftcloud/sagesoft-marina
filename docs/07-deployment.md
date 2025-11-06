# 🚀 Production Deployment
## Marina Email System Go-Live Procedures

> **Phase 7**: Final deployment steps, go-live procedures, and post-deployment validation for Marina's citizen-facing email system.

---

## 🎯 Deployment Objectives

**Goals:**
- Deploy Marina email system to production environment
- Validate all components work in production
- Enable citizen-facing email services
- Establish operational procedures for ongoing management
- Ensure seamless transition from testing to live service

**Success Criteria**: Citizens can receive OTP, transaction alerts, and official communications reliably

---

## ✅ Pre-Deployment Checklist

> **Critical**: All prerequisites must be completed before production deployment
> **Validation**: Each item must be verified and signed off by responsible team

### Technical Prerequisites
- [ ] **AWS SES Production Access**: Approved and active (not sandbox mode)
- [ ] **Domain Verification**: marina.gov.ph verified and DKIM enabled
- [ ] **DNS Records**: All required records propagated and verified
- [ ] **IAM Configuration**: Service user created with minimal permissions
- [ ] **Application Code**: Tested and ready for production deployment
- [ ] **Monitoring Setup**: CloudWatch dashboard and alerts configured
- [ ] **Testing Complete**: All test scenarios passed successfully

### Operational Prerequisites  
- [ ] **Team Training**: Operations team trained on monitoring and procedures
- [ ] **Documentation**: All operational procedures documented
- [ ] **Support Contacts**: Emergency contact list prepared
- [ ] **Rollback Plan**: Procedures for reverting to previous system if needed
- [ ] **Communication Plan**: Stakeholder notification procedures ready
- [ ] **Maintenance Window**: Scheduled deployment time confirmed

### Compliance Prerequisites
- [ ] **Security Review**: Government security requirements validated
- [ ] **Privacy Compliance**: Data Privacy Act requirements met
- [ ] **Audit Trail**: Logging and monitoring meet government standards
- [ ] **Backup Procedures**: Data backup and recovery procedures in place

---

## 🔧 Step 1: Production Environment Setup

> **Purpose**: Configure production environment with proper security and performance settings
> **Environment**: Production AWS account with government-grade security
> **Configuration**: Optimized for Marina's citizen service requirements

### Environment Configuration
```bash
# Set production environment variables
export AWS_PROFILE=marina-production
export AWS_DEFAULT_REGION=ap-southeast-1
export MARINA_ENV=production
export MARINA_LOG_LEVEL=INFO

# Verify production access
aws sts get-caller-identity --profile marina-production
aws ses get-send-quota --region ap-southeast-1 --profile marina-production
```

### Production Application Configuration
```python
# production_config.py - Marina Email Service Production Settings
import os

class MarinaProductionConfig:
    """Production configuration for Marina Email Service"""
    
    # AWS Configuration
    AWS_REGION = 'ap-southeast-1'
    AWS_PROFILE = 'marina-production'
    
    # Email Configuration
    DEFAULT_FROM_EMAIL = 'noreply@marina.gov.ph'
    SUPPORT_EMAIL = 'support@marina.gov.ph'
    ADMIN_EMAIL = 'admin@marina.gov.ph'
    
    # Rate Limiting (Production Values)
    OTP_RATE_LIMIT = 5  # 5 OTPs per 5 minutes per user
    ALERT_RATE_LIMIT = 10  # 10 alerts per 5 minutes per user
    RATE_LIMIT_WINDOW = 300  # 5 minutes
    
    # Logging Configuration
    LOG_LEVEL = 'INFO'
    LOG_GROUP = 'marina-email-production'
    AUDIT_LOG_RETENTION_DAYS = 2557  # 7 years for government compliance
    
    # Performance Settings
    MAX_CONCURRENT_EMAILS = 10  # Stay well under SES limits
    EMAIL_TIMEOUT_SECONDS = 30
    RETRY_ATTEMPTS = 3
    
    # Monitoring
    ENABLE_CLOUDWATCH_METRICS = True
    ENABLE_PERFORMANCE_MONITORING = True
    
    # Security
    MASK_EMAIL_IN_LOGS = True
    ENABLE_AUDIT_TRAIL = True
    
    @classmethod
    def validate_config(cls):
        """Validate production configuration"""
        required_vars = [
            'AWS_REGION', 'DEFAULT_FROM_EMAIL', 'SUPPORT_EMAIL'
        ]
        
        for var in required_vars:
            if not getattr(cls, var):
                raise ValueError(f"Required configuration {var} not set")
        
        print("✅ Production configuration validated")
        return True

# Validate configuration on import
MarinaProductionConfig.validate_config()
```

### Production Deployment Script
```bash
#!/bin/bash
# deploy_marina_email_production.sh

set -e  # Exit on any error

echo "🚀 Marina Email System - Production Deployment"
echo "=============================================="

# Verify prerequisites
echo "1. Verifying prerequisites..."

# Check AWS access
if ! aws sts get-caller-identity --profile marina-production > /dev/null 2>&1; then
    echo "❌ AWS production access not configured"
    exit 1
fi

# Check SES production status
SEND_QUOTA=$(aws ses get-send-quota --region ap-southeast-1 --profile marina-production --query 'Max24HourSend' --output text)
if [ "$SEND_QUOTA" -eq 200 ]; then
    echo "❌ SES still in sandbox mode - production access required"
    exit 1
fi

echo "✅ Prerequisites verified"

# Deploy application
echo "2. Deploying Marina email service..."

# Copy production configuration
cp production_config.py /opt/marina-email/config.py

# Install/update dependencies
pip install -r requirements.txt

# Run production tests
echo "3. Running production validation tests..."
python -m pytest tests/test_production.py -v

# Start monitoring
echo "4. Enabling production monitoring..."
python scripts/setup_monitoring.py --environment production

# Verify deployment
echo "5. Verifying deployment..."
python scripts/verify_production.py

echo "✅ Marina Email System deployed successfully to production"
echo "📊 Monitor system at: https://console.aws.amazon.com/cloudwatch/home?region=ap-southeast-1#dashboards:name=Marina-Email-System-Operations"
```

---

## 🧪 Step 2: Production Validation Testing

> **Purpose**: Verify all components work correctly in production environment
> **Scope**: End-to-end testing with real citizen email addresses
> **Validation**: Confirm system ready for public use

### Production Validation Script
```python
# production_validation.py
import time
from marina_email_service import MarinaEmailServiceWithLogging
from production_config import MarinaProductionConfig

class ProductionValidator:
    def __init__(self):
        self.email_service = MarinaEmailServiceWithLogging()
        self.validation_results = []
        
    def run_production_validation(self):
        """Run comprehensive production validation tests"""
        print("🧪 Marina Email System - Production Validation")
        print("=" * 50)
        
        # Test 1: Basic email functionality
        self.test_basic_email_sending()
        
        # Test 2: OTP email delivery
        self.test_otp_email_production()
        
        # Test 3: Transaction alert delivery
        self.test_transaction_alert_production()
        
        # Test 4: Rate limiting in production
        self.test_rate_limiting_production()
        
        # Test 5: Monitoring and alerting
        self.test_monitoring_production()
        
        # Generate validation report
        self.generate_validation_report()
        
    def test_basic_email_sending(self):
        """Test basic email sending functionality"""
        print("\n1. Testing basic email sending...")
        
        try:
            result = self.email_service.send_email(
                to_email="admin@marina.gov.ph",
                subject="Marina Production Validation - Basic Email Test",
                body_text="""
Marina Email System Production Validation

This email confirms that the Marina email system is operational in production.

Test Details:
- Environment: Production
- Service: Basic Email Sending
- Timestamp: {timestamp}
- Status: Operational

Maritime Industry Authority
Republic of the Philippines
                """.format(timestamp=time.strftime("%Y-%m-%d %H:%M:%S"))
            )
            
            if result['success']:
                print("✅ Basic email sending: PASSED")
                self.validation_results.append(("Basic Email", True, result['message_id']))
            else:
                print(f"❌ Basic email sending: FAILED - {result['error']}")
                self.validation_results.append(("Basic Email", False, result['error']))
                
        except Exception as e:
            print(f"❌ Basic email sending: ERROR - {str(e)}")
            self.validation_results.append(("Basic Email", False, str(e)))
            
    def test_otp_email_production(self):
        """Test OTP email in production environment"""
        print("\n2. Testing OTP email delivery...")
        
        try:
            result = self.email_service.send_otp_email(
                to_email="admin@marina.gov.ph",
                otp_code="PROD123",
                service_name="Marina Production Validation"
            )
            
            if result['success']:
                print("✅ OTP email delivery: PASSED")
                self.validation_results.append(("OTP Email", True, result['message_id']))
            else:
                print(f"❌ OTP email delivery: FAILED - {result['error']}")
                self.validation_results.append(("OTP Email", False, result['error']))
                
        except Exception as e:
            print(f"❌ OTP email delivery: ERROR - {str(e)}")
            self.validation_results.append(("OTP Email", False, str(e)))
            
    def test_transaction_alert_production(self):
        """Test transaction alert in production environment"""
        print("\n3. Testing transaction alert delivery...")
        
        try:
            transaction_data = {
                'type': 'Production Validation Test',
                'amount': 'PHP 0.00',
                'reference': 'PROD-VAL-001',
                'date': time.strftime("%B %d, %Y at %I:%M %p"),
                'status': 'Validation Complete'
            }
            
            result = self.email_service.send_transaction_alert(
                to_email="admin@marina.gov.ph",
                transaction_data=transaction_data
            )
            
            if result['success']:
                print("✅ Transaction alert delivery: PASSED")
                self.validation_results.append(("Transaction Alert", True, result['message_id']))
            else:
                print(f"❌ Transaction alert delivery: FAILED - {result['error']}")
                self.validation_results.append(("Transaction Alert", False, result['error']))
                
        except Exception as e:
            print(f"❌ Transaction alert delivery: ERROR - {str(e)}")
            self.validation_results.append(("Transaction Alert", False, str(e)))
            
    def test_rate_limiting_production(self):
        """Test rate limiting in production"""
        print("\n4. Testing rate limiting...")
        
        try:
            # Send multiple OTPs rapidly to test rate limiting
            test_email = "ratelimit.test@marina.gov.ph"
            rapid_results = []
            
            for i in range(3):  # Test with 3 rapid requests
                result = self.email_service.send_otp_email(
                    to_email=test_email,
                    otp_code=f"RT{i:03d}",
                    service_name="Rate Limit Test"
                )
                rapid_results.append(result['success'])
                
            # All should succeed (within rate limit)
            if all(rapid_results):
                print("✅ Rate limiting: PASSED (within limits)")
                self.validation_results.append(("Rate Limiting", True, "Within limits"))
            else:
                print("⚠️ Rate limiting: Unexpected behavior")
                self.validation_results.append(("Rate Limiting", False, "Unexpected behavior"))
                
        except Exception as e:
            print(f"❌ Rate limiting test: ERROR - {str(e)}")
            self.validation_results.append(("Rate Limiting", False, str(e)))
            
    def test_monitoring_production(self):
        """Test monitoring and alerting in production"""
        print("\n5. Testing monitoring systems...")
        
        try:
            # Check CloudWatch dashboard accessibility
            import boto3
            cloudwatch = boto3.client('cloudwatch', region_name='ap-southeast-1')
            
            # Verify dashboard exists
            dashboards = cloudwatch.list_dashboards()
            marina_dashboard = any(
                'Marina-Email-System-Operations' in d['DashboardName'] 
                for d in dashboards['DashboardEntries']
            )
            
            if marina_dashboard:
                print("✅ Monitoring dashboard: ACCESSIBLE")
                self.validation_results.append(("Monitoring", True, "Dashboard accessible"))
            else:
                print("❌ Monitoring dashboard: NOT FOUND")
                self.validation_results.append(("Monitoring", False, "Dashboard not found"))
                
        except Exception as e:
            print(f"❌ Monitoring test: ERROR - {str(e)}")
            self.validation_results.append(("Monitoring", False, str(e)))
            
    def generate_validation_report(self):
        """Generate production validation report"""
        print("\n" + "=" * 50)
        print("📊 PRODUCTION VALIDATION REPORT")
        print("=" * 50)
        
        passed_tests = sum(1 for _, success, _ in self.validation_results if success)
        total_tests = len(self.validation_results)
        success_rate = (passed_tests / total_tests) * 100 if total_tests > 0 else 0
        
        print(f"Tests Passed: {passed_tests}/{total_tests}")
        print(f"Success Rate: {success_rate:.1f}%")
        print()
        
        for test_name, success, details in self.validation_results:
            status = "✅ PASS" if success else "❌ FAIL"
            print(f"{test_name:<20} {status:<8} {details}")
        
        print()
        if success_rate == 100:
            print("🚀 PRODUCTION READY: All validation tests passed!")
            print("Marina Email System is ready to serve Filipino citizens.")
        elif success_rate >= 80:
            print("⚠️ MOSTLY READY: Minor issues detected, review before full deployment")
        else:
            print("❌ NOT READY: Critical issues must be resolved before deployment")
            
        return success_rate >= 80

# Run production validation
if __name__ == "__main__":
    validator = ProductionValidator()
    is_ready = validator.run_production_validation()
    
    if is_ready:
        print("\n✅ Marina Email System validated for production use")
    else:
        print("\n❌ Marina Email System requires fixes before production use")
        exit(1)
```

---

## 🎯 Step 3: Go-Live Procedures

> **Purpose**: Systematic activation of Marina email system for citizen use
> **Coordination**: Multiple teams working together for smooth transition
> **Communication**: Stakeholder notifications and public announcements

### Go-Live Checklist
```bash
#!/bin/bash
# marina_go_live.sh - Production Go-Live Procedures

echo "🚀 Marina Email System - Go-Live Procedures"
echo "==========================================="

# Phase 1: Final Pre-Flight Checks
echo "Phase 1: Final Pre-Flight Checks"
echo "--------------------------------"

# Verify all systems operational
python production_validation.py
if [ $? -ne 0 ]; then
    echo "❌ Pre-flight validation failed - ABORT GO-LIVE"
    exit 1
fi

# Check team readiness
echo "✅ Technical validation complete"
echo "📞 Confirm operations team ready: [Y/n]"
read -r ops_ready
if [[ $ops_ready =~ ^[Nn]$ ]]; then
    echo "❌ Operations team not ready - ABORT GO-LIVE"
    exit 1
fi

# Phase 2: Enable Production Services
echo -e "\nPhase 2: Enable Production Services"
echo "-----------------------------------"

# Update application configuration to production mode
echo "Enabling production email services..."
export MARINA_EMAIL_PRODUCTION=true

# Start production monitoring
echo "Activating enhanced monitoring..."
python scripts/enable_production_monitoring.py

# Phase 3: Gradual Rollout
echo -e "\nPhase 3: Gradual Service Rollout"
echo "--------------------------------"

echo "Step 1: Enable OTP emails for staff testing..."
# Enable OTP emails for Marina staff first
python scripts/enable_service.py --service otp --audience staff

echo "Waiting 15 minutes for staff validation..."
sleep 900  # 15 minutes

echo "Step 2: Enable transaction alerts for pilot users..."
# Enable transaction alerts for pilot group
python scripts/enable_service.py --service alerts --audience pilot

echo "Waiting 30 minutes for pilot validation..."
sleep 1800  # 30 minutes

echo "Step 3: Enable all email services for public..."
# Full public rollout
python scripts/enable_service.py --service all --audience public

# Phase 4: Post Go-Live Monitoring
echo -e "\nPhase 4: Post Go-Live Monitoring"
echo "--------------------------------"

echo "✅ Marina Email System is now LIVE and serving citizens"
echo "📊 Monitor system health at: https://console.aws.amazon.com/cloudwatch/"
echo "📧 First citizen emails should appear in dashboard within minutes"

# Set up intensive monitoring for first 24 hours
python scripts/setup_intensive_monitoring.py --duration 24h

echo -e "\n🎉 GO-LIVE COMPLETE!"
echo "Marina Email System is now operational for Filipino citizens"
```

### Stakeholder Communication Template
```markdown
# Marina Email System - Production Go-Live Notification

**To**: Marina Leadership, IT Operations, Citizen Services Team
**From**: Marina IT Department  
**Date**: [Current Date]
**Subject**: Marina Email System Now Live - Serving Citizens

## Go-Live Status: ✅ SUCCESSFUL

The Marina Email Notification System has been successfully deployed to production and is now serving Filipino citizens.

### System Capabilities Now Active:
- ✅ OTP verification emails for citizen portal access
- ✅ Transaction confirmation emails for maritime services  
- ✅ System notifications for vessel registration and permits
- ✅ Official communications from Marina to stakeholders

### Key Metrics (First Hour):
- Email delivery success rate: [XX]%
- Average delivery time: [XX] seconds
- System availability: [XX]%
- No critical alerts triggered

### Monitoring & Support:
- **Real-time Dashboard**: [CloudWatch Dashboard URL]
- **Operations Team**: Available 24/7 for first 48 hours
- **Escalation Contact**: [Emergency Contact]
- **Citizen Support**: support@marina.gov.ph

### Next Steps:
1. Monitor system performance for first 24 hours
2. Collect citizen feedback on email experience
3. Weekly performance review scheduled for [Date]

The system is performing within expected parameters and ready to support Marina's digital services for Filipino citizens.

**Marina IT Department**  
Maritime Industry Authority  
Republic of the Philippines
```

---

## 📊 Step 4: Post-Deployment Monitoring

> **Purpose**: Intensive monitoring during critical first 24-48 hours
> **Focus**: Early detection of issues before widespread citizen impact
> **Response**: Rapid issue resolution and system optimization

### Intensive Monitoring Script
```python
# intensive_monitoring.py - Enhanced monitoring for go-live period
import time
import boto3
from datetime import datetime, timedelta

class GoLiveMonitor:
    def __init__(self):
        self.cloudwatch = boto3.client('cloudwatch', region_name='ap-southeast-1')
        self.ses = boto3.client('ses', region_name='ap-southeast-1')
        self.monitoring_active = True
        
    def start_intensive_monitoring(self, duration_hours=24):
        """Start intensive monitoring for go-live period"""
        print(f"🔍 Starting intensive monitoring for {duration_hours} hours")
        
        end_time = datetime.now() + timedelta(hours=duration_hours)
        check_interval = 300  # 5 minutes
        
        while datetime.now() < end_time and self.monitoring_active:
            try:
                # Check system health
                health_status = self.check_system_health()
                
                # Check for anomalies
                anomalies = self.detect_anomalies()
                
                # Generate status report
                self.generate_status_report(health_status, anomalies)
                
                # Sleep until next check
                time.sleep(check_interval)
                
            except KeyboardInterrupt:
                print("\n⏹️ Monitoring stopped by user")
                self.monitoring_active = False
                break
            except Exception as e:
                print(f"❌ Monitoring error: {e}")
                time.sleep(60)  # Wait 1 minute before retry
                
    def check_system_health(self):
        """Check overall system health metrics"""
        try:
            # Get recent email statistics
            end_time = datetime.now()
            start_time = end_time - timedelta(minutes=15)  # Last 15 minutes
            
            # Get send count
            send_response = self.cloudwatch.get_metric_statistics(
                Namespace='AWS/SES',
                MetricName='Send',
                StartTime=start_time,
                EndTime=end_time,
                Period=300,
                Statistics=['Sum']
            )
            
            recent_sends = sum(point['Sum'] for point in send_response['Datapoints'])
            
            # Get bounce count
            bounce_response = self.cloudwatch.get_metric_statistics(
                Namespace='AWS/SES',
                MetricName='Bounce',
                StartTime=start_time,
                EndTime=end_time,
                Period=300,
                Statistics=['Sum']
            )
            
            recent_bounces = sum(point['Sum'] for point in bounce_response['Datapoints'])
            
            # Calculate health metrics
            bounce_rate = (recent_bounces / recent_sends) * 100 if recent_sends > 0 else 0
            
            return {
                'timestamp': datetime.now().isoformat(),
                'emails_sent_15min': int(recent_sends),
                'bounces_15min': int(recent_bounces),
                'bounce_rate': round(bounce_rate, 2),
                'healthy': bounce_rate < 5.0 and recent_sends > 0
            }
            
        except Exception as e:
            return {
                'timestamp': datetime.now().isoformat(),
                'error': str(e),
                'healthy': False
            }
            
    def detect_anomalies(self):
        """Detect unusual patterns that might indicate issues"""
        anomalies = []
        
        try:
            # Check for unusual volume spikes
            end_time = datetime.now()
            start_time = end_time - timedelta(hours=1)
            
            volume_response = self.cloudwatch.get_metric_statistics(
                Namespace='AWS/SES',
                MetricName='Send',
                StartTime=start_time,
                EndTime=end_time,
                Period=300,
                Statistics=['Sum']
            )
            
            recent_volume = sum(point['Sum'] for point in volume_response['Datapoints'])
            
            # Anomaly detection (simple threshold-based)
            if recent_volume > 1000:  # More than 1000 emails in 1 hour
                anomalies.append({
                    'type': 'high_volume',
                    'description': f'Unusually high email volume: {recent_volume} emails in last hour',
                    'severity': 'warning'
                })
                
            # Check for error rate spikes
            # (Additional anomaly detection logic can be added here)
            
        except Exception as e:
            anomalies.append({
                'type': 'monitoring_error',
                'description': f'Error in anomaly detection: {str(e)}',
                'severity': 'error'
            })
            
        return anomalies
        
    def generate_status_report(self, health_status, anomalies):
        """Generate and display current status report"""
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        
        print(f"\n📊 Marina Email System Status - {timestamp}")
        print("=" * 60)
        
        # System health
        if health_status.get('healthy', False):
            print("🟢 System Status: HEALTHY")
        else:
            print("🔴 System Status: ATTENTION REQUIRED")
            
        # Key metrics
        if 'emails_sent_15min' in health_status:
            print(f"📧 Emails (15 min): {health_status['emails_sent_15min']}")
            print(f"📈 Bounce Rate: {health_status['bounce_rate']}%")
            
        # Anomalies
        if anomalies:
            print(f"\n⚠️ Anomalies Detected: {len(anomalies)}")
            for anomaly in anomalies:
                severity_icon = "🔴" if anomaly['severity'] == 'error' else "🟡"
                print(f"  {severity_icon} {anomaly['description']}")
        else:
            print("\n✅ No anomalies detected")
            
        print("-" * 60)

# Usage for go-live monitoring
if __name__ == "__main__":
    monitor = GoLiveMonitor()
    monitor.start_intensive_monitoring(duration_hours=24)
```

---

## ✅ Deployment Success Criteria

### Technical Success Metrics
- [ ] **Email Delivery**: 95%+ success rate for all email types
- [ ] **Performance**: <30 second delivery time for OTP emails
- [ ] **Availability**: 99.9%+ system uptime
- [ ] **Security**: No security incidents or unauthorized access
- [ ] **Monitoring**: All alerts and dashboards operational

### Operational Success Metrics  
- [ ] **Citizen Satisfaction**: No major complaints about email delivery
- [ ] **Support Tickets**: <5 email-related support requests per day
- [ ] **Team Readiness**: Operations team successfully managing system
- [ ] **Documentation**: All procedures documented and accessible
- [ ] **Compliance**: Government audit requirements met

### Business Success Metrics
- [ ] **Service Availability**: Citizens can complete online transactions
- [ ] **Process Efficiency**: Reduced manual communication overhead
- [ ] **Cost Effectiveness**: Email costs within approved budget
- [ ] **Scalability**: System ready for expected growth in usage

---

## 🎯 Success Criteria

**Phase 7 Complete When:**
- ✅ Production environment fully operational
- ✅ All validation tests passed successfully  
- ✅ Go-live procedures executed without issues
- ✅ Intensive monitoring confirms system stability
- ✅ Citizens successfully receiving Marina emails
- ✅ Operations team managing system effectively

**Marina Email System Status**: 🚀 **LIVE AND SERVING CITIZENS**

---

## 📞 Post-Deployment Support

### 24/7 Support (First 48 Hours)
- **Primary Contact**: Marina IT Operations
- **Escalation**: AWS Support (if needed)
- **Emergency**: [Emergency contact number]

### Ongoing Support Structure
- **Daily Monitoring**: Operations team checks dashboard
- **Weekly Reviews**: Performance and optimization analysis  
- **Monthly Reports**: Compliance and cost analysis
- **Quarterly Audits**: Full system review and updates

### Continuous Improvement
- **Citizen Feedback**: Regular collection and analysis
- **Performance Optimization**: Ongoing system tuning
- **Feature Enhancements**: Based on Marina service needs
- **Security Updates**: Regular security reviews and updates

---

**📅 Deployment Date**: [Current Date]  
**👥 Deployment Team**: Marina IT Department  
**🎯 Achievement**: Production-ready email system serving Filipino citizens reliably and securely

**🇵🇭 Serving the Filipino Maritime Community with Excellence**
