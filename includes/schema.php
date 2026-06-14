<?php
// Central schema metadata used by the generic CRUD screens.

function appTables(): array
{
    return [
        'departments' => [
            'title' => 'Departments',
            'icon' => '🏛️',
            'description' => 'Khoa, bộ môn hoặc phòng ban quản lý người dùng và phòng lab.',
            'list' => ['id', 'department_code', 'department_name', 'created_at'],
            'form' => ['department_code', 'department_name', 'description'],
            'required' => ['department_code', 'department_name'],
            'display' => ['department_code', 'department_name'],
        ],
        'users' => [
            'title' => 'Users',
            'icon' => '👤',
            'description' => 'Tài khoản hệ thống: admin, student, technician.',
            'list' => ['id', 'full_name', 'email', 'role', 'department_id', 'phone', 'status'],
            'form' => ['department_id', 'full_name', 'email', 'password', 'role', 'phone', 'status'],
            'required' => ['full_name', 'email', 'role', 'status'],
            'display' => ['full_name', 'email'],
        ],
        'labs' => [
            'title' => 'Labs',
            'icon' => '🧪',
            'description' => 'Phòng lab thực hành và thông tin quản lý.',
            'list' => ['id', 'lab_code', 'lab_name', 'department_id', 'location', 'capacity', 'manager_user_id', 'status'],
            'form' => ['department_id', 'lab_code', 'lab_name', 'location', 'capacity', 'manager_user_id', 'status'],
            'required' => ['lab_code', 'lab_name', 'capacity', 'status'],
            'display' => ['lab_code', 'lab_name'],
        ],
        'equipment_categories' => [
            'title' => 'Equipment Categories',
            'icon' => '📦',
            'description' => 'Danh mục loại thiết bị và chu kỳ bảo trì/hiệu chuẩn mặc định.',
            'list' => ['id', 'category_name', 'maintenance_cycle_days', 'calibration_cycle_days'],
            'form' => ['category_name', 'description', 'maintenance_cycle_days', 'calibration_cycle_days'],
            'required' => ['category_name'],
            'display' => ['category_name'],
        ],
        'suppliers' => [
            'title' => 'Suppliers',
            'icon' => '🚚',
            'description' => 'Nhà cung cấp thiết bị phòng lab.',
            'list' => ['id', 'supplier_name', 'contact_person', 'phone', 'email'],
            'form' => ['supplier_name', 'contact_person', 'phone', 'email', 'address'],
            'required' => ['supplier_name'],
            'display' => ['supplier_name'],
        ],
        'equipment' => [
            'title' => 'Equipment',
            'icon' => '🔧',
            'description' => 'Danh sách thiết bị/tài sản cụ thể trong các phòng lab.',
            'list' => ['id', 'asset_code', 'equipment_name', 'category_id', 'lab_id', 'status', 'purchase_price', 'total_used_hours'],
            'form' => ['asset_code', 'equipment_name', 'category_id', 'lab_id', 'supplier_id', 'serial_number', 'purchase_date', 'purchase_price', 'status', 'total_used_hours'],
            'required' => ['asset_code', 'equipment_name', 'category_id', 'lab_id', 'status'],
            'display' => ['asset_code', 'equipment_name'],
        ],
        'borrow_requests' => [
            'title' => 'Borrow Requests',
            'icon' => '📝',
            'description' => 'Yêu cầu mượn thiết bị của sinh viên/người dùng.',
            'list' => ['id', 'user_id', 'equipment_id', 'purpose', 'start_time', 'expected_return_time', 'status', 'approved_by'],
            'form' => ['user_id', 'equipment_id', 'purpose', 'start_time', 'expected_return_time', 'status', 'approved_by', 'approved_at', 'note'],
            'required' => ['user_id', 'equipment_id', 'purpose', 'start_time', 'expected_return_time', 'status'],
            'display' => ['id', 'status'],
        ],
        'borrow_records' => [
            'title' => 'Borrow Records',
            'icon' => '📋',
            'description' => 'Phiếu check-out/check-in thực tế sau khi yêu cầu mượn được duyệt.',
            'list' => ['id', 'request_id', 'checkout_by', 'checkout_time', 'expected_return_time', 'checkin_by', 'checkin_time', 'status', 'used_hours'],
            'form' => ['request_id', 'checkout_by', 'checkout_time', 'expected_return_time', 'checkin_by', 'checkin_time', 'condition_out', 'condition_in', 'used_hours', 'status', 'note'],
            'required' => ['request_id', 'checkout_by', 'checkout_time', 'expected_return_time', 'status'],
            'display' => ['id', 'status'],
        ],
        'maintenance_schedules' => [
            'title' => 'Maintenance Schedules',
            'icon' => '🗓️',
            'description' => 'Lịch bảo trì hoặc hiệu chuẩn định kỳ cho thiết bị.',
            'list' => ['id', 'equipment_id', 'maintenance_type', 'scheduled_date', 'frequency_days', 'usage_hour_interval', 'status', 'created_by'],
            'form' => ['equipment_id', 'maintenance_type', 'scheduled_date', 'frequency_days', 'usage_hour_interval', 'status', 'created_by'],
            'required' => ['equipment_id', 'maintenance_type', 'scheduled_date', 'status', 'created_by'],
            'display' => ['id', 'maintenance_type', 'scheduled_date'],
        ],
        'maintenance_logs' => [
            'title' => 'Maintenance Logs',
            'icon' => '🛠️',
            'description' => 'Kết quả thực hiện bảo trì/hiệu chuẩn thiết bị.',
            'list' => ['id', 'equipment_id', 'technician_id', 'performed_date', 'result_status', 'cost', 'next_due_date'],
            'form' => ['schedule_id', 'equipment_id', 'technician_id', 'performed_date', 'action_taken', 'cost', 'next_due_date', 'result_status'],
            'required' => ['equipment_id', 'technician_id', 'performed_date', 'action_taken', 'result_status'],
            'display' => ['id', 'performed_date', 'result_status'],
        ],
        'damage_reports' => [
            'title' => 'Damage Reports',
            'icon' => '⚠️',
            'description' => 'Biên bản thiết bị hỏng/mất và khoản đền bù liên quan.',
            'list' => ['id', 'equipment_id', 'record_id', 'reported_by', 'severity', 'repair_cost', 'penalty_amount', 'status', 'reported_at'],
            'form' => ['record_id', 'equipment_id', 'reported_by', 'severity', 'description', 'repair_cost', 'penalty_amount', 'status', 'reported_at'],
            'required' => ['equipment_id', 'reported_by', 'severity', 'description', 'status'],
            'display' => ['id', 'severity', 'status'],
        ],
        'penalty_payments' => [
            'title' => 'Penalty Payments',
            'icon' => '💳',
            'description' => 'Thanh toán tiền đền bù phát sinh từ biên bản hỏng/mất thiết bị.',
            'list' => ['id', 'damage_report_id', 'paid_by', 'amount', 'payment_method', 'payment_status', 'paid_at'],
            'form' => ['damage_report_id', 'paid_by', 'amount', 'payment_method', 'payment_status', 'paid_at', 'note'],
            'required' => ['damage_report_id', 'paid_by', 'amount', 'payment_method', 'payment_status'],
            'display' => ['id', 'amount', 'payment_status'],
        ],
    ];
}

function fieldLabels(): array
{
    return [
        'id' => 'ID', 'department_code' => 'Department Code', 'department_name' => 'Department Name',
        'description' => 'Description', 'created_at' => 'Created At', 'department_id' => 'Department',
        'full_name' => 'Full Name', 'email' => 'Email', 'password_hash' => 'Password Hash', 'password' => 'Password',
        'role' => 'Role', 'phone' => 'Phone', 'status' => 'Status', 'lab_code' => 'Lab Code',
        'lab_name' => 'Lab Name', 'location' => 'Location', 'capacity' => 'Capacity',
        'manager_user_id' => 'Manager', 'category_name' => 'Category Name',
        'maintenance_cycle_days' => 'Maintenance Cycle Days', 'calibration_cycle_days' => 'Calibration Cycle Days',
        'supplier_name' => 'Supplier Name', 'contact_person' => 'Contact Person', 'address' => 'Address',
        'asset_code' => 'Asset Code', 'equipment_name' => 'Equipment Name', 'category_id' => 'Category',
        'lab_id' => 'Lab', 'supplier_id' => 'Supplier', 'serial_number' => 'Serial Number',
        'purchase_date' => 'Purchase Date', 'purchase_price' => 'Purchase Price', 'total_used_hours' => 'Total Used Hours',
        'user_id' => 'Requester', 'equipment_id' => 'Equipment', 'purpose' => 'Purpose',
        'start_time' => 'Start Time', 'expected_return_time' => 'Expected Return Time',
        'approved_by' => 'Approved By', 'approved_at' => 'Approved At', 'note' => 'Note',
        'request_id' => 'Borrow Request', 'checkout_by' => 'Checkout By', 'checkout_time' => 'Checkout Time',
        'checkin_by' => 'Checkin By', 'checkin_time' => 'Checkin Time', 'condition_out' => 'Condition Out',
        'condition_in' => 'Condition In', 'used_hours' => 'Used Hours', 'maintenance_type' => 'Maintenance Type',
        'scheduled_date' => 'Scheduled Date', 'frequency_days' => 'Frequency Days', 'usage_hour_interval' => 'Usage Hour Interval',
        'created_by' => 'Created By', 'schedule_id' => 'Maintenance Schedule', 'technician_id' => 'Technician',
        'performed_date' => 'Performed Date', 'action_taken' => 'Action Taken', 'cost' => 'Cost',
        'next_due_date' => 'Next Due Date', 'result_status' => 'Result Status', 'record_id' => 'Borrow Record',
        'reported_by' => 'Reported By', 'severity' => 'Severity', 'repair_cost' => 'Repair Cost',
        'penalty_amount' => 'Penalty Amount', 'reported_at' => 'Reported At',
        'damage_report_id' => 'Damage Report', 'paid_by' => 'Paid By', 'amount' => 'Amount',
        'payment_method' => 'Payment Method', 'payment_status' => 'Payment Status', 'paid_at' => 'Paid At',
    ];
}

function enumOptionsFor(string $table, string $field): array
{
    $options = [
        'users.role' => ['admin', 'student', 'technician'],
        'users.status' => ['active', 'inactive'],
        'labs.status' => ['active', 'inactive'],
        'equipment.status' => ['available', 'borrowed', 'maintenance', 'damaged', 'retired'],
        'borrow_requests.status' => ['pending', 'approved', 'rejected', 'cancelled', 'completed'],
        'borrow_records.status' => ['checked_out', 'returned', 'overdue', 'lost', 'damaged'],
        'maintenance_schedules.maintenance_type' => ['maintenance', 'calibration'],
        'maintenance_schedules.status' => ['planned', 'in_progress', 'completed', 'overdue', 'cancelled'],
        'maintenance_logs.result_status' => ['passed', 'needs_repair', 'failed'],
        'damage_reports.severity' => ['low', 'medium', 'high', 'critical'],
        'damage_reports.status' => ['reported', 'reviewing', 'charged', 'paid', 'closed'],
        'penalty_payments.payment_method' => ['cash', 'bank_transfer', 'other'],
        'penalty_payments.payment_status' => ['unpaid', 'partial', 'paid'],
    ];
    return $options[$table . '.' . $field] ?? [];
}

function foreignKeys(): array
{
    return [
        'department_id' => ['table' => 'departments', 'display' => ['department_code', 'department_name']],
        'manager_user_id' => ['table' => 'users', 'display' => ['full_name', 'email']],
        'category_id' => ['table' => 'equipment_categories', 'display' => ['category_name']],
        'lab_id' => ['table' => 'labs', 'display' => ['lab_code', 'lab_name']],
        'supplier_id' => ['table' => 'suppliers', 'display' => ['supplier_name']],
        'user_id' => ['table' => 'users', 'display' => ['full_name', 'email']],
        'equipment_id' => ['table' => 'equipment', 'display' => ['asset_code', 'equipment_name', 'status']],
        'approved_by' => ['table' => 'users', 'display' => ['full_name', 'email']],
        'request_id' => ['table' => 'borrow_requests', 'display' => ['id', 'status', 'purpose']],
        'checkout_by' => ['table' => 'users', 'display' => ['full_name', 'email']],
        'checkin_by' => ['table' => 'users', 'display' => ['full_name', 'email']],
        'created_by' => ['table' => 'users', 'display' => ['full_name', 'email']],
        'schedule_id' => ['table' => 'maintenance_schedules', 'display' => ['id', 'maintenance_type', 'scheduled_date']],
        'technician_id' => ['table' => 'users', 'display' => ['full_name', 'email']],
        'record_id' => ['table' => 'borrow_records', 'display' => ['id', 'status', 'checkout_time']],
        'reported_by' => ['table' => 'users', 'display' => ['full_name', 'email']],
        'damage_report_id' => ['table' => 'damage_reports', 'display' => ['id', 'severity', 'status']],
        'paid_by' => ['table' => 'users', 'display' => ['full_name', 'email']],
    ];
}

function allowedTables(): array
{
    return array_keys(appTables());
}
