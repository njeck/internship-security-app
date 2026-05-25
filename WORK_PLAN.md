# Student Internship Work Plan

Project: Practical Security Application  
Student: Terry  
Mentor: Project supervisor  
Reference document: `Terry_7_Working_Day_Practical_Security_Program.pdf`

## Project Goal

Build and improve a PHP/MySQL security-focused web application for managing users, payroll uploads, payment status, and reports. The student should learn secure application development by improving the existing app step by step, documenting decisions, and demonstrating each feature at the end of the work period.

## Current Application Summary

The current app includes:

- User registration and login.
- Password hashing with `password_hash`.
- Admin and user roles.
- Payroll CSV upload by admin.
- Customer/payment status management.
- Admin report export.
- Basic database schema in `schema.sql`.

The current app is useful for learning, but it still needs security hardening before it can be trusted for real use.

## Learning Outcomes

By the end of this plan, the student should be able to:

- Explain the login, registration, role, and session flow.
- Configure a PHP/MySQL application without committing secrets.
- Use prepared statements for database actions.
- Prevent common web risks such as SQL injection, cross-site scripting, CSRF, weak session handling, and unsafe file uploads.
- Document setup steps, database structure, and security improvements.
- Present a working demo and explain remaining risks.

## 7 Working Day Plan

### Day 1: Project Setup and Understanding

Objectives:

- Clone/open the project from GitHub.
- Configure local database settings using `config.example.php`.
- Import `schema.sql`.
- Run the app locally.
- Read all PHP files and map the user journey.

Tasks:

- Create local `config.php`.
- Create test admin and user accounts.
- Draw or write a simple flow: register -> login -> admin upload -> user payment -> report.
- Identify at least five security problems in the current code.

Deliverables:

- Local app running.
- Short notes explaining each file.
- First issue list in `STUDENT_NOTES.md`.

Mentor checkpoint:

- Student explains how sessions and roles are working.

### Day 2: Database and Authentication Cleanup

Objectives:

- Improve authentication reliability and database access.
- Make the database layer more consistent.

Tasks:

- Ensure every database query checks for failure.
- Keep all database credentials out of Git.
- Add duplicate username/email handling during registration.
- Remove login debug output that prints the username.
- Make error messages user-friendly without exposing internal database details.

Deliverables:

- Improved registration and login flow.
- Updated notes describing the database tables.

Mentor checkpoint:

- Student explains why secrets must not be committed to GitHub.

### Day 3: Output Escaping and Input Validation

Objectives:

- Reduce cross-site scripting risk.
- Validate data before saving or displaying it.

Tasks:

- Escape displayed values using `htmlspecialchars`.
- Validate email format, phone format, dates, and required text fields.
- Validate payroll amount as a number.
- Fix visible spelling and HTML structure errors in pages.
- Fix the user page search JavaScript.

Deliverables:

- Safer display of user and payroll data.
- Working table search on `user.php`.

Mentor checkpoint:

- Student demonstrates how unsafe output can become XSS.

### Day 4: Secure State-Changing Actions

Objectives:

- Stop sensitive actions from happening through simple GET links.
- Add CSRF protection.

Tasks:

- Change payment and undo-payment actions from GET links to POST forms.
- Add CSRF token generation and validation.
- Add role checks before admin-only actions.
- Prevent normal users from undoing payments.
- Add confirmation for important actions.

Deliverables:

- Payment actions use POST.
- CSRF token helper added and used.

Mentor checkpoint:

- Student explains the difference between GET and POST for state-changing actions.

### Day 5: File Upload Security

Objectives:

- Make payroll upload safer.
- Prevent unsafe or invalid file uploads.

Tasks:

- Accept only CSV files.
- Check upload errors before reading the file.
- Limit file size.
- Validate each CSV row before inserting it.
- Skip or report invalid rows clearly.
- Protect against duplicate payroll/customer records where possible.

Deliverables:

- Safer payroll upload process.
- Example valid CSV file for testing.

Mentor checkpoint:

- Student explains why file uploads are high risk.

### Day 6: Reporting, Auditability, and User Experience

Objectives:

- Improve reporting accuracy and user accountability.
- Make pages easier to use during demo.

Tasks:

- Improve report filters.
- Show totals for paid and unpaid amounts.
- Add simple audit information for who performed payment actions.
- Improve navigation between admin, user, and report pages.
- Add a simple shared stylesheet if time allows.

Deliverables:

- Cleaner report page.
- Demo-ready navigation.

Mentor checkpoint:

- Student explains how payment records are connected to customers and users.

### Day 7: Final Review, Testing, and Presentation

Objectives:

- Verify the full application flow.
- Prepare a final internship demonstration.

Tasks:

- Test registration, login, payroll upload, payment, undo payment, and export.
- Test access control as both admin and user.
- Confirm no real secrets are committed.
- Update `README.md` with setup and demo instructions.
- Write final notes: completed work, known issues, and next improvements.

Deliverables:

- Final demo.
- Updated documentation.
- Clean Git commit history.

Mentor checkpoint:

- Student presents the app and explains the main security improvements.

## Security Improvement Backlog

High priority:

- Add output escaping with `htmlspecialchars`.
- Use POST and CSRF tokens for payment actions.
- Validate uploaded CSV files.
- Fix JavaScript errors on the user page.
- Remove debug/error output from production pages.

Medium priority:

- Add a shared layout and stylesheet.
- Add pagination or search for large payrolls.
- Add an admin user creation process.
- Add stronger password rules.
- Add logout/session timeout behavior.

Future improvements:

- Move to a framework such as Laravel.
- Add automated tests.
- Add migration tooling.
- Add deployment documentation.
- Add audit logs for all admin actions.

## Daily Student Routine

Each working day, the student should:

1. Pull the latest code.
2. Read the task for the day.
3. Create or update notes in `STUDENT_NOTES.md`.
4. Make small code changes.
5. Test the changed feature manually.
6. Commit with a clear message.
7. Explain the work to the mentor.

## Suggested Git Commit Messages

- `Add local configuration example`
- `Improve registration validation`
- `Escape user-facing output`
- `Use POST for payment actions`
- `Add CSRF token protection`
- `Validate payroll CSV upload`
- `Update setup documentation`

## Definition of Done

A task is done when:

- The feature works locally.
- The student can explain what changed.
- No real password or secret is committed.
- The code is committed to Git.
- The mentor has reviewed the result.

