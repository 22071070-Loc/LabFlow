# LabFlow (Lab & Equipment Manager)
Web Final Project

## 1. Project Introduction

**Lab & Equipment Manager** is a web-based system designed to manage practical laboratory rooms and equipment in a university environment. The system supports the management of lab assets, equipment borrowing, check-in/check-out records, maintenance schedules, damage reports, and penalty payments.

This project is developed for the course **INS3064 - Multimedia Design and Web Development**. The main purpose of the system is to help lab administrators, technicians, and students manage equipment usage more efficiently and reduce manual tracking errors.

## 2. Project Objectives

The system aims to:

- Manage departments, laboratory rooms, equipment categories, suppliers, and equipment.
- Allow students to submit equipment borrowing requests.
- Allow administrators or technicians to approve or reject borrowing requests.
- Track equipment check-out and check-in records.
- Manage maintenance and calibration schedules.
- Record maintenance logs after each maintenance activity.
- Report damaged or lost equipment.
- Calculate and manage penalty payments when equipment is damaged or lost.
- Maintain clear relationships between all database entities.

## 3. Main Users

The system includes three main user roles:

| Role | Description |
|---|---|
| Admin | Manages users, labs, equipment, requests, and overall system data |
| Student | Creates equipment borrowing requests and tracks request status |
| Technician | Handles equipment check-out/check-in, maintenance logs, and damage reports |

## 4. Technology Stack

| Component | Technology |
|---|---|
| Backend | PHP |
| Frontend | HTML, CSS, JavaScript |
| Database | MySQL / MariaDB |
| Local Server | XAMPP |
| Database Tool | phpMyAdmin |
| Version Control | Git / GitHub |

## 5. Database Overview

The database is named:

```sql
lab_equipment_manager