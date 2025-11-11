# Pig Farm Management System - Routes & API Documentation

## 1. Routes Structure

```
routes/
├── web.php          # Web UI Routes (Blade templates)
├── api.php          # API Routes (JSON responses)
└── console.php      # Console/Artisan commands
```

---

## 2. Web Routes (routes/web.php)

### 2.1 Authentication Routes (Jetstream)
```
POST   /login                          Login page
POST   /register                       Register page
POST   /logout                         Logout
POST   /forgot-password                Forgot password
POST   /reset-password                 Reset password
```

### 2.2 Dashboard
```
GET    /                               Dashboard (Profit/KPI view)
GET    /home                           Legacy home page
```

---

### 2.3 Farm Management

#### Farms
```
GET    /add_farm                       Add farm form
POST   /upload_farm                    Create farm
GET    /view_farm                      List farms
```

#### Barns
```
GET    /add_barn                       Add barn form
POST   /upload_barn                    Create barn
GET    /view_barn                      List barns
```

#### Pens
```
GET    /add_pen                        Add pen form
POST   /upload_pen                     Create pen
GET    /view_pen                       List pens
```

---

### 2.4 Batch Management

#### Batch CRUD
```
GET    /batch/create_entry             Create batch form
POST   /batch/store_entry              Create batch + entry
GET    /batch/                         List batches
GET    /batch/{batch}                  View batch
GET    /batch/{batch}/edit_entry       Edit batch
PUT    /batch/{id}                     Update batch
DELETE /batch/{id}                     Delete batch
PATCH  /batch/{id}/restore             Restore deleted batch
GET    /batch/archived                 View deleted batches

POST   /batch/{id}/update-status       Update status
POST   /batch/{id}/payment             Update payment info
GET    /batch/export/csv               Export CSV
```

#### Batch Pen Allocation
```
GET    /batch_pen_allocations/         List allocations
GET    /batch_pen_allocations/export/csv  Export CSV
```

---

### 2.5 Pig Entry Management

```
GET    /pig_entry_record               Show pig entry records
POST   /upload_pig_entry_record        Create entry record

GET    /pigentryrecord/                List entries (table)
POST   /pigentryrecord/create          Create entry (modal)
GET    /pigentryrecord/{id}/edit       Edit entry
PUT    /pigentryrecord/{id}            Update entry
POST   /pigentryrecord/{id}/payment    Record payment
DELETE /pigentryrecord/{id}            Delete entry
GET    /pigentryrecord/export/csv      Export CSV
GET    /pigentryrecord/export/pdf      Export PDF

GET    /get-batches/{farmId}           Get batches for farm (AJAX)
GET    /get-barns/{farmId}             Get barns for farm (AJAX)
GET    /get-available-barns/{farmId}   Get available barns (AJAX)
GET    /get-barn-capacity/{farmId}     Get barn capacity (AJAX)
```

---

### 2.6 Daily Operations

#### Dairy Records
```
GET    /viewDairy                      Show dairy view page
POST   /uploadDairy                    Create dairy record

GET    /dairy_records/                 List dairy records
POST   /dairy_records/create           Create (modal)
GET    /dairy_records/{id}/edit        Edit record
PUT    /dairy_records/{id}             Update record
DELETE /dairy_records/{id}             Delete record
GET    /dairy_records/export/csv       Export CSV
GET    /dairy_records/export/pdf       Export PDF

PUT    /{dairyId}/{useId}/update-feed/{type}        Update feed
PUT    /{dairyId}/{btId}/update-medicine/{type}     Update medicine
PUT    /pig-death/{id}                 Update pig death
```

#### Treatments
```
GET    /treatments/                    List treatments
GET    /treatments/{id}                View treatment
PUT    /treatments/{id}                Update treatment
DELETE /treatments/{id}                Delete treatment
GET    /treatments/export/csv          Export CSV

POST   /batch-treatments/dairy-records/{dairy_record}    Create treatment
PATCH  /batch-treatments/{batch_treatment}              Update treatment
PATCH  /batch-treatments/{batch_treatment}/status       Update status
POST   /batch-treatments/{batch_treatment}/start        Start treatment
POST   /batch-treatments/{batch_treatment}/complete     Complete
POST   /batch-treatments/{batch_treatment}/stop         Stop
DELETE /batch-treatments/{batch_treatment}             Delete
GET    /batch-treatments/summary/{batch}               Get summary

POST   /daily-treatment-logs/          Create log
PATCH  /daily-treatment-logs/{log}     Update log
DELETE /daily-treatment-logs/{log}     Delete log
GET    /batch-treatments/{id}/daily-logs               Get logs
```

---

### 2.7 Pig Sales

```
GET    /pig_sales/                     List sales
GET    /pig_sales/create               Create form
POST   /pig_sales/                     Create sale
GET    /pig_sales/{id}                 View sale
PUT    /pig_sales/{id}                 Update sale
DELETE /pig_sales/{id}                 Cancel sale
PATCH  /pig_sales/{id}/confirm-cancel  Confirm cancellation
POST   /pig_sales/{id}/upload_receipt  Upload receipt

GET    /pig_sales/export/csv           Export CSV
GET    /pig_sales/export/pdf           Export PDF
POST   /pig_sales/get-status-batch     Get batch status (auto-refresh)

GET    /pig_sales/batches-by-farm/{farmId}           Get batches (AJAX)
GET    /pig_sales/pens-by-farm/{farmId}              Get pens (AJAX)
GET    /pig_sales/pens-by-batch/{batchId}            Get pens by batch (AJAX)
GET    /pig_sales/barns-by-farm/{farmId}             Get barns (AJAX)
GET    /pig_sales/barns-by-farm-for-allocation/{farmId}  Get barns (AJAX)
GET    /pig_sales/pens-by-barn/{barnId}              Get pens by barn (AJAX)
```

---

### 2.8 Payments

#### Payment Recording (User)
```
POST   /payments                       Record payment
PATCH  /payments/{id}/approve          Approve payment
PATCH  /payments/{id}/reject           Reject payment
```

#### Payment Approval (Admin)
```
GET    /payment_approvals/             List pending approvals
GET    /payment_approvals/{notificationId}/detail    View detail

PATCH  /payment_approvals/{paymentId}/approve-payment      Approve sale payment
PATCH  /payment_approvals/{paymentId}/reject-payment       Reject sale payment

PATCH  /payment_approvals/{pigSaleId}/approve-pig-sale     Approve pig sale
PATCH  /payment_approvals/{pigSaleId}/reject-pig-sale      Reject pig sale

PATCH  /payment_approvals/{pigSaleId}/approve-cancel-sale  Approve cancellation
PATCH  /payment_approvals/{pigSaleId}/reject-cancel-sale   Reject cancellation

GET    /payment_approvals/export/csv   Export CSV
```

#### Cost Payment Approval (Admin)
```
GET    /cost_payment_approvals/        List pending
GET    /cost_payment_approvals/{id}    View detail
POST   /cost_payment_approvals/{id}/approve      Approve
POST   /cost_payment_approvals/{id}/reject       Reject
GET    /cost_payment_approvals/export/csv        Export CSV
```

---

### 2.9 Storehouse & Inventory

#### Storehouse Management
```
GET    /viewStoreHouseRecord           Show storehouse view
POST   /uploadStoreHouseRecord         Create item

GET    /storehouse_records/            List items
POST   /storehouse_records/create      Create (modal)
GET    /storehouse_records/{id}/edit   Edit item
PUT    /storehouse_records/{id}        Update item
DELETE /storehouse_records/{id}        Delete item
GET    /storehouse_records/export/csv  Export CSV
GET    /storehouse_records/export/pdf  Export PDF
```

#### Inventory Movements
```
GET    /inventory_movements/           List movements
GET    /inventory_movements/export/csv Export CSV
GET    /inventory_movements/export/pdf Export PDF
```

---

### 2.10 Notifications

```
GET    /notifications/                 List all notifications
GET    /notifications/recent           Get recent (10 items)
GET    /notifications/unread_count     Get unread count

POST   /notifications/{id}/mark_as_read                              Mark as read
POST   /notifications/{id}/mark_as_read_only                         Mark as read only
POST   /notifications/{id}/mark_as_read_and_navigate_to_notifications Navigate to list
POST   /notifications/mark_all_read                                   Mark all as read
POST   /notifications/clear_read                                      Clear read notifications
GET    /notifications/{id}/mark_and_navigate                         Mark & navigate
DELETE /notifications/{id}                                            Delete notification
```

---

### 2.11 User Management (Admin)

```
GET    /user_management/               List users
GET    /user_management/pending        List pending registrations

POST   /user_management/{id}/approve   Approve user
POST   /user_management/{id}/reject    Reject user
POST   /user_management/{id}/assign_role      Assign role
POST   /user_management/{id}/update_roles     Update roles
DELETE /user_management/{id}            Delete user

POST   /user_management/{id}/request_cancel             Request cancel
PATCH  /user_management/{id}/approve_cancel             Approve cancel
PATCH  /user_management/{id}/reject_cancel              Reject cancel

GET    /user_management/api/user_type_options           Get options (AJAX)
GET    /user_management/api/user_roles/{id}             Get roles (AJAX)
```

---

### 2.12 Session & Misc

```
GET    /check-session                  Check if authenticated
GET    /registration_pending           Pending registration page
```

---

## 3. API Routes (routes/api.php)

### 3.1 Authentication
```
GET    /api/user                       Get current user (requires sanctum)
POST   /api/login                      API login
POST   /api/logout                     API logout
```

### 3.2 Farms
```
GET    /api/farms                      List all farms
GET    /api/farms/{farm_id}/batches   Get batches for farm
GET    /api/farms/{farm_id}/barns     Get barns for farm
```

### 3.3 Batches
```
GET    /api/farms/{farm_id}/batches   Get batches
GET    /api/batches/{batch_id}        Get batch details
```

### 3.4 Medicines/Items
```
GET    /api/medicines?farm_id=X       Get medicines for farm
GET    /api/feeds?farm_id=X           Get feeds for farm
GET    /api/storehouse?farm_id=X      Get inventory items
```

### 3.5 Barn-Pen Selection
```
GET    /api/barn-pen/selection?farm_id=X&batch_id=Y
```
Returns: Barn and pen structure for selection

**Request Parameters:**
- farm_id: Farm ID
- batch_id: Batch ID

**Response Format:**
```json
{
  "success": true,
  "data": [
    {
      "barn_id": 1,
      "barn_code": "BARN-001",
      "barn_name": "Barn A",
      "pens": [
        {
          "pen_id": 1,
          "pen_code": "PEN-001",
          "pen_name": "Pen 1",
          "allocated": 100,
          "capacity": 150
        }
      ]
    }
  ]
}
```

### 3.6 Treatments
```
GET    /api/treatments/{id}           Get treatment details
POST   /api/treatments                Create treatment
PUT    /api/treatments/{id}           Update treatment
DELETE /api/treatments/{id}           Delete treatment
```

### 3.7 Dairy Records
```
GET    /api/dairy-records/{batch_id}  Get records for batch
POST   /api/dairy-records             Create record
PUT    /api/dairy-records/{id}        Update record
```

### 3.8 Costs
```
GET    /api/costs/{batch_id}          Get costs for batch
POST   /api/costs                     Create cost
PUT    /api/costs/{id}                Update cost
```

### 3.9 Pig Sales
```
GET    /api/pig-sales/{batch_id}      Get sales for batch
POST   /api/pig-sales                 Create sale
PUT    /api/pig-sales/{id}            Update sale
DELETE /api/pig-sales/{id}            Cancel sale
```

### 3.10 Inventory
```
GET    /api/inventory/{farm_id}       Get inventory
POST   /api/inventory-movement        Record movement
GET    /api/inventory-movement/{batch_id}  Get movements
```

### 3.11 Profit/KPI
```
GET    /api/profit/{batch_id}         Get profit for batch
GET    /api/kpi/{batch_id}            Get KPI metrics
GET    /api/projected-profit/{batch_id}  Get projected profit
```

### 3.12 Reports
```
GET    /api/reports/batch/{batch_id}  Batch report
GET    /api/reports/farm/{farm_id}    Farm report
GET    /api/reports/financial         Financial summary
```

---

## 4. Response Format Standards

### 4.1 Success Response
```json
{
  "success": true,
  "data": { /* actual data */ },
  "message": "Operation successful"
}
```

### 4.2 Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### 4.3 Pagination Response
```json
{
  "success": true,
  "data": [ /* items */ ],
  "pagination": {
    "total": 100,
    "per_page": 10,
    "current_page": 1,
    "last_page": 10
  }
}
```

---

## 5. HTTP Status Codes

| Code | Meaning | Usage |
|------|---------|-------|
| 200 | OK | Successful GET/POST/PUT |
| 201 | Created | Successful POST (create) |
| 204 | No Content | Successful DELETE |
| 400 | Bad Request | Invalid input |
| 401 | Unauthorized | Not authenticated |
| 403 | Forbidden | No permission |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable | Validation error |
| 500 | Server Error | Server error |

---

## 6. Authentication & Authorization

### 6.1 Web Routes
- Protected by `auth` middleware
- Additional: `permission:manage_users` for admin routes
- Additional: `prevent.cache` for cache control

### 6.2 API Routes
- Protected by `auth:sanctum` middleware
- Requires API token in header: `Authorization: Bearer {token}`

### 6.3 Roles & Permissions

**Roles:**
- Admin: Full access to all features
- Manager: Can manage batch operations and approve payments
- Staff: Can create records but limited approval
- Viewer: Read-only access

**Permissions:**
- manage_users
- manage_costs
- approve_payments
- manage_inventory
- view_reports
- manage_batch

---

## 7. Common AJAX Routes

**Usage:** Called from JavaScript for dynamic updates

```
GET    /pig_sales/batches-by-farm/{farmId}
GET    /pig_sales/pens-by-farm/{farmId}
GET    /pig_sales/pens-by-batch/{batchId}
GET    /pig_sales/barns-by-farm/{farmId}
GET    /pig_sales/pens-by-barn/{barnId}
GET    /pig_sales/get-status-batch (POST)

GET    /pigentryrecord/get-batches/{farmId}
GET    /pigentryrecord/get-barns/{farmId}
GET    /pigentryrecord/get-available-barns/{farmId}
GET    /pigentryrecord/get-barn-capacity/{farmId}

GET    /notifications/unread_count
GET    /api/barn-pen/selection?farm_id=X&batch_id=Y
```

---

## 8. Request/Response Examples

### Example 1: Create Pig Sale

**Request:**
```
POST /pig_sales/
Content-Type: multipart/form-data

{
  "batch_id": 1,
  "farm_id": 1,
  "pen_id": 5,
  "customer_id": 2,
  "date": "2025-11-08",
  "quantity": 100,
  "total_weight": 11500,
  "price_per_kg": 58,
  "shipping_cost": 5000,
  "receipt_file": (file)
}
```

**Response:**
```json
{
  "success": true,
  "message": "Pig sale created successfully",
  "data": {
    "id": 150,
    "sale_number": "SALE-20251108001",
    "net_total": 661000,
    "status": "pending"
  }
}
```

### Example 2: Get Barn-Pen Selection

**Request:**
```
GET /api/barn-pen/selection?farm_id=1&batch_id=5
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "barn_id": 1,
      "barn_code": "BARN-A",
      "pens": [
        {
          "pen_id": 1,
          "pen_code": "PEN-01",
          "allocated": 150,
          "capacity": 200
        }
      ]
    }
  ]
}
```

### Example 3: Record Payment

**Request:**
```
POST /payments
Content-Type: multipart/form-data

{
  "pig_sale_id": 150,
  "amount": 661000,
  "payment_method": "โอนเงิน",
  "payment_date": "2025-11-08",
  "reference_number": "TRF123456",
  "bank_name": "กรุงไทย",
  "receipt_file": (file)
}
```

**Response:**
```json
{
  "success": true,
  "message": "Payment recorded successfully",
  "data": {
    "id": 100,
    "status": "pending"
  }
}
```

---

## 9. Error Handling Examples

### Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "quantity": ["Quantity must be greater than 0"],
    "price_per_kg": ["Price is required"]
  }
}
```

### Authorization Error
```json
{
  "success": false,
  "message": "Unauthorized - You don't have permission to approve payments"
}
```

### Not Found Error
```json
{
  "success": false,
  "message": "Pig sale not found"
}
```

---

## 10. Rate Limiting

- No rate limiting currently implemented
- Can be added via middleware if needed
- Recommended: 60 requests per minute for API

---

**Last Updated:** November 8, 2025
**Version:** 1.0
