# 📖 Prerequisites & Planning
## Marina AWS SES Email System Requirements

> **Phase 1**: Foundation setup and requirements verification before technical implementation.

---

## 🎯 Project Overview

**Objective**: Implement production-ready email notification system for Marina using AWS SES
**Domain**: marina.gov.ph
**Primary Use Cases**: OTP, alerts, notifications, marketing
**Target**: Filipino citizens and maritime industry stakeholders

---

## ✅ AWS Account Requirements

### Account Access
- [ ] **AWS Account Available**: Confirmed AWS Partner status
- [ ] **Billing Setup**: Active payment method configured
- [ ] **Region Selection**: Asia Pacific (Singapore) - ap-southeast-1
- [ ] **Root Account Security**: MFA enabled on root account

### Service Limits Check
```bash
# Check current SES status
aws ses get-send-quota --region ap-southeast-1
aws ses get-account-sending-enabled --region ap-southeast-1

# Expected initial response: Sandbox mode, 200 emails/day limit
```

---

## 🏛️ Domain & DNS Requirements

### Domain Ownership
- [ ] **Domain Control**: Full administrative access to marina.gov.ph
- [ ] **DNS Management**: Ability to add TXT and CNAME records
- [ ] **Government Verification**: Domain ownership documentation ready

### DNS Access Requirements
**Records You'll Need to Add:**
```
# Domain verification
TXT: _amazonses.marina.gov.ph → (AWS verification token)

# DKIM authentication (3 records)
CNAME: token1._domainkey.marina.gov.ph → token1.dkim.amazonses.com
CNAME: token2._domainkey.marina.gov.ph → token2.dkim.amazonses.com  
CNAME: token3._domainkey.marina.gov.ph → token3.dkim.amazonses.com

# SPF record (recommended)
TXT: marina.gov.ph → "v=spf1 include:amazonses.com ~all"

# DMARC policy (recommended)
TXT: _dmarc.marina.gov.ph → "v=DMARC1; p=quarantine; rua=mailto:dmarc@marina.gov.ph"
```

### DNS Team Coordination
- [ ] **DNS Team Contact**: Identified person responsible for DNS changes
- [ ] **Change Process**: Understood procedure for DNS modifications
- [ ] **Timeline**: DNS propagation can take up to 72 hours
- [ ] **Backup Plan**: Rollback procedure if issues occur

---

## 👥 Team & Roles

### Required Team Members
- [ ] **Project Manager**: Overall coordination and timeline management
- [ ] **AWS Administrator**: SES setup and IAM configuration
- [ ] **Developer**: Application integration and testing
- [ ] **DNS Administrator**: Domain record management
- [ ] **Security Officer**: Policy review and compliance
- [ ] **Operations**: Monitoring and maintenance

### Skill Requirements
**AWS Administrator:**
- AWS Console navigation
- Basic CLI usage
- IAM policy understanding

**Developer:**
- Python or Node.js experience
- API integration knowledge
- Email template development

**DNS Administrator:**
- DNS record management
- Domain verification process
- Troubleshooting DNS issues

---

## 🔧 Technical Environment

### Development Setup
- [ ] **AWS CLI Installed**: Version 2.x recommended
- [ ] **Credentials Configured**: AWS profile setup
- [ ] **Programming Language**: Python 3.8+ or Node.js 16+
- [ ] **Code Repository**: Git repository for version control

### AWS CLI Configuration
```bash
# Install AWS CLI v2
curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
unzip awscliv2.zip
sudo ./aws/install

# Configure credentials
aws configure --profile marina-ses
# AWS Access Key ID: (from IAM user)
# AWS Secret Access Key: (from IAM user)  
# Default region: ap-southeast-1
# Default output format: json

# Test configuration
aws sts get-caller-identity --profile marina-ses
```

### Development Dependencies
**Python Requirements:**
```bash
pip install boto3 python-dotenv
```

**Node.js Requirements:**
```bash
npm install aws-sdk dotenv
```

---

## 📋 Compliance & Security

### Government Requirements
- [ ] **Data Privacy Act Compliance**: Email handling procedures documented
- [ ] **Security Policies**: Government IT security requirements reviewed
- [ ] **Audit Trail**: Logging and monitoring requirements defined
- [ ] **Backup Procedures**: Data retention and backup policies

### Security Considerations
- [ ] **Credential Management**: Secure storage for AWS keys
- [ ] **Network Security**: VPC and security group planning
- [ ] **Encryption**: Data in transit and at rest requirements
- [ ] **Access Control**: Principle of least privilege implementation

---

## 💰 Budget Planning

### AWS SES Costs (Philippines)
```
Email Volume Estimates:
- OTP emails: 5,000/month
- Transaction alerts: 3,000/month  
- Marketing emails: 2,000/month
- System notifications: 1,000/month
Total: ~11,000 emails/month

Cost Calculation:
- Under 62,000/month: FREE (if sent from EC2)
- Under 1,000/month: FREE (if sent externally)
- Marina estimate: $0-5/month for email sending

Additional Costs:
- CloudWatch monitoring: $5-10/month
- Total estimated: $5-15/month
```

### Cost Optimization
- [ ] **EC2 Deployment**: Consider EC2 hosting for free tier benefits
- [ ] **Monitoring Level**: Basic vs detailed CloudWatch metrics
- [ ] **Volume Planning**: Estimate growth for capacity planning

---

## 📅 Project Timeline

### Phase Planning (11-15 hours total)
```
Week 1: Prerequisites & Setup (4-6 hours)
├── Day 1: Prerequisites verification (1 hour)
├── Day 2: AWS SES setup and domain verification (2-3 hours)  
└── Day 3: Production access request submission (1 hour)

Week 2: Development & Testing (4-6 hours)
├── Day 1: IAM setup and security configuration (1 hour)
├── Day 2: Application code development (3-4 hours)
└── Day 3: Testing and validation (1-2 hours)

Week 3: Production & Monitoring (3-4 hours)
├── Day 1: Monitoring setup (1-2 hours)
├── Day 2: Production deployment (1 hour)
└── Day 3: Documentation and training (1 hour)
```

### Critical Path Items
- [ ] **Production Access Request**: Submit early (24-48 hour approval)
- [ ] **DNS Changes**: Allow 72 hours for propagation
- [ ] **Team Coordination**: Schedule DNS changes in advance
- [ ] **Testing Window**: Plan testing during low-traffic periods

---

## 🚨 Risk Assessment

### Technical Risks
| Risk | Impact | Mitigation |
|------|--------|------------|
| DNS propagation delays | High | Submit DNS changes early, monitor propagation |
| Production access denial | High | Use government use case template, provide detailed justification |
| Email deliverability issues | Medium | Implement DKIM, SPF, DMARC properly |
| Rate limiting | Low | Implement proper rate limiting in application |

### Operational Risks
| Risk | Impact | Mitigation |
|------|--------|------------|
| Team availability | Medium | Cross-train multiple team members |
| DNS team delays | Medium | Coordinate DNS changes in advance |
| Security policy conflicts | Low | Review policies early, get security approval |

---

## ✅ Prerequisites Checklist

**Before Starting Technical Implementation:**
- [ ] All team members identified and available
- [ ] AWS account access confirmed
- [ ] DNS change process understood and approved
- [ ] Security and compliance requirements reviewed
- [ ] Budget approved and allocated
- [ ] Timeline agreed upon by all stakeholders
- [ ] Risk mitigation plans in place

**Ready to Proceed When:**
- [ ] All checklist items completed
- [ ] Team kickoff meeting held
- [ ] Communication channels established
- [ ] Next phase scheduled

---

## 🚀 Next Steps

**After Prerequisites Complete:**
1. **Begin Technical Setup**: [SES Configuration Guide](./02-ses-setup.md)
2. **Submit Production Request**: Use provided government template
3. **Coordinate DNS Changes**: Schedule with DNS team
4. **Set Up Development Environment**: Install tools and dependencies

**Questions or Issues?**
- Review [Troubleshooting Guide](./troubleshooting.md)
- Check [Sandbox vs Production](./sandbox-production.md) for approval process
- Contact AWS Support for account-specific issues

---

**📅 Estimated Completion**: 1-2 days  
**👥 Key Stakeholders**: Project Manager, AWS Admin, DNS Team  
**🎯 Success Criteria**: All prerequisites verified and team ready for technical implementation
