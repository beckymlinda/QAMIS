Student Admission & Fee Management Module
Objective
Extend the current HEQAMIS system to support online student applications, admissions, enrollment, and fee management while preserving all existing functionality.

This module should follow a workflow similar to the NCHE online application system, allowing prospective students to apply online, track their application status, and transition into fully enrolled students.

1. Public Student Application Portal
Create a public application page that does not require an existing student account.

The application form should collect:

Personal Information
First Name

Last Name

Email Address

Phone Number

Date of Birth / Age

Gender

Physical Address / Location

Nationality (optional)

Programme Selection
The applicant should be able to select one programme from a list of programmes currently open for applications.

Each programme should display:

Programme Name

Faculty / Department

Duration

Entry Requirements

Required Grades

Application Fee

Tuition Fee

Short Description

Required Uploads
Applicants must upload:

National ID or Passport (optional)

Academic Certificate(s)

Examination Results

Passport Photo (optional)

Proof of Application Fee Payment

Accepted formats:

PDF

JPG

PNG

Maximum upload size should be configurable.

Payment Information
The application page should clearly display payment instructions configured by the administrator.

Example:

Application Fee: MK10,000

Bank Name

Account Number

Account Name

Mobile Money Option

Reference Number Instructions

This information should be editable by the administrator without modifying the code.

2. Programme Management (Admin)
The administrator should be able to create and manage programmes.

Each programme should include:

Programme Name

Faculty

Department

Duration

Tuition Fee

Application Fee

Entry Requirements

Required Grades

Maximum Intake

Application Closing Date

Whether Applications are Open or Closed

3. Application Workflow
Application statuses should include:

Submitted

↓

Under Review

↓

Reviewed

↓

Approved

or

Rejected

or

Waiting List

Every status change should automatically notify the applicant.

Notifications should be available:

Inside the student portal

By email

4. Student Application Dashboard
Applicants should have a simple dashboard.

Since they are not yet enrolled students, the sidebar should contain only:

Dashboard

Start Application

My Applications

Notifications

Profile

They must not see:

Courses

LMS

Timetable

Grades

Attendance

Evaluations

Those features become available only after enrollment.

5. Application Tracking
Applicants should see a timeline.

Example

Application Submitted

↓

Application Under Review

↓

Documents Verified

↓

Decision Made

↓

Enrolled

Every step should display:

Date

Status

Officer comments (optional)

6. Admission Decision
When an application is approved:

The administrator should be able to click

"Enroll Student"

The system should automatically:

Create a Student Account

Generate Student Number

Assign Programme

Assign Academic Year

Assign Semester

Activate Student Portal

The applicant automatically transitions into a regular student.

7. Student Sidebar After Enrollment
After enrollment, unlock all existing student modules.

Additionally add a new menu:

Fees & Payments
(or simply Finance)

This section becomes the student's financial portal.

8. Student Finance Portal
Students should see:

Total Tuition

Amount Paid

Outstanding Balance

Payment History

Approved Payments

Pending Verification

Rejected Payments

Students should also be able to upload new tuition payments.

Payment form:

Amount Paid

Payment Date

Bank / Mobile Money

Reference Number

Receipt Upload

Submit

After submission:

Status

Pending Verification

9. Accounts Officer Role
Create a new user role:

Accounts Officer
Permissions:

View Tuition Payments

View Application Fee Payments

Download Uploaded Receipts

Approve Payments

Reject Payments

Add Comments

Search Payments

Filter by:

Pending

Approved

Rejected

Programme

Student

Date

10. Payment Approval Workflow
Student uploads receipt

↓

Accounts Officer reviews receipt

↓

Approve or Reject

If Approved

Student balance automatically reduces.

Payment history updates.

Student receives:

Portal Notification

Email Notification

If Rejected

Student receives:

Reason for rejection

Can upload another receipt.

11. Programme Fee Management
Administrators should configure:

Application Fee

Registration Fee

Tuition Fee

Other Charges

These fees should automatically appear:

During application

Inside student finance portal

During enrollment

12. Application Fee Verification
Accounts Officer should also verify application fee receipts.

Workflow:

Application Submitted

↓

Receipt Uploaded

↓

Accounts Verifies

↓

Application Moves to Review Queue

Applications with unverified payments should not proceed for academic review.

13. Notifications
Notify applicants and students whenever:

Application Submitted

Application Under Review

Application Approved

Application Rejected

Student Enrolled

Application Fee Approved

Application Fee Rejected

Tuition Payment Approved

Tuition Payment Rejected

Balance Updated

Notifications should appear both:

Inside the portal

By email

14. Admin Dashboard
Add admission statistics.

Example:

Applications Received

Applications Under Review

Approved

Rejected

Pending Payments

Students Enrolled

Application Revenue

Outstanding Tuition

15. Design Requirements
Maintain the existing HEQAMIS branding.

Use:

Modern Bootstrap 5 cards

Responsive layouts

Progress timelines

Status badges

Bootstrap Icons

Soft shadows

Rounded corners

Clean typography

Professional spacing

The applicant dashboard should feel similar to the NCHE online application portal, while the enrolled student portal should remain a full university ERP/LMS experience.

Important
Do not modify existing modules or business logic.

Implement this as an extension of the current system.

Ensure:

Existing enrolled students continue using the current portal without disruption.

Only applicants see the limited sidebar and admission workflow.

Once enrollment is complete, the student's interface automatically changes to the full student portal with access to LMS, results, timetable, and the new Finance / Fees & Payments module.

This architecture keeps the system scalable and mirrors how most modern university management systems separate the admissions, finance, and student academic lifecycle