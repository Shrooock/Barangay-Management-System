### 5.4 Bug Reports
During the development and testing of the Barangay Management System, several bugs were identified and resolved to enhance system stability and reliability. The table below summarizes the major issues, their severity, status, and applied fixes.

#### Table 5.6 Bug Reports

| ID | Bug | Severity | Fix |
|:---:|:---|:---:|:---|
| **B01** | Dashboard does not immediately show updated data after adding or editing records. | Medium | Ensured real-time data fetching by optimizing database queries and implementing proper page redirection after database operations. |
| **B02** | Search and filter sometimes do not show the correct resident results. | Medium | Refactored the search algorithm and SQL query logic to accurately handle multiple filtering criteria and edge cases. |
| **B03** | Some user actions are not recorded in the activity logs. | High | Integrated comprehensive activity logging across all system modules to ensure every critical transaction is documented for auditing. |
| **B04** | Logo Upload Failure: "Update Barangay Info" modal fails to save logos. | High | Fixed malformed HTML form structure and refined the backend file upload script to handle image processing correctly. |
| **B05** | Database Restore Failure: Errors when restoring backups containing JSON data. | High | Updated the backup generation to escaped SQL and improved the restore parser to handle multi-line queries. |
| **B06** | Data Integrity Issue: Duplicate National IDs allowed in the system. | Medium | Added backend validation checks to prevent duplicate entries and implemented user notifications for existing records. |
| **B07** | Official Display Excess: Certificate layout breaks with more than 7 Kagawads. | Low | Enforced a system-wide limit of 7 active Kagawads and updated generation scripts to standardize document layout. |
