## **SOFTWARE REQUIREMENTS SPECIFICATION (SRS)** 

## **Quality Management System for Higher Compliance Monitoring** 

## Version 1.0 

Page **1** of **13** 

## **1. Introduction** 

## **1.1 Purpose** 

The purpose of this Software Requirements Specification (SRS) is to define the functional and non-functional requirements for a web-based Quality Management System (QMS) that enables higher education institutions to: 

- Capture institutional and programme data. 

- Assess compliance against NCHE Minimum Standards. 

- Support evidence-based self-assessment. 

- Automatically calculate compliance scores. 

- Generate institutional self-assessment reports. 

- Generate annual quality assurance reports. 

- Produce accreditation recommendations and readiness decisions. 

- Track corrective actions and continuous improvement initiatives. 

## **1.2 Scope** 

The system shall support: 

- Institutional accreditation assessments. 

- Programme accreditation assessments. 

- Internal quality assurance reviews. 

- Annual institutional reporting. 

- Compliance monitoring. 

- Evidence management. 

- Dashboard reporting. 

- Decision support for accreditation readiness. 

The system shall use NCHE Minimum Standards as benchmarks and institutional/programme assessment tools as scoring instruments. 

## **2. Overall Description** 

## **2.1 Users** 

Page **2** of **13** 

|**Role**|**Responsibilities**|
|---|---|
|System Administrator|User management,system confguration|
|Institution Administrator|Manage institutionalprofle|
|QualityAssurance Ofcer|Coordinate assessments and reports|
|Data EntryOfcer|Enter compliance data and upload evidence|
|Department Head|Completeprogramme assessments|
|Reviewer/Assessor|Review evidence and assign scores|
|Executive Management|View dashboards and reports|
|Council/Board|Access summaryreports|
|External Evaluator|Read-onlyaccess for accreditation reviews|



## **3. Functional Requirements** 

## **FR1 Institution Profile Management** 

The system shall allow users to maintain: 

- Institution details 

- Vision and mission 

- Core values 

- Strategic plan 

- Governance structures 

- Faculties 

- Schools 

- Departments 

- Centres 

- Programmes 

- Campuses 

- Contact information 

## **FR2 Programme Management** 

The system shall allow users to: 

- Register programmes 

- Define programme attributes 

- Record mode of delivery 

- Capture accreditation status 

- Maintain curriculum review dates 

Page **3** of **13** 

- Link programmes to departments 

- Check NCHE accreditation status of the programmes 

- Track professional body accreditation 

## **FR3 Standards Repository** 

The system shall maintain a configurable repository of: 

- NCHE Minimum Standards 

- Assessment criteria 

- Indicators 

- Mandatory requirements 

- Weightings 

- Scoring rubrics 

- Compliance thresholds 

Administrators shall be able to update standards and output criteria without software redevelopment. 

## **FR4 Assessment Tool Management** 

The system shall digitize institutional and programme assessment tools including: 

- Governance 

- Financial resources 

- Infrastructure 

- Water and sanitation 

- Library resources 

- ICT 

- Academic programmes 

- Staff complement 

- Student support 

- Student admissions 

- Quality assurance systems 

- Research 

- Laboratories 

- Safety 

- Learning resources 

Page **4** of **13** 

Each item shall allow: 

- Numerical score (0–4) 

- Comments 

- Evidence uploads 

- Reviewer observations 

- Improvement recommendations 

- Strength identified 

## **FR5 Evidence Repository** 

The system shall support upload and management of: 

- Policies 

- Strategic plans 

- Financial statements 

- Academic regulations 

- Committee minutes 

- Curriculum documents 

- Staff CVs 

- Qualification certificates 

- Audit reports 

- Photographs 

- Licences 

- Accreditation certificates 

- PDFs 

- Office documents 

- Images 

Version control shall be supported. 

## **FR6 Automated Compliance Engine** 

The system shall automatically: 

- Compare entered data with NCHE standards. 

- Calculate compliance percentages. 

- Flag mandatory criteria not met (starred areas with a score of less than 3) 

Page **5** of **13** 

- Identify gaps. 

- Produce risk levels. 

- Highlight critical deficiencies (Starred areas) 

## **FR7 Accreditation Decision Engine** 

The system shall automatically determine: 

- Fully compliant (Average score of 3 and a score of 3 and above in all the critical areas) 

- Partially compliant (Average score of 2 to 2.99 and a score of 3 and above in all the critical areas) 

- Non-compliant ((Average score of 0 to 1.99 and with a score of less than 3 in at least in of the critical areas) 

Decision logic shall include: 

- Overall compliance score. 

- Mandatory criterion performance. 

- Programme-specific thresholds. 

- Institutional threshold requirements. 

- Weighted scoring. 

- Outstanding corrective actions( Areas of improvements) 

The system shall provide a recommendation: 

- Ready for Accreditation 

- Accreditation with Conditions 

- Deferred Pending Improvements 

- Not Ready for Accreditation 

## **FR8 Self-Assessment Module based on the data input in the system** 

The system shall automatically produce a Self-Assessment Report containing: 

- Executive summary 

- Institutional background 

Page **6** of **13** 

- Governance 

- Policies 

- Staff profile 

- Student profile 

- SWOT analysis 

- Institutional assessment scores 

- Programme assessment scores 

- Strengths for each assessed area 

- Weaknesses for each assessed areas 

- Observations 

- Recommendations 

- Progress on previous recommendations 

- Appendices 

The report shall be exportable to PDF and Word. 

## **FR9 Annual Report Generator based on the data input in the system** 

The system shall automatically generate annual reports, including: 

- Institutional profile 

- Registration status 

- Mission 

- Vision 

- Values 

- Strategic plan implementation 

- Faculties 

- Programmes 

- Staffing 

- Student enrolment 

- Curriculum reviews 

- Research output 

- Infrastructure developments 

- Quality assurance activities 

- Compliance status 

- Financial summaries 

- Challenges 

- plans 

Page **7** of **13** 

Reports shall be exportable in PDF and Word. 

## **FR10 Dashboard** 

Provide dashboards displaying: 

- Overall compliance percentage 

- Compliance with the standard 

- Compliance by faculty 

- Compliance by programme 

- Accreditation readiness 

- Outstanding actions 

- Evidence completeness 

- Risk indicators 

- Trend analysis 

- Historical comparisons 

## **FR11 Corrective Action Management** 

Users shall be able to: 

- Record findings 

- Assign actions 

- Assign responsible persons 

- Set deadlines 

- Monitor progress 

- Record completion evidence 

- Escalate overdue actions 

## **FR12 Workflow** 

Support workflows: 

Draft → Submitted → Reviewed → Approved → Locked 

Notifications shall be sent at each stage. 

Page **8** of **13** 

## **FR13 Notifications** 

The system shall notify users regarding: 

- Missing evidence 

- Pending reviews 

- Upcoming deadlines 

- Expiring accreditations 

- Overdue actions 

- Report submissions 

Notifications may be sent via email and in-system alerts. 

## **FR14 Search** 

Provide advanced search across: 

- Institutions 

- Departments 

- Programmes 

- Standards 

- Documents 

- Assessments 

- Evidence 

- Reports 

## **FR15 Audit Trail** 

Record: 

- User 

- Timestamp 

- Activity 

- Previous value 

- New value 

Audit logs shall be immutable. 

Page **9** of **13** 

## **4. Compliance Scoring** 

## **4.1 Scoring Scale** 

|**Score**|**Meaning**|
|---|---|
|0|Poor /<br>Unavailable|
|1|Insufcient|
|2|Satisfactory|
|3|Good|
|4|Excellent|



Mandatory criteria shall require a minimum score of 3. 

## **5. Reports** 

The system shall generate: 

- Self-assessment reports 

- Annual reports 

- Programme reports 

- Institutional reports 

- Gap analysis reports 

- Accreditation readiness reports 

- Compliance summaries 

- Executive dashboards 

- Faculty reports 

- Department reports 

Page **10** of **13** 

- Evidence inventories 

- Corrective action registers 

## **6. Non-Functional Requirements** 

## **Performance** 

- Response time < 3 seconds for standard operations. 

- Generate reports within 30 seconds. 

## **Security** 

- Role-based access control. 

- Multi-factor authentication (optional). 

- Encrypted communications. 

- Encrypted backups. 

## **Availability** 

- 99.5% uptime. 

## **Scalability** 

- Support multiple institutions and campuses. 

- Support unlimited programmes and assessments. 

## **Usability** 

- Responsive web interface. 

- Mobile compatible. 

- Accessible to users with disabilities. 

## **Maintainability** 

- Configurable standards and scoring. 

- Modular architecture. 

- API support for integration. 

## **7. Suggested Database Entities** 

Page **11** of **13** 

- Users 

- Roles 

- Institutions 

- Faculties 

- Departments 

- Programmes 

- Standards 

- Indicators 

- Assessments 

- Assessment Scores 

- Evidence Documents 

- Compliance Results 

- Corrective Actions 

- Recommendations 

- Annual Reports 

- Self-Assessment Reports 

- Notifications 

- Audit Logs 

## **8. Business Rules** 

1. Mandatory accreditation criteria must achieve at least the configured minimum score. 

2. Missing mandatory evidence shall prevent a “Ready for Accreditation” recommendation. 

3. All uploaded evidence shall be linked to one or more standards. 

4. Reports shall be generated from live assessment data. 

5. Historical assessments shall remain read-only after approval. 

6. Every recommendation shall support action tracking and status updates. 

7. Compliance dashboards shall update automatically after assessment changes. 

## **9. Integration Requirements** 

The system should support: 

- Document management systems 

- Student Information Systems (SIS) 

Page **12** of **13** 

- Human Resource Management Systems (HRMS) 

- Finance systems 

- Learning Management Systems (LMS) 

- Email services 

- National regulatory reporting interfaces 

## **10. Expected Outputs** 

- Institutional compliance scorecards 

- Programme compliance scorecards 

- Accreditation readiness status 

- Self-assessment reports 

- Annual reports 

- SWOT analyses 

- Gap analyses 

- Corrective action plans 

- Management dashboards 

- Executive summaries 

- Board-ready quality assurance reports 

- Evidence registers 

Page **13** of **13** 

