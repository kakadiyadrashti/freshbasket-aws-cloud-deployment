# FreshBasket AWS Cloud Deployment

FreshBasket is a simple cloud-based marketplace application deployed on AWS for Cloud Computing and Software as a Service Assignment 3.

The project demonstrates a scalable, highly available, and fault-tolerant AWS architecture using Elastic Beanstalk, EC2, Auto Scaling, Load Balancer, custom VPC, custom AMI, security groups, Amazon RDS MySQL Multi-AZ, and SNS email notifications.

## Project Overview

FreshBasket was originally described as a small Sydney-based online marketplace running from a single desktop PC. This project migrates the application to AWS using a cloud-native architecture designed for scalability and disaster recovery.

The deployed PHP application allows users to submit FreshBasket orders. Each order is written to Amazon RDS MySQL and immediately read back into the Recent Orders table. This proves end-to-end connectivity through the AWS architecture.

## Architecture Flow

User Browser  
→ Internet Gateway  
→ Application Load Balancer  
→ Elastic Beanstalk Environment  
→ EC2 Auto Scaling Group  
→ Amazon RDS MySQL Multi-AZ  
→ Amazon SNS Email Notifications

## AWS Services Used

- AWS Elastic Beanstalk
- Amazon EC2
- Application Load Balancer
- Auto Scaling Group
- Custom VPC
- Public subnets in two Availability Zones
- Custom EC2 Security Group
- Custom RDS Security Group
- Amazon RDS MySQL Multi-AZ
- Custom AMI
- Custom Key Pair
- Amazon SNS Email Notifications
- LabRole and LabInstanceProfile from AWS Academy Learner Lab

## Application Features

- PHP 8.5 web application
- FreshBasket marketplace dashboard
- RDS connection status
- Order submission form
- Recent orders table
- Live database read/write confirmation
- EC2 instance and Availability Zone display
- Architecture flow summary on page

## Auto Scaling Configuration

- Minimum instances: 2
- Maximum instances: 8
- Scale out trigger: CPUUtilization > 60%
- Scale in trigger: CPUUtilization < 30%

## Database

- Engine: Amazon RDS MySQL
- Version: MySQL 8.4
- Deployment: Multi-AZ
- Database name: freshbasket
- Table used: orders

## Security Configuration

### EC2 Security Group

Inbound rules:

- HTTP port 80
- SSH port 22

### RDS Security Group

Inbound rules:

- MySQL/Aurora port 3306
- Source restricted to EC2 Security Group only

## Environment Variables

Set these values in Elastic Beanstalk environment properties:

```text
DB_HOST=your-rds-endpoint-or-private-ip
DB_USER=admin
DB_PASS=your-rds-password
DB_NAME=freshbasket
