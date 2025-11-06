# 📊 Monitoring & Logging Setup
## Comprehensive Monitoring for Marina Email System

> **Phase 6**: Implement production-grade monitoring, alerting, and logging for government compliance and operational excellence.

---

## 🎯 Monitoring Objectives

**Goals:**
- Real-time visibility into email system performance
- Automated alerts for critical issues (bounce rates, failures)
- Comprehensive audit trail for government compliance
- Proactive issue detection before citizen impact
- Cost monitoring and optimization insights

**Government Requirements**: Audit trails, performance metrics, incident response capabilities

---

## 📈 Step 1: CloudWatch Dashboard Setup

> **Purpose**: Create visual monitoring dashboard for Marina email system health
> **Audience**: IT operations, management, support teams
> **Key Metrics**: Email volume, delivery rates, reputation metrics, costs

### Create Marina Email Dashboard (Console)

1. **Navigate to CloudWatch**:
   - Go to [CloudWatch Console](https://console.aws.amazon.com/cloudwatch/)
   - Ensure region is **ap-southeast-1** (Singapore)
   - Click **"Dashboards"** in left menu

2. **Create New Dashboard**:
   - Click **"Create dashboard"**
   - Dashboard name: `Marina-Email-System-Operations`
   - Click **"Create dashboard"**

3. **Add Email Volume Widget**:
   - Click **"Add widget"**
   - Select **"Line"** chart
   - Configure metrics:
     ```
     Namespace: AWS/SES
     Metric: Send
     Statistic: Sum
     Period: 5 minutes
     ```
   - Widget title: `Marina Email Volume (Last 24 Hours)`
   - Click **"Create widget"**

4. **Add Delivery Success Widget**:
   - Add another **"Line"** widget
   - Metrics:
     ```
     AWS/SES - Send (Sum)
     AWS/SES - Bounce (Sum) 
     AWS/SES - Complaint (Sum)
     AWS/SES - Delivery (Sum)
     ```
   - Widget title: `Email Delivery Metrics`

5. **Add Reputation Widget**:
   - Add **"Number"** widget
   - Metrics:
     ```
     AWS/SES - Reputation.BounceRate (Average)
     AWS/SES - Reputation.ComplaintRate (Average)
     ```
   - Widget title: `Email Reputation Health`

6. **Add Cost Widget** (Optional):
   - Add **"Line"** widget
   - Namespace: `AWS/Billing`
   - Metric: `EstimatedCharges`
   - Dimension: `ServiceName = AmazonSES`
   - Widget title: `Marina Email Costs (Monthly)`

### Create Dashboard via CLI
```bash
# Create comprehensive Marina email dashboard
cat > marina-email-dashboard.json << 'EOF'
{
    "widgets": [
        {
            "type": "metric",
            "x": 0,
            "y": 0,
            "width": 12,
            "height": 6,
            "properties": {
                "metrics": [
                    [ "AWS/SES", "Send" ],
                    [ ".", "Bounce" ],
                    [ ".", "Complaint" ],
                    [ ".", "Delivery" ]
                ],
                "view": "timeSeries",
                "stacked": false,
                "region": "ap-southeast-1",
                "title": "Marina Email Delivery Metrics",
                "period": 300,
                "stat": "Sum"
            }
        },
        {
            "type": "metric",
            "x": 12,
            "y": 0,
            "width": 12,
            "height": 6,
            "properties": {
                "metrics": [
                    [ "AWS/SES", "Reputation.BounceRate" ],
                    [ ".", "Reputation.ComplaintRate" ]
                ],
                "view": "timeSeries",
                "region": "ap-southeast-1",
                "title": "Marina Email Reputation Health",
                "period": 300,
                "stat": "Average",
                "yAxis": {
                    "left": {
                        "min": 0,
                        "max": 0.1
                    }
                }
            }
        },
        {
            "type": "metric",
            "x": 0,
            "y": 6,
            "width": 24,
            "height": 6,
            "properties": {
                "metrics": [
                    [ "AWS/SES", "Send" ]
                ],
                "view": "timeSeries",
                "region": "ap-southeast-1",
                "title": "Marina Daily Email Volume Trend",
                "period": 3600,
                "stat": "Sum"
            }
        }
    ]
}
EOF

# Create the dashboard
aws cloudwatch put-dashboard \
    --dashboard-name "Marina-Email-System-Operations" \
    --dashboard-body file://marina-email-dashboard.json \
    --region ap-southeast-1
```

### ✅ Dashboard Verification
```bash
# Verify dashboard creation
aws cloudwatch list-dashboards --region ap-southeast-1

# Get dashboard details
aws cloudwatch get-dashboard \
    --dashboard-name "Marina-Email-System-Operations" \
    --region ap-southeast-1
```

> **Status Check**: Dashboard visible in CloudWatch console with real-time metrics
> **Access**: Share dashboard URL with Marina operations team
> **Next**: Set up automated alerts for critical issues

---

## 🚨 Step 2: Critical Alert Configuration

> **Purpose**: Automated notifications when email system needs immediate attention
> **Government Requirement**: Rapid response to service disruptions
> **Alert Strategy**: Early warning before citizen impact

### High Bounce Rate Alert (Console)

1. **Navigate to CloudWatch Alarms**:
   - Go to CloudWatch Console → **"Alarms"**
   - Click **"Create alarm"**

2. **Configure Bounce Rate Alarm**:
   - Select metric: `AWS/SES` → `Reputation.BounceRate`
   - Statistic: **Average**
   - Period: **5 minutes**
   - Threshold: **Greater than 0.05** (5%)
   - Evaluation periods: **2**
   - Missing data: **Treat as not breaching**

3. **Set Alarm Details**:
   - Alarm name: `Marina-Email-High-Bounce-Rate`
   - Description: `CRITICAL: Marina email bounce rate exceeds 5% - Risk of reputation damage`
   - Actions: Create SNS topic `marina-email-alerts`

4. **Configure Notifications**:
   - Create new SNS topic: `marina-email-alerts`
   - Add email subscribers:
     - `it-operations@marina.gov.ph`
     - `email-admin@marina.gov.ph`
     - `support@marina.gov.ph`

### High Complaint Rate Alert
```bash
# Create complaint rate alarm (critical for AWS account health)
aws cloudwatch put-metric-alarm \
    --alarm-name "Marina-Email-High-Complaint-Rate" \
    --alarm-description "CRITICAL: Marina email complaint rate exceeds 0.1% - Risk of account suspension" \
    --metric-name "Reputation.ComplaintRate" \
    --namespace "AWS/SES" \
    --statistic "Average" \
    --period 300 \
    --threshold 0.001 \
    --comparison-operator "GreaterThanThreshold" \
    --evaluation-periods 2 \
    --alarm-actions "arn:aws:sns:ap-southeast-1:ACCOUNT-ID:marina-email-alerts" \
    --region ap-southeast-1
```

### Email Volume Anomaly Alert
```bash
# Alert for unusual email volume (potential security issue)
aws cloudwatch put-metric-alarm \
    --alarm-name "Marina-Email-Unusual-Volume" \
    --alarm-description "WARNING: Marina email volume unusually high - Check for security issues" \
    --metric-name "Send" \
    --namespace "AWS/SES" \
    --statistic "Sum" \
    --period 3600 \
    --threshold 1000 \
    --comparison-operator "GreaterThanThreshold" \
    --evaluation-periods 1 \
    --alarm-actions "arn:aws:sns:ap-southeast-1:ACCOUNT-ID:marina-email-alerts" \
    --region ap-southeast-1
```

### Email Sending Failures Alert
```bash
# Alert for high failure rate
aws cloudwatch put-metric-alarm \
    --alarm-name "Marina-Email-High-Failure-Rate" \
    --alarm-description "WARNING: Marina email failure rate high - Check system health" \
    --metric-name "Bounce" \
    --namespace "AWS/SES" \
    --statistic "Sum" \
    --period 900 \
    --threshold 50 \
    --comparison-operator "GreaterThanThreshold" \
    --evaluation-periods 2 \
    --alarm-actions "arn:aws:sns:ap-southeast-1:ACCOUNT-ID:marina-email-alerts" \
    --region ap-southeast-1
```

### ✅ Alert Verification
```bash
# List all Marina email alarms
aws cloudwatch describe-alarms \
    --alarm-name-prefix "Marina-Email" \
    --region ap-southeast-1

# Test alarm notification (optional)
aws cloudwatch set-alarm-state \
    --alarm-name "Marina-Email-High-Bounce-Rate" \
    --state-value "ALARM" \
    --state-reason "Testing alarm notification system" \
    --region ap-southeast-1
```

> **Status Check**: All alarms show "OK" state in CloudWatch console
> **Notification Test**: Verify Marina team receives alert emails
> **Next**: Implement application-level logging

---

## 📝 Step 3: Application Logging Setup

> **Purpose**: Detailed audit trail for government compliance and troubleshooting
> **Requirements**: Privacy protection, structured logging, retention policies
> **Compliance**: Data Privacy Act, government audit requirements

### Enhanced Logging Class
```python
import logging
import json
from datetime import datetime
import boto3
from typing import Dict, Any

class MarinaEmailLogger:
    def __init__(self, log_group_name='marina-email-service'):
        """Initialize Marina-specific logging with CloudWatch integration"""
        self.log_group_name = log_group_name
        self.setup_logging()
        self.cloudwatch_logs = boto3.client('logs', region_name='ap-southeast-1')
        self.ensure_log_group_exists()
        
    def setup_logging(self):
        """Configure structured logging for government compliance"""
        # Create formatter for structured logs
        formatter = logging.Formatter(
            '%(asctime)s - %(name)s - %(levelname)s - %(message)s'
        )
        
        # File handler for local logs
        file_handler = logging.FileHandler('marina_email_audit.log')
        file_handler.setFormatter(formatter)
        file_handler.setLevel(logging.INFO)
        
        # Console handler for development
        console_handler = logging.StreamHandler()
        console_handler.setFormatter(formatter)
        console_handler.setLevel(logging.INFO)
        
        # Configure logger
        self.logger = logging.getLogger('MarinaEmailService')
        self.logger.setLevel(logging.INFO)
        self.logger.addHandler(file_handler)
        self.logger.addHandler(console_handler)
        
    def ensure_log_group_exists(self):
        """Create CloudWatch log group if it doesn't exist"""
        try:
            self.cloudwatch_logs.create_log_group(
                logGroupName=self.log_group_name,
                tags={
                    'Department': 'Marina-IT',
                    'Purpose': 'EmailAuditTrail',
                    'Compliance': 'DataPrivacyAct'
                }
            )
            
            # Set retention policy (7 years for government records)
            self.cloudwatch_logs.put_retention_policy(
                logGroupName=self.log_group_name,
                retentionInDays=2557  # 7 years
            )
            
        except self.cloudwatch_logs.exceptions.ResourceAlreadyExistsException:
            pass  # Log group already exists
            
    def log_email_attempt(self, email_type: str, recipient: str, success: bool, 
                         message_id: str = None, error: str = None, 
                         additional_data: Dict[str, Any] = None):
        """Log email sending attempt with privacy protection"""
        
        # Mask recipient email for privacy
        masked_recipient = self._mask_email(recipient)
        
        log_entry = {
            'timestamp': datetime.now().isoformat(),
            'event_type': 'email_send_attempt',
            'email_type': email_type,
            'recipient_masked': masked_recipient,
            'success': success,
            'message_id': message_id,
            'error': error,
            'service': 'marina-email-system',
            'version': '1.0'
        }
        
        # Add additional data if provided
        if additional_data:
            log_entry.update(additional_data)
        
        # Log locally
        if success:
            self.logger.info(f"Email sent successfully: {json.dumps(log_entry)}")
        else:
            self.logger.error(f"Email sending failed: {json.dumps(log_entry)}")
            
        # Send to CloudWatch (optional for high-volume scenarios)
        self._send_to_cloudwatch(log_entry)
        
    def log_system_event(self, event_type: str, description: str, 
                        severity: str = 'INFO', additional_data: Dict[str, Any] = None):
        """Log system events for operational monitoring"""
        
        log_entry = {
            'timestamp': datetime.now().isoformat(),
            'event_type': event_type,
            'description': description,
            'severity': severity,
            'service': 'marina-email-system'
        }
        
        if additional_data:
            log_entry.update(additional_data)
            
        if severity == 'ERROR':
            self.logger.error(f"System event: {json.dumps(log_entry)}")
        elif severity == 'WARNING':
            self.logger.warning(f"System event: {json.dumps(log_entry)}")
        else:
            self.logger.info(f"System event: {json.dumps(log_entry)}")
            
    def log_daily_summary(self, total_sent: int, total_failed: int, 
                         bounce_count: int, complaint_count: int):
        """Log daily email statistics for reporting"""
        
        success_rate = (total_sent / (total_sent + total_failed)) * 100 if (total_sent + total_failed) > 0 else 0
        
        summary = {
            'timestamp': datetime.now().isoformat(),
            'event_type': 'daily_summary',
            'date': datetime.now().date().isoformat(),
            'total_sent': total_sent,
            'total_failed': total_failed,
            'success_rate': round(success_rate, 2),
            'bounce_count': bounce_count,
            'complaint_count': complaint_count,
            'service': 'marina-email-system'
        }
        
        self.logger.info(f"Daily summary: {json.dumps(summary)}")
        
    def _mask_email(self, email: str) -> str:
        """Mask email address for privacy compliance"""
        if '@' in email:
            local, domain = email.split('@', 1)
            if len(local) > 2:
                masked_local = local[0] + '*' * (len(local) - 2) + local[-1]
            else:
                masked_local = local[0] + '*'
            return f"{masked_local}@{domain}"
        return email
        
    def _send_to_cloudwatch(self, log_entry: Dict[str, Any]):
        """Send log entry to CloudWatch (implement based on volume needs)"""
        # For high-volume scenarios, consider batching or sampling
        # This is a placeholder for CloudWatch Logs integration
        pass
```

### Integration with Email Service
```python
# Enhanced Marina Email Service with comprehensive logging
class MarinaEmailServiceWithLogging(MarinaEmailService):
    def __init__(self, region='ap-southeast-1'):
        super().__init__(region)
        self.audit_logger = MarinaEmailLogger()
        
    def send_otp_email(self, to_email: str, otp_code: str, 
                       service_name: str = "Marina Online Services") -> Dict[str, Any]:
        """Send OTP email with comprehensive logging"""
        
        # Log attempt start
        self.audit_logger.log_system_event(
            'otp_request_start',
            f'OTP email requested for service: {service_name}',
            additional_data={'service_name': service_name}
        )
        
        # Send email using parent method
        result = super().send_otp_email(to_email, otp_code, service_name)
        
        # Log attempt result
        self.audit_logger.log_email_attempt(
            email_type='otp_verification',
            recipient=to_email,
            success=result['success'],
            message_id=result.get('message_id'),
            error=result.get('error'),
            additional_data={
                'service_name': service_name,
                'otp_length': len(otp_code)
            }
        )
        
        return result
        
    def send_transaction_alert(self, to_email: str, transaction_data: Dict[str, Any]) -> Dict[str, Any]:
        """Send transaction alert with comprehensive logging"""
        
        # Log transaction start
        self.audit_logger.log_system_event(
            'transaction_alert_start',
            f'Transaction alert for type: {transaction_data.get("type", "Unknown")}',
            additional_data={
                'transaction_type': transaction_data.get('type'),
                'transaction_reference': transaction_data.get('reference')
            }
        )
        
        # Send email using parent method
        result = super().send_transaction_alert(to_email, transaction_data)
        
        # Log attempt result
        self.audit_logger.log_email_attempt(
            email_type='transaction_alert',
            recipient=to_email,
            success=result['success'],
            message_id=result.get('message_id'),
            error=result.get('error'),
            additional_data={
                'transaction_type': transaction_data.get('type'),
                'transaction_reference': transaction_data.get('reference'),
                'transaction_amount': transaction_data.get('amount')
            }
        )
        
        return result
```

---

## 📊 Step 4: Performance Monitoring

> **Purpose**: Track system performance and identify optimization opportunities
> **Metrics**: Response times, throughput, error rates, cost efficiency
> **Government Focus**: Service level compliance and citizen satisfaction

### Custom CloudWatch Metrics
```python
import boto3
from datetime import datetime

class MarinaPerformanceMonitor:
    def __init__(self, region='ap-southeast-1'):
        self.cloudwatch = boto3.client('cloudwatch', region_name=region)
        self.namespace = 'Marina/EmailService'
        
    def record_email_latency(self, email_type: str, latency_ms: float):
        """Record email sending latency for performance monitoring"""
        self.cloudwatch.put_metric_data(
            Namespace=self.namespace,
            MetricData=[
                {
                    'MetricName': 'EmailLatency',
                    'Dimensions': [
                        {
                            'Name': 'EmailType',
                            'Value': email_type
                        }
                    ],
                    'Value': latency_ms,
                    'Unit': 'Milliseconds',
                    'Timestamp': datetime.now()
                }
            ]
        )
        
    def record_success_rate(self, email_type: str, success_count: int, total_count: int):
        """Record success rate for different email types"""
        success_rate = (success_count / total_count) * 100 if total_count > 0 else 0
        
        self.cloudwatch.put_metric_data(
            Namespace=self.namespace,
            MetricData=[
                {
                    'MetricName': 'SuccessRate',
                    'Dimensions': [
                        {
                            'Name': 'EmailType',
                            'Value': email_type
                        }
                    ],
                    'Value': success_rate,
                    'Unit': 'Percent',
                    'Timestamp': datetime.now()
                }
            ]
        )
        
    def record_citizen_satisfaction_metric(self, service_type: str, rating: float):
        """Record citizen satisfaction metrics (from feedback systems)"""
        self.cloudwatch.put_metric_data(
            Namespace=self.namespace,
            MetricData=[
                {
                    'MetricName': 'CitizenSatisfaction',
                    'Dimensions': [
                        {
                            'Name': 'ServiceType',
                            'Value': service_type
                        }
                    ],
                    'Value': rating,
                    'Unit': 'None',
                    'Timestamp': datetime.now()
                }
            ]
        )
```

### Performance Dashboard Widget
```bash
# Add performance metrics to dashboard
cat > performance-widget.json << 'EOF'
{
    "type": "metric",
    "properties": {
        "metrics": [
            [ "Marina/EmailService", "EmailLatency", "EmailType", "otp_verification" ],
            [ ".", ".", ".", "transaction_alert" ],
            [ ".", "SuccessRate", ".", "otp_verification" ],
            [ ".", ".", ".", "transaction_alert" ]
        ],
        "view": "timeSeries",
        "region": "ap-southeast-1",
        "title": "Marina Email Performance Metrics",
        "period": 300,
        "stat": "Average"
    }
}
EOF
```

---

## 🔍 Step 5: Log Analysis and Reporting

> **Purpose**: Generate reports for government compliance and operational insights
> **Audience**: Management, auditors, operations team
> **Frequency**: Daily, weekly, monthly reports

### Automated Daily Report Script
```python
import boto3
from datetime import datetime, timedelta
import json

class MarinaEmailReporter:
    def __init__(self, region='ap-southeast-1'):
        self.cloudwatch = boto3.client('cloudwatch', region_name=region)
        self.ses = boto3.client('ses', region_name=region)
        
    def generate_daily_report(self, date=None):
        """Generate comprehensive daily email report"""
        if date is None:
            date = datetime.now().date()
            
        start_time = datetime.combine(date, datetime.min.time())
        end_time = start_time + timedelta(days=1)
        
        # Get SES statistics
        ses_stats = self._get_ses_statistics(start_time, end_time)
        
        # Get custom metrics
        performance_stats = self._get_performance_statistics(start_time, end_time)
        
        # Generate report
        report = {
            'report_date': date.isoformat(),
            'generated_at': datetime.now().isoformat(),
            'email_statistics': ses_stats,
            'performance_metrics': performance_stats,
            'compliance_status': self._check_compliance_status(ses_stats),
            'recommendations': self._generate_recommendations(ses_stats, performance_stats)
        }
        
        # Save report
        report_filename = f"marina_email_report_{date.isoformat()}.json"
        with open(report_filename, 'w') as f:
            json.dump(report, f, indent=2)
            
        print(f"Daily report generated: {report_filename}")
        return report
        
    def _get_ses_statistics(self, start_time, end_time):
        """Get SES statistics for the specified time period"""
        try:
            # Get send statistics
            response = self.cloudwatch.get_metric_statistics(
                Namespace='AWS/SES',
                MetricName='Send',
                StartTime=start_time,
                EndTime=end_time,
                Period=86400,  # Daily
                Statistics=['Sum']
            )
            
            total_sent = sum(point['Sum'] for point in response['Datapoints'])
            
            # Get bounce statistics
            bounce_response = self.cloudwatch.get_metric_statistics(
                Namespace='AWS/SES',
                MetricName='Bounce',
                StartTime=start_time,
                EndTime=end_time,
                Period=86400,
                Statistics=['Sum']
            )
            
            total_bounces = sum(point['Sum'] for point in bounce_response['Datapoints'])
            
            # Get complaint statistics
            complaint_response = self.cloudwatch.get_metric_statistics(
                Namespace='AWS/SES',
                MetricName='Complaint',
                StartTime=start_time,
                EndTime=end_time,
                Period=86400,
                Statistics=['Sum']
            )
            
            total_complaints = sum(point['Sum'] for point in complaint_response['Datapoints'])
            
            # Calculate rates
            bounce_rate = (total_bounces / total_sent) * 100 if total_sent > 0 else 0
            complaint_rate = (total_complaints / total_sent) * 100 if total_sent > 0 else 0
            
            return {
                'total_sent': int(total_sent),
                'total_bounces': int(total_bounces),
                'total_complaints': int(total_complaints),
                'bounce_rate': round(bounce_rate, 3),
                'complaint_rate': round(complaint_rate, 3)
            }
            
        except Exception as e:
            return {'error': str(e)}
            
    def _check_compliance_status(self, stats):
        """Check if email metrics meet government service standards"""
        compliance = {
            'bounce_rate_compliant': stats.get('bounce_rate', 0) < 5.0,
            'complaint_rate_compliant': stats.get('complaint_rate', 0) < 0.1,
            'overall_compliant': True
        }
        
        compliance['overall_compliant'] = (
            compliance['bounce_rate_compliant'] and 
            compliance['complaint_rate_compliant']
        )
        
        return compliance
        
    def _generate_recommendations(self, ses_stats, performance_stats):
        """Generate operational recommendations based on metrics"""
        recommendations = []
        
        if ses_stats.get('bounce_rate', 0) > 3.0:
            recommendations.append("Consider implementing email validation to reduce bounce rate")
            
        if ses_stats.get('complaint_rate', 0) > 0.05:
            recommendations.append("Review email content and frequency to reduce complaints")
            
        if ses_stats.get('total_sent', 0) > 10000:
            recommendations.append("Consider requesting higher SES sending limits")
            
        return recommendations

# Usage
reporter = MarinaEmailReporter()
daily_report = reporter.generate_daily_report()
```

---

## ✅ Monitoring Setup Checklist

### Completed Monitoring Components
- [ ] **CloudWatch Dashboard**: Real-time email system visibility
- [ ] **Critical Alerts**: Bounce rate, complaint rate, volume anomalies
- [ ] **Application Logging**: Comprehensive audit trail with privacy protection
- [ ] **Performance Monitoring**: Latency, success rates, citizen satisfaction
- [ ] **Automated Reporting**: Daily, weekly, monthly operational reports
- [ ] **Compliance Tracking**: Government audit requirements met

### Operational Procedures
- [ ] **Daily Monitoring**: Check dashboard for system health
- [ ] **Alert Response**: Procedures for critical alert handling
- [ ] **Weekly Reviews**: Analyze trends and performance
- [ ] **Monthly Reports**: Generate compliance and performance reports
- [ ] **Quarterly Audits**: Full system review and optimization

---

## 🎯 Success Criteria

**Phase 6 Complete When:**
- ✅ Real-time monitoring dashboard operational
- ✅ Critical alerts configured and tested
- ✅ Comprehensive logging with privacy protection
- ✅ Performance metrics tracking citizen satisfaction
- ✅ Automated reporting for government compliance
- ✅ Operations team trained on monitoring procedures

**Ready for Next Phase:**
- [Production Deployment](./07-deployment.md)
- Full system operational readiness
- Government compliance verification

---

**📅 Estimated Time**: 1-2 hours  
**👥 Key Roles**: AWS Admin, Operations Team, Compliance Officer  
**🎯 Outcome**: Production-grade monitoring ensuring reliable email service for Filipino citizens
