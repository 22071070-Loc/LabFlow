<?php
class Schema
{
    public static function all(): array
    {
        return [
            'departments' => [
                'title' => 'Departments',
                'table' => 'departments',
                'roles' => ['index'=>['admin','technician'], 'view'=>['admin','technician'], 'create'=>['admin'], 'store'=>['admin'], 'edit'=>['admin'], 'update'=>['admin'], 'delete'=>['admin']],
                'fields' => [
                    'department_code' => ['label'=>'Department Code', 'type'=>'text', 'required'=>true],
                    'department_name' => ['label'=>'Department Name', 'type'=>'text', 'required'=>true],
                    'description' => ['label'=>'Description', 'type'=>'textarea'],
                ],
                'list' => ['id','department_code','department_name','description','created_at'],
            ],
            'users' => [
                'title' => 'Users',
                'table' => 'users',
                'roles' => ['index'=>['admin'], 'view'=>['admin'], 'create'=>['admin'], 'store'=>['admin'], 'edit'=>['admin'], 'update'=>['admin'], 'delete'=>['admin']],
                'fields' => [
                    'department_id' => ['label'=>'Department', 'type'=>'relation', 'relation'=>['table'=>'departments','label'=>'department_name']],
                    'full_name' => ['label'=>'Full Name', 'type'=>'text', 'required'=>true],
                    'email' => ['label'=>'Email', 'type'=>'email', 'required'=>true],
                    'student_code' => ['label'=>'Student ID', 'type'=>'text'],
                    'password' => ['label'=>'Password', 'type'=>'password', 'virtual'=>true],
                    'role' => ['label'=>'Role', 'type'=>'select', 'options'=>['admin'=>'Admin','student'=>'Student','technician'=>'Technician'], 'required'=>true],
                    'phone' => ['label'=>'Phone', 'type'=>'text'],
                    'status' => ['label'=>'Status', 'type'=>'select', 'options'=>['active'=>'Active','inactive'=>'Inactive'], 'required'=>true],
                ],
                'list' => ['id','full_name','email','student_code','role','department_id','phone','status','created_at'],
            ],
            'labs' => [
                'title' => 'Labs',
                'table' => 'labs',
                'roles' => ['index'=>['admin','technician'], 'view'=>['admin','technician'], 'create'=>['admin'], 'store'=>['admin'], 'edit'=>['admin'], 'update'=>['admin'], 'delete'=>['admin']],
                'fields' => [
                    'department_id' => ['label'=>'Department', 'type'=>'relation', 'relation'=>['table'=>'departments','label'=>'department_name']],
                    'lab_code' => ['label'=>'Lab Code', 'type'=>'text', 'required'=>true],
                    'lab_name' => ['label'=>'Lab Name', 'type'=>'text', 'required'=>true],
                    'location' => ['label'=>'Location', 'type'=>'text'],
                    'capacity' => ['label'=>'Capacity', 'type'=>'number', 'required'=>true, 'step'=>'1'],
                    'manager_user_id' => ['label'=>'Lab Manager', 'type'=>'relation', 'relation'=>['table'=>'users','label'=>'full_name']],
                    'status' => ['label'=>'Status', 'type'=>'select', 'options'=>['active'=>'Active','inactive'=>'Inactive'], 'required'=>true],
                ],
                'list' => ['id','lab_code','lab_name','department_id','location','capacity','manager_user_id','status'],
            ],
            'equipment_categories' => [
                'title' => 'Equipment Categories',
                'table' => 'equipment_categories',
                'roles' => ['index'=>['admin','technician','student'], 'view'=>['admin','technician','student'], 'create'=>['admin'], 'store'=>['admin'], 'edit'=>['admin'], 'update'=>['admin'], 'delete'=>['admin']],
                'fields' => [
                    'category_name' => ['label'=>'Category Name', 'type'=>'text', 'required'=>true],
                    'description' => ['label'=>'Description', 'type'=>'textarea'],
                    'maintenance_cycle_days' => ['label'=>'Maintenance Cycle Days', 'type'=>'number', 'step'=>'1'],
                    'calibration_cycle_days' => ['label'=>'Calibration Cycle Days', 'type'=>'number', 'step'=>'1'],
                ],
                'list' => ['id','category_name','maintenance_cycle_days','calibration_cycle_days','description'],
            ],
            'suppliers' => [
                'title' => 'Suppliers',
                'table' => 'suppliers',
                'roles' => ['index'=>['admin','technician'], 'view'=>['admin','technician'], 'create'=>['admin'], 'store'=>['admin'], 'edit'=>['admin'], 'update'=>['admin'], 'delete'=>['admin']],
                'fields' => [
                    'supplier_name' => ['label'=>'Supplier Name', 'type'=>'text', 'required'=>true],
                    'contact_person' => ['label'=>'Contact Person', 'type'=>'text'],
                    'phone' => ['label'=>'Phone', 'type'=>'text'],
                    'email' => ['label'=>'Email', 'type'=>'email'],
                    'address' => ['label'=>'Address', 'type'=>'text'],
                ],
                'list' => ['id','supplier_name','contact_person','phone','email','address'],
            ],
            'equipment' => [
                'title' => 'Equipment',
                'table' => 'equipment',
                'roles' => ['index'=>['admin','technician','student'], 'view'=>['admin','technician','student'], 'create'=>['admin'], 'store'=>['admin'], 'edit'=>['admin','technician'], 'update'=>['admin','technician'], 'delete'=>['admin']],
                'fields' => [
                    'asset_code' => ['label'=>'Asset Code', 'type'=>'text', 'required'=>true],
                    'equipment_name' => ['label'=>'Equipment Name', 'type'=>'text', 'required'=>true],
                    'category_id' => ['label'=>'Category', 'type'=>'relation', 'relation'=>['table'=>'equipment_categories','label'=>'category_name'], 'required'=>true],
                    'lab_id' => ['label'=>'Lab', 'type'=>'relation', 'relation'=>['table'=>'labs','label'=>'lab_name'], 'required'=>true],
                    'supplier_id' => ['label'=>'Supplier', 'type'=>'relation', 'relation'=>['table'=>'suppliers','label'=>'supplier_name']],
                    'serial_number' => ['label'=>'Serial Number', 'type'=>'text'],
                    'purchase_date' => ['label'=>'Purchase Date', 'type'=>'date'],
                    'purchase_price' => ['label'=>'Purchase Price', 'type'=>'number', 'step'=>'0.01'],
                    'status' => ['label'=>'Status', 'type'=>'select', 'options'=>['available'=>'Available','borrowed'=>'Borrowed','maintenance'=>'Maintenance','damaged'=>'Damaged','retired'=>'Retired'], 'required'=>true],
                    'total_used_hours' => ['label'=>'Total Used Hours', 'type'=>'number', 'step'=>'0.01'],
                ],
                'list' => ['id','asset_code','equipment_name','category_id','lab_id','status','total_used_hours','purchase_price'],
            ],
            'borrow_requests' => [
                'title' => 'Borrow Requests',
                'table' => 'borrow_requests',
                'roles' => ['index'=>['admin','technician','student'], 'view'=>['admin','technician','student'], 'create'=>['admin','technician','student'], 'store'=>['admin','technician','student'], 'edit'=>['admin','student'], 'update'=>['admin','student'], 'delete'=>['admin','student'], 'approve'=>['admin','technician'], 'reject'=>['admin','technician']],
                'fields' => [
                    'user_id' => ['label'=>'Borrower', 'type'=>'relation', 'relation'=>['table'=>'users','label'=>'full_name'], 'required'=>true, 'student_self'=>true],
                    'equipment_id' => ['label'=>'Equipment', 'type'=>'relation', 'relation'=>['table'=>'equipment','label'=>'equipment_name','extra'=>'asset_code'], 'required'=>true],
                    'purpose' => ['label'=>'Purpose', 'type'=>'textarea', 'required'=>true],
                    'start_time' => ['label'=>'Start Time', 'type'=>'datetime-local', 'required'=>true],
                    'expected_return_time' => ['label'=>'Expected Return Time', 'type'=>'datetime-local', 'required'=>true],
                    'status' => ['label'=>'Status', 'type'=>'select', 'options'=>['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','cancelled'=>'Cancelled','completed'=>'Completed'], 'required'=>true, 'admin_only'=>true],
                    'approved_by' => ['label'=>'Approved By', 'type'=>'relation', 'relation'=>['table'=>'users','label'=>'full_name'], 'admin_only'=>true],
                    'approved_at' => ['label'=>'Approved At', 'type'=>'datetime-local', 'admin_only'=>true],
                    'note' => ['label'=>'Note', 'type'=>'textarea'],
                ],
                'list' => ['id','user_id','equipment_id','start_time','expected_return_time','status','approved_by','created_at'],
            ],
            'borrow_records' => [
                'title' => 'Borrow Records',
                'table' => 'borrow_records',
                'roles' => ['index'=>['admin','technician','student'], 'view'=>['admin','technician','student'], 'create'=>['admin','technician'], 'store'=>['admin','technician'], 'edit'=>['admin','technician'], 'update'=>['admin','technician'], 'delete'=>['admin'], 'checkout'=>['admin','technician'], 'returnGood'=>['admin','technician'], 'returnDamaged'=>['admin','technician'], 'returnLost'=>['admin','technician']],
                'fields' => [
                    'request_id' => ['label'=>'Borrow Request', 'type'=>'relation', 'relation'=>['table'=>'borrow_requests','label'=>'id'], 'required'=>true],
                    'checkout_by' => ['label'=>'Checkout By', 'type'=>'relation', 'relation'=>['table'=>'users','label'=>'full_name'], 'required'=>true],
                    'checkout_time' => ['label'=>'Checkout Time', 'type'=>'datetime-local', 'required'=>true],
                    'expected_return_time' => ['label'=>'Expected Return Time', 'type'=>'datetime-local', 'required'=>true],
                    'checkin_by' => ['label'=>'Check-in By', 'type'=>'relation', 'relation'=>['table'=>'users','label'=>'full_name']],
                    'checkin_time' => ['label'=>'Check-in Time', 'type'=>'datetime-local'],
                    'condition_out' => ['label'=>'Condition Out', 'type'=>'textarea'],
                    'condition_in' => ['label'=>'Condition In', 'type'=>'textarea'],
                    'used_hours' => ['label'=>'Used Hours', 'type'=>'number', 'step'=>'0.01'],
                    'status' => ['label'=>'Status', 'type'=>'select', 'options'=>['checked_out'=>'Checked Out','returned'=>'Returned','overdue'=>'Overdue','lost'=>'Lost','damaged'=>'Damaged'], 'required'=>true],
                    'note' => ['label'=>'Note', 'type'=>'textarea'],
                ],
                'list' => ['id','request_id','checkout_by','checkout_time','expected_return_time','checkin_by','checkin_time','used_hours','status'],
            ],
            'maintenance_schedules' => [
                'title' => 'Maintenance Schedules',
                'table' => 'maintenance_schedules',
                'roles' => ['index'=>['admin','technician'], 'view'=>['admin','technician'], 'create'=>['admin','technician'], 'store'=>['admin','technician'], 'edit'=>['admin','technician'], 'update'=>['admin','technician'], 'delete'=>['admin'], 'start'=>['admin','technician'], 'complete'=>['admin','technician']],
                'fields' => [
                    'equipment_id' => ['label'=>'Equipment', 'type'=>'relation', 'relation'=>['table'=>'equipment','label'=>'equipment_name','extra'=>'asset_code'], 'required'=>true],
                    'maintenance_type' => ['label'=>'Type', 'type'=>'select', 'options'=>['maintenance'=>'Maintenance','calibration'=>'Calibration'], 'required'=>true],
                    'scheduled_date' => ['label'=>'Scheduled Date', 'type'=>'date', 'required'=>true],
                    'frequency_days' => ['label'=>'Frequency Days', 'type'=>'number', 'step'=>'1'],
                    'usage_hour_interval' => ['label'=>'Usage Hour Interval', 'type'=>'number', 'step'=>'1'],
                    'status' => ['label'=>'Status', 'type'=>'select', 'options'=>['planned'=>'Planned','in_progress'=>'In Progress','completed'=>'Completed','overdue'=>'Overdue','cancelled'=>'Cancelled'], 'required'=>true],
                    'created_by' => ['label'=>'Created By', 'type'=>'relation', 'relation'=>['table'=>'users','label'=>'full_name'], 'required'=>true, 'default_current_user'=>true],
                ],
                'list' => ['id','equipment_id','maintenance_type','scheduled_date','status','created_by','created_at'],
            ],
            'maintenance_logs' => [
                'title' => 'Maintenance Logs',
                'table' => 'maintenance_logs',
                'roles' => ['index'=>['admin','technician'], 'view'=>['admin','technician'], 'create'=>['admin','technician'], 'store'=>['admin','technician'], 'edit'=>['admin','technician'], 'update'=>['admin','technician'], 'delete'=>['admin']],
                'fields' => [
                    'schedule_id' => ['label'=>'Schedule', 'type'=>'relation', 'relation'=>['table'=>'maintenance_schedules','label'=>'id']],
                    'equipment_id' => ['label'=>'Equipment', 'type'=>'relation', 'relation'=>['table'=>'equipment','label'=>'equipment_name','extra'=>'asset_code'], 'required'=>true],
                    'technician_id' => ['label'=>'Technician', 'type'=>'relation', 'relation'=>['table'=>'users','label'=>'full_name'], 'required'=>true, 'default_current_user'=>true],
                    'performed_date' => ['label'=>'Performed Date', 'type'=>'date', 'required'=>true],
                    'action_taken' => ['label'=>'Action Taken', 'type'=>'textarea', 'required'=>true],
                    'cost' => ['label'=>'Cost', 'type'=>'number', 'step'=>'0.01'],
                    'next_due_date' => ['label'=>'Next Due Date', 'type'=>'date'],
                    'result_status' => ['label'=>'Result Status', 'type'=>'select', 'options'=>['passed'=>'Passed','needs_repair'=>'Needs Repair','failed'=>'Failed'], 'required'=>true],
                ],
                'list' => ['id','schedule_id','equipment_id','technician_id','performed_date','cost','result_status'],
            ],
            'damage_reports' => [
                'title' => 'Damage Reports',
                'table' => 'damage_reports',
                'roles' => ['index'=>['admin','technician'], 'view'=>['admin','technician'], 'create'=>['admin','technician'], 'store'=>['admin','technician'], 'edit'=>['admin','technician'], 'update'=>['admin','technician'], 'delete'=>['admin'], 'markPaid'=>['admin','technician']],
                'fields' => [
                    'record_id' => ['label'=>'Borrow Record', 'type'=>'relation', 'relation'=>['table'=>'borrow_records','label'=>'id']],
                    'equipment_id' => ['label'=>'Equipment', 'type'=>'relation', 'relation'=>['table'=>'equipment','label'=>'equipment_name','extra'=>'asset_code'], 'required'=>true],
                    'reported_by' => ['label'=>'Reported By', 'type'=>'relation', 'relation'=>['table'=>'users','label'=>'full_name'], 'required'=>true, 'default_current_user'=>true],
                    'severity' => ['label'=>'Severity', 'type'=>'select', 'options'=>['low'=>'Low','medium'=>'Medium','high'=>'High','critical'=>'Critical'], 'required'=>true],
                    'description' => ['label'=>'Description', 'type'=>'textarea', 'required'=>true],
                    'repair_cost' => ['label'=>'Repair Cost', 'type'=>'number', 'step'=>'0.01'],
                    'penalty_amount' => ['label'=>'Penalty Amount', 'type'=>'number', 'step'=>'0.01'],
                    'status' => ['label'=>'Status', 'type'=>'select', 'options'=>['reported'=>'Reported','reviewing'=>'Reviewing','charged'=>'Charged','paid'=>'Paid','closed'=>'Closed'], 'required'=>true],
                    'reported_at' => ['label'=>'Reported At', 'type'=>'datetime-local'],
                ],
                'list' => ['id','record_id','equipment_id','reported_by','severity','penalty_amount','status','reported_at'],
            ],
            'penalty_payments' => [
                'title' => 'Penalty Payments',
                'table' => 'penalty_payments',
                'roles' => ['index'=>['admin','technician','student'], 'view'=>['admin','technician','student'], 'create'=>['admin','technician'], 'store'=>['admin','technician'], 'edit'=>['admin','technician'], 'update'=>['admin','technician'], 'delete'=>['admin']],
                'fields' => [
                    'damage_report_id' => ['label'=>'Damage Report', 'type'=>'relation', 'relation'=>['table'=>'damage_reports','label'=>'id'], 'required'=>true],
                    'paid_by' => ['label'=>'Paid By', 'type'=>'relation', 'relation'=>['table'=>'users','label'=>'full_name'], 'required'=>true],
                    'amount' => ['label'=>'Amount', 'type'=>'number', 'step'=>'0.01', 'required'=>true],
                    'payment_method' => ['label'=>'Payment Method', 'type'=>'select', 'options'=>['cash'=>'Cash','bank_transfer'=>'Bank Transfer','other'=>'Other'], 'required'=>true],
                    'payment_status' => ['label'=>'Payment Status', 'type'=>'select', 'options'=>['unpaid'=>'Unpaid','partial'=>'Partial','paid'=>'Paid'], 'required'=>true],
                    'paid_at' => ['label'=>'Paid At', 'type'=>'datetime-local'],
                    'note' => ['label'=>'Note', 'type'=>'textarea'],
                ],
                'list' => ['id','damage_report_id','paid_by','amount','payment_method','payment_status','paid_at'],
            ],
        ];
    }

    public static function get(string $key): array
    {
        $all = self::all();
        if (!isset($all[$key])) {
            http_response_code(404);
            die('Module not found.');
        }
        return $all[$key];
    }

    public static function modules(): array
    {
        return array_keys(self::all());
    }

    public static function moduleLabel(string $key): string
    {
        return self::get($key)['title'];
    }
}
