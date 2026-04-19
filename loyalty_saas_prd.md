# Loyalty SaaS PRD

## 1. Document Information

- **Product Name:** QR-Based Loyalty SaaS for Offline Merchants
- **Document Type:** Product Requirements Document (PRD)
- **Version:** 1.0
- **Status:** Draft for Product, Design, and Engineering Alignment
- **Primary Use Case:** Offline stores such as juice shops, cafes, snack points, dessert outlets, and similar quick-service merchants
- **Deployment Model:** Multi-tenant SaaS
- **Client Surface:** Customer web app + Staff web app + Merchant admin web app

---

## 2. Executive Summary

This product is a web-based loyalty SaaS for offline merchants that helps stores enroll walk-in customers into a repeat-purchase program without requiring app download, prepaid subscription, WhatsApp dependency, or SMS OTP as part of the core flow.

Customers scan a store QR, join a loyalty plan using name and phone number, and receive a digital membership pass with a member QR. At the counter, staff scan the pass or search by phone number to add eligible purchases. Rewards unlock automatically based on plan rules such as **Buy 10, Get 2 Free**. Customers redeem rewards by presenting their pass QR, and staff confirm redemption.

The system is designed for real-world retail conditions:
- low billing maturity
- high cashier speed requirements
- intermittent network quality
- staff turnover
- merchants with single or multiple branches
- need for auditability and fraud resistance without OTP friction

This is not a billing/POS system. This is a **loyalty event tracking platform** with merchant-grade controls, customer pass management, staff action logging, and scalable multi-tenant architecture.

---

## 3. Problem Statement

Offline merchants want to increase repeat purchases and customer retention, but existing workflows are broken because:

1. many stores do not issue itemized bills consistently
2. staff cannot maintain paper loyalty cards reliably
3. app-based loyalty has low adoption
4. prepaid subscription models reduce trust for first-time users
5. manual customer tracking leads to disputes and incorrect rewards
6. SMB merchants need simple operations, not enterprise POS complexity

Customers also face friction:
- they do not want to install an app
- they do not trust prepaid plans immediately
- they do not want a slow or confusing counter experience
- they expect rewards to be visible and reliable

The product must deliver a loyalty system that is:
- easy to join
- fast at checkout
- trustworthy
- hard to abuse
- scalable across many merchants and branches

---

## 4. Product Vision

Enable any offline merchant to run a modern, QR-first, no-app loyalty program where customers earn rewards through repeat purchases and staff can track progress accurately in seconds.

---

## 5. Product Goals

### Business Goals
- increase repeat visits for merchants
- improve customer retention and reward redemption rates
- provide merchants with measurable loyalty analytics
- create a scalable SaaS platform that supports many merchants and branches
- reduce operational errors in customer progress tracking

### User Goals

#### Customer Goals
- join instantly without app install
- see loyalty progress clearly
- trust that purchases are recorded correctly
- redeem rewards easily in store

#### Staff Goals
- identify member quickly
- add purchase in one fast workflow
- redeem rewards with confidence
- avoid mistakes and disputes

#### Merchant Goals
- configure plans easily
- see customer growth and loyalty performance
- audit staff actions
- correct errors safely with visibility

---

## 6. Non-Goals

The first production release will **not** attempt to be:
- a full POS or billing system
- an inventory management platform
- a CRM for outbound campaigns beyond basic notifications
- a marketplace or ordering platform
- a WhatsApp bot-first experience
- a prepaid subscription or wallet product
- a payment collection platform for the core loyalty flow

---

## 7. Product Principles

1. **No app install required**
2. **No OTP in the core flow**
3. **QR-first, phone-number fallback**
4. **Earning must be easy; redemption must be stricter**
5. **All loyalty actions must be auditable**
6. **Counter flow must complete within seconds**
7. **Merchant complexity must stay low**
8. **Architecture must support large scale and multi-tenancy from day one**

---

## 8. Core Product Model

### Core Flow
1. Merchant displays store loyalty QR at the counter.
2. Customer scans QR and opens mobile web flow.
3. Customer enters name and phone number.
4. Customer selects an eligible product and plan.
5. System creates customer account, membership, and digital pass.
6. Customer receives member QR in web pass.
7. At each eligible purchase, cashier scans member QR or searches by phone number.
8. Cashier taps **Add Purchase**.
9. System writes loyalty event and updates progress.
10. When milestone threshold is reached, reward balance unlocks automatically.
11. Customer redeems reward by presenting pass QR.
12. Cashier confirms redemption.
13. System writes redemption event and updates remaining balance.

### Example Plan
- **Plan Name:** Simple Juice Loyalty
- **Rule:** Buy 10 eligible orders, get 2 free
- **Tracking Unit:** 1 eligible order = 1 purchase credit
- **Reward Type:** fixed free-item credits

---

## 9. User Personas

### 9.1 Customer
- walk-in buyer
- repeat local customer
- price-sensitive but convenience-driven
- does not want app download
- expects fast recognition at the counter

### 9.2 Cashier / Counter Staff
- serves customers quickly
- minimal training tolerance
- high volume during rush periods
- must execute loyalty tasks in 1 to 3 taps

### 9.3 Branch Manager
- supervises staff
- handles disputes
- authorizes reversals and corrections
- monitors daily loyalty operations

### 9.4 Merchant Owner / Admin
- configures loyalty plans
- reviews reports
- manages branches and staff
- wants transparency and growth metrics

### 9.5 SaaS Platform Admin
- manages tenant onboarding
- monitors system health
- supports merchant escalations
- handles enterprise support and policy enforcement

---

## 10. User Stories

### Customer Stories
- As a customer, I want to join a loyalty program by scanning a QR code so that I do not need to download an app.
- As a customer, I want to see my progress in real time so that I trust the reward system.
- As a customer, I want to redeem my reward by showing my pass so that the process is simple and fast.

### Cashier Stories
- As a cashier, I want to scan a customer pass quickly so I can add a purchase without slowing the line.
- As a cashier, I want to see customer identity before confirming a purchase so that I avoid adding to the wrong account.
- As a cashier, I want to redeem rewards in a controlled way so that misuse is prevented.

### Manager Stories
- As a manager, I want to reverse an incorrect purchase with a reason so that mistakes can be fixed without losing auditability.
- As a manager, I want to reissue a lost pass so that the customer can continue using the program.

### Merchant Stories
- As a merchant, I want to create plans like Buy 10 Get 2 Free so that I can drive repeat purchases.
- As a merchant, I want branch-wise and staff-wise activity reports so that I can detect abuse or poor execution.

---

## 11. Scope

### In Scope
- tenant and branch management
- plan creation and configuration
- customer web enrollment
- customer digital pass with QR
- staff login and branch access
- purchase addition workflow
- reward unlock workflow
- reward redemption workflow
- pass reissue and account recovery workflow
- event ledger and audit logs
- merchant dashboard and analytics
- admin reversal and correction tools
- large-scale multi-tenant support

### Out of Scope for Initial Release
- POS integrations
- item-level inventory sync
- payment collection and settlement
- referral engine
- wallet balance or stored value
- AI recommendations
- kiosk hardware integrations

---

## 12. Detailed Functional Requirements

## 12.1 Merchant Onboarding

### Requirements
- merchant can create an organization account
- merchant can create one or more branches
- merchant can invite staff users
- merchant can assign roles by branch
- merchant can configure brand name, logo, and loyalty banner assets

### Acceptance Criteria
- merchant can create first branch during onboarding
- merchant can generate branch-specific customer enrollment QR
- each branch is isolated operationally while reporting to same tenant

---

## 12.2 Loyalty Plan Configuration

### Requirements
Merchant must be able to create and manage loyalty plans with:
- plan name
- eligible product/category label
- earning rule
- reward rule
- active/inactive status
- branch applicability
- optional validity period
- redemption constraints

### Initial Rule Support
- fixed threshold purchase plans only
- example: 10 purchases -> 2 reward credits
- example: 20 purchases -> 4 reward credits total

### Acceptance Criteria
- merchant can create, edit, deactivate, and assign plans
- deactivated plans remain visible in historical reporting
- existing memberships remain governed by plan version rules stored at enrollment time

---

## 12.3 Customer Enrollment Web App

### Requirements
Customer enrollment page must:
- open on mobile browser from QR scan
- load quickly on poor networks
- show merchant branding and plan details
- capture customer name and phone number
- optionally capture email
- allow plan selection when multiple plans are available
- create customer record and membership
- issue digital pass immediately

### Validation Rules
- phone number must be normalized before save
- duplicate membership for the same active plan and same phone should be prevented or redirected
- user must consent to terms/privacy before completion

### Acceptance Criteria
- customer can complete enrollment in under 60 seconds
- enrollment succeeds without OTP
- customer sees pass immediately after join

---

## 12.4 Customer Digital Pass

### Requirements
Digital pass must display:
- customer name
- merchant/branch identity if relevant
- active plan name
- current progress count
- reward balance available
- signed member QR
- pass status

### Pass Capabilities
- pass persists via magic link/session/local device storage strategy
- pass can be reopened from saved link
- pass can be reissued by manager/admin
- old pass versions can be revoked

### Acceptance Criteria
- pass QR loads reliably on mobile
- cashier can scan pass in under 2 seconds on normal connectivity
- revoked pass cannot be used after reissue or admin action

---

## 12.5 Staff Login and Access

### Requirements
- staff must log in to separate staff web application
- role-based access by tenant and branch is mandatory
- staff session must be secure and expire on inactivity
- branch selection may be fixed or selected based on assignment

### Roles
- cashier
- manager
- merchant admin
- platform admin

### Acceptance Criteria
- cashier can only perform allowed actions for assigned branch
- manager can access correction tools for assigned branch/tenant
- merchant admin can access reports and plan configuration

---

## 12.6 Add Purchase Workflow

### Primary Flow
1. cashier scans customer pass QR
2. system resolves membership
3. screen shows customer name, masked phone, plan, progress, reward balance
4. cashier taps **Add Purchase**
5. system validates against duplicate/add restrictions
6. system writes `PURCHASE_ADDED` ledger event
7. system updates progress summary
8. if threshold reached, system writes `REWARD_UNLOCKED`
9. UI shows success and updated progress

### Fallback Flow
- cashier searches by normalized phone number
- system shows matching memberships
- cashier confirms customer identity visually
- cashier taps **Add Purchase**

### Rules
- one purchase action equals one eligible order
- multiple quick adds for same membership require rule-based warning or manager override
- purchase add must be idempotent to avoid double tap duplication

### Acceptance Criteria
- purchase add completes in 3 taps or fewer after lookup
- accidental refresh/retry does not create duplicate purchase
- ledger event includes staff, branch, timestamp, and action source

---

## 12.7 Reward Unlock Workflow

### Requirements
- reward unlock must be fully automatic
- threshold evaluation occurs immediately after purchase add
- unlocked reward balance must be visible on staff UI and customer pass
- unlock event must be immutable in ledger

### Acceptance Criteria
- reaching threshold updates reward balance within same transaction boundary or deterministic event sequence
- customer can redeem newly unlocked reward immediately if business permits

---

## 12.8 Reward Redemption Workflow

### Requirements
Redemption must be stricter than earning.

Redemption flow:
1. customer presents pass QR
2. cashier scans pass
3. system shows reward balance and redeemable items
4. cashier taps **Redeem Reward**
5. system confirms balance availability
6. system writes `REWARD_REDEEMED`
7. UI shows remaining balance

### Rules
- spoken phone number alone is insufficient for redemption in standard flow
- reward balance cannot go below zero
- redemption must be atomic
- reversal requires elevated permissions

### Acceptance Criteria
- reward cannot be redeemed twice from same balance event due to repeated taps
- staff can only redeem against active, valid membership

---

## 12.9 Reversal and Correction Workflow

### Requirements
Managers must be able to:
- reverse incorrect purchase add
- reverse incorrect reward redemption
- correct customer phone number
- reissue customer pass
- merge duplicate accounts

### Rules
- all reversals require reason code
- all reversals create new ledger events, never destructive edits
- manager identity must be logged
- old and new state must remain auditable

### Acceptance Criteria
- no historical action is deleted from the ledger
- corrected balances recalculate accurately after reversal

---

## 12.10 Duplicate Account Handling

### Requirements
- system must detect likely duplicate customers by phone, name similarity, and branch context
- merchant manager/admin can merge accounts manually
- merged accounts must preserve full event history

### Acceptance Criteria
- one surviving membership can inherit valid ledger history as defined by merge logic
- merged obsolete pass is revoked automatically

---

## 12.11 Notifications

### Initial Scope
- optional welcome confirmation page only
- no OTP requirement
- future extensibility for SMS/email/WhatsApp notifications, but not core dependency

### System Notifications to Staff UI
- purchase add success/failure
- duplicate warning
- reward unlocked
- reward redeemed
- pending offline sync

---

## 12.12 Merchant Dashboard

### Metrics
- total members
- active members
- enrollments by branch
- purchases added per day/week/month
- rewards unlocked
- rewards redeemed
- redemption rate
- repeat purchase frequency
- staff action count
- reversal count
- suspicious activity flags

### Views
- overview dashboard
- branch performance
- plan performance
- staff activity report
- customer lookup/history
- exception report

### Acceptance Criteria
- dashboard data must be queryable by date range
- merchant can filter by branch and plan
- suspicious reversal patterns are visible

---

## 13. Security and Abuse Prevention Requirements

## 13.1 Identity and Lookup Strategy

### Rules
- no OTP required in core flow
- phone number is business lookup key, not system primary key
- every customer has internal UUID
- every membership has internal UUID
- QR token must be signed and revocable

### Rationale
This design avoids making unverified phone numbers the sole system identity while preserving low-friction operations.

---

## 13.2 QR Security

### Requirements
- QR must represent signed membership/pass token, not exposed raw database ID
- token must include versioning or revocation capability
- invalid or expired tokens must fail safely
- pass reissue must invalidate old pass version

---

## 13.3 Auditability

### Requirements
All sensitive actions must be logged with:
- tenant_id
- branch_id
- staff_user_id
- membership_id
- customer_id
- action type
- timestamp
- device/session metadata
- reason code if applicable

### Sensitive Actions
- purchase add
- redemption
- reversal
- phone correction
- pass reissue
- account merge
- staff role change

---

## 13.4 Duplicate Action Protection

### Requirements
- purchase add requests must include idempotency key
- redemption requests must include idempotency key
- repeated request due to retry must return same result instead of creating duplicate state

---

## 13.5 Fraud and Misuse Controls

### Controls
- rapid repeated purchase adds for same membership trigger warning
- high-volume adds by one staff member trigger anomaly flag
- excessive reversals by one staff member trigger anomaly flag
- lookup attempts may be rate limited
- no public API should expose whether a phone number already exists in a sensitive way

---

## 13.6 Access Control

### Requirements
- strict tenant isolation across all APIs and database queries
- role-based permissions enforced server-side
- branch scoping enforced server-side
- merchant users cannot access platform admin data
- one merchant cannot ever access another merchant's customers or reports

---

## 14. Real-World Edge Cases

### Customer enters wrong phone number at signup
- manager can correct with reason and audit log

### Customer forgets pass
- cashier uses phone lookup fallback for earning only
- for redemption, manager/cashier may use controlled recovery policy based on tenant configuration

### Customer changes phone number
- manager/admin updates phone after identity verification policy defined by merchant
- old lookup remains linked through audit history only, not active identifier

### Customer signs up twice
- duplicate merge supported

### Staff double taps add purchase
- idempotency prevents duplicate event creation

### Network failure during purchase add
- UI shows pending/sync status
- request retries must be safe

### Reward redeemed but UI times out
- final state must resolve from idempotent server response and ledger reconciliation

### Merchant deactivates plan
- existing active memberships follow stored plan version policy
- no new enrollments into inactive plan

### Branch has poor internet
- lightweight client and retry-safe APIs are required

---

## 15. Non-Functional Requirements

## 15.1 Performance
- customer enrollment page first meaningful load under 3 seconds on average mobile network
- staff lookup response under 1 second for common cases
- purchase add success response under 1 second target, 2 seconds acceptable ceiling
- dashboard overview under 3 seconds for standard date ranges

## 15.2 Reliability
- all write operations must be idempotent
- system should maintain high availability for staff workflows
- ledger writes must be durable and recoverable

## 15.3 Scalability
- support large multi-tenant merchant base
- support high read/write concurrency at peak retail hours
- support millions of customers and memberships over time
- support branch-level sharding/partitioning strategy if needed in future

## 15.4 Maintainability
- modular service boundaries
- event-based domain model
- versioned APIs
- structured logs and metrics

## 15.5 Compliance and Privacy
- store minimal personal data required for product operation
- apply consent and privacy disclosures during enrollment
- support data export/deletion policies by tenant/jurisdiction requirements

---

## 16. Data Model

## 16.1 Core Entities

### tenants
- tenant_id
- name
- status
- branding
- created_at

### branches
- branch_id
- tenant_id
- name
- address
- timezone
- status

### staff_users
- staff_user_id
- tenant_id
- primary_branch_id
- role
- name
- email/login identifier
- status

### customers
- customer_id
- tenant_id
- full_name
- normalized_phone
- email
- phone_verified_flag (false by default)
- status
- created_at

### memberships
- membership_id
- tenant_id
- branch_id or plan assignment scope
- customer_id
- plan_id
- plan_version
- status
- started_at

### plans
- plan_id
- tenant_id
- name
- earning_unit_type
- threshold_count
- reward_credit_count
- status
- version

### member_passes
- pass_id
- membership_id
- pass_version
- signed_token_reference
- status
- issued_at
- revoked_at

### ledger_events
- event_id
- tenant_id
- branch_id
- membership_id
- customer_id
- event_type
- quantity
- metadata_json
- reason_code
- idempotency_key
- created_by_staff_user_id
- created_at

### membership_summaries
- membership_id
- purchase_count
- reward_earned_count
- reward_redeemed_count
- reward_balance
- last_activity_at

### audit_logs
- audit_id
- tenant_id
- actor_id
- actor_role
- action
- target_type
- target_id
- before_json
- after_json
- created_at

---

## 17. Domain Event Types

Required event types:
- `MEMBERSHIP_CREATED`
- `PASS_ISSUED`
- `PASS_REISSUED`
- `PURCHASE_ADDED`
- `PURCHASE_REVERSED`
- `REWARD_UNLOCKED`
- `REWARD_REDEEMED`
- `REWARD_REDEMPTION_REVERSED`
- `PHONE_UPDATED`
- `ACCOUNT_MERGED`
- `PLAN_ASSIGNED`
- `PLAN_DEACTIVATED_REFERENCE`

These events form the source of truth for balance reconstruction, dispute resolution, and analytics.

---

## 18. API Requirements

## 18.1 Customer APIs
- `POST /enrollments`
- `GET /passes/{pass_token}`
- `GET /memberships/{membership_id}/summary`

## 18.2 Staff APIs
- `POST /staff/login`
- `POST /memberships/lookup-by-qr`
- `GET /memberships/search?phone=`
- `POST /memberships/{id}/purchase-add`
- `POST /memberships/{id}/redeem`
- `POST /memberships/{id}/reissue-pass`
- `POST /memberships/{id}/reverse-purchase`
- `POST /memberships/{id}/reverse-redeem`

## 18.3 Merchant Admin APIs
- `POST /plans`
- `PATCH /plans/{id}`
- `POST /branches`
- `POST /staff-users`
- `GET /reports/overview`
- `GET /reports/staff-activity`
- `GET /reports/exceptions`

## 18.4 API Rules
- all write APIs require idempotency key
- all APIs enforce tenant and role authorization
- customer pass token must be verified on lookup
- every write creates corresponding audit/ledger entry

---

## 19. UI Modules

## 19.1 Customer Web App
- landing page
- enrollment form
- plan selection screen
- success/pass screen
- pass details page
- progress view

## 19.2 Staff Web App
- login
- QR scan screen
- phone lookup screen
- membership summary screen
- add purchase action
- redeem reward action
- correction actions (role based)
- pending sync/errors screen

## 19.3 Merchant Admin Portal
- onboarding setup
- branch management
- staff management
- plan management
- customer search/history
- analytics dashboard
- anomaly/exceptions dashboard

---

## 20. Analytics and Reporting Requirements

### Enrollment Metrics
- scan to enrollment conversion
- enrollments by branch
- enrollments by plan

### Loyalty Metrics
- average purchases per active member
- time to first reward unlock
- reward redemption rate
- member retention cohorts
- active vs dormant members

### Operational Metrics
- purchases added per staff user
- redemptions per staff user
- reversal rate
- duplicate warning rate
- failed lookup rate

### Platform Metrics
- API latency
- scan success rate
- enrollment completion rate
- write idempotency conflict rate
- tenant/branch activity volume

---

## 21. Success Metrics

### Product Success
- enrollment conversion rate
- repeat purchase uplift
- reward redemption rate
- merchant retention rate
- monthly active members

### Operational Success
- low incorrect purchase dispute rate
- low reversal percentage
- sub-1 second lookup latency in standard conditions
- high pass scan success rate

### Platform Success
- high uptime
- low cross-tenant access violations
- low duplicate event creation rate

---

## 22. Rollout Plan

### Phase 1 - Foundation
- tenant, branch, staff roles
- plan creation
- customer enrollment
- pass generation
- purchase add
- reward unlock
- reward redemption
- audit ledger

### Phase 2 - Operations Hardening
- pass reissue
- reversal tools
- duplicate account merge
- anomaly dashboard
- improved reports

### Phase 3 - Scale and Integrations
- branch clusters
- export/reporting APIs
- optional integrations with POS or CRM systems
- advanced campaign tooling

---

## 23. Key Product Decisions Locked

1. **No OTP in core flow**
2. **No prepaid requirement**
3. **No WhatsApp dependency**
4. **Web app only for customer experience**
5. **Phone number is primary fallback lookup, not true system ID**
6. **Customer pass QR is primary identity at counter**
7. **Reward redemption requires pass-based flow, not only spoken phone number**
8. **All changes are ledger-based and auditable**
9. **Corrections are additive events, not destructive edits**
10. **Strict tenant isolation is mandatory**

---

## 24. Risks and Mitigations

### Risk: wrong account gets purchase
**Mitigation:** show customer name, masked phone, and plan before confirm; QR-first workflow

### Risk: duplicate customer accounts
**Mitigation:** duplicate detection and merge flow

### Risk: staff misuse
**Mitigation:** audit logs, anomaly reports, manager-only reversal rights

### Risk: repeated tap duplicates
**Mitigation:** idempotency keys and request dedupe

### Risk: lost customer pass
**Mitigation:** pass reissue and revocation workflow

### Risk: merchant complexity grows too fast
**Mitigation:** start with one earning unit only: 1 eligible order = 1 credit

### Risk: scale bottlenecks in reporting
**Mitigation:** use event ledger plus summary projections and analytics aggregation pipeline

---

## 25. Open Questions

- should one tenant be allowed to share customer identity across branches by default or optionally?
- should managers be allowed to redeem via phone lookup in recovery mode, or only via pass QR always?
- what is the exact duplicate add lock window: 2 minutes, 5 minutes, or configurable?
- should plan rules support multiple products later, or stay single-product initially?
- should customer pass be link-based only, or support wallet-style save options later?

---

## 26. Final Summary

This product is a production-grade loyalty SaaS for offline merchants built around a simple but robust operating model:

- customer joins from QR in web app
- no OTP required in core flow
- no prepaid trust barrier
- member QR is the main counter identity
- phone number is fallback lookup
- cashier adds purchases quickly
- rewards unlock automatically
- redemption is controlled and auditable
- all operations are backed by immutable ledger events
- platform is designed for multi-tenant scale from day one

This model balances:
- low friction for customers
- speed for staff
- operational control for merchants
- scalability for SaaS growth
- strong protection against common tracking and fraud problems without relying on OTP
