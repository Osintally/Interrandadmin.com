@extends('layouts.app')

@section('title', __('Salary Management'))

@section('css')
<style>
/* Salary Management Styles */
:root {
  --salary-primary: #3b82f6;
  --salary-primary-dark: #2563eb;
  --salary-secondary: #10b981;
  --salary-warning: #f59e0b;
  --salary-danger: #ef4444;
  --salary-success: #22c55e;
  --salary-bg: #f8fafc;
  --salary-surface: #ffffff;
  --salary-surface-light: #f1f5f9;
  --salary-text: #1e293b;
  --salary-text-secondary: #475569;
  --salary-text-muted: #64748b;
  --salary-border: #e2e8f0;
  --salary-radius: 12px;
  --salary-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
  --salary-shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1), 0 4px 6px rgba(0, 0, 0, 0.05);
}

/* System Link Cards */
.system-link-card {
  display: flex;
  align-items: center;
  padding: 20px;
  background: var(--salary-surface);
  border: 1px solid var(--salary-border);
  border-radius: var(--salary-radius);
  box-shadow: var(--salary-shadow);
  transition: all 0.3s ease;
  height: 100%;
}

.system-link-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--salary-shadow-lg);
  border-color: var(--salary-primary);
}

.system-icon {
  margin-right: 16px;
  font-size: 32px;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(59, 130, 246, 0.1);
  border-radius: 50%;
  flex-shrink: 0;
}

.system-info {
  flex: 1;
}

.system-info h6 {
  margin: 0 0 8px 0;
  font-size: 16px;
  font-weight: 600;
  color: var(--salary-text);
}

.system-info p {
  margin: 0 0 12px 0;
  font-size: 14px;
  color: var(--salary-text-secondary);
  line-height: 1.4;
}

.system-info .btn {
  font-size: 12px;
  padding: 6px 12px;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.system-info .btn:hover {
  transform: translateY(-1px);
}

.salary-dashboard {
  background: var(--salary-bg);
  min-height: 100vh;
  padding: 0;
}

.page-header {
  background: white;
  padding: 20px 24px;
  border-bottom: 1px solid var(--salary-border);
  margin-bottom: 24px;
  box-shadow: var(--salary-shadow);
}

.page-header h1 {
  font-size: 28px;
  font-weight: 600;
  color: var(--salary-text);
  margin: 0 0 8px 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.page-header p {
  color: var(--salary-text-secondary);
  margin: 0;
  font-size: 16px;
}

.salary-content {
  padding: 0 24px 24px 24px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  border-radius: var(--salary-radius);
  padding: 24px;
  box-shadow: var(--salary-shadow);
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
}

.stat-card.employees::before { background: var(--salary-primary); }
.stat-card.total-salary::before { background: var(--salary-secondary); }
.stat-card.average-salary::before { background: var(--salary-warning); }
.stat-card.pending::before { background: var(--salary-danger); }

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--salary-radius);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  color: white;
  margin-bottom: 16px;
}

.stat-card.employees .stat-icon { background: var(--salary-primary); }
.stat-card.total-salary .stat-icon { background: var(--salary-secondary); }
.stat-card.average-salary .stat-icon { background: var(--salary-warning); }
.stat-card.pending .stat-icon { background: var(--salary-danger); }

.stat-value {
  font-size: 32px;
  font-weight: 600;
  color: var(--salary-text);
  margin-bottom: 4px;
}

.stat-label {
  color: var(--salary-text-secondary);
  font-size: 14px;
  font-weight: 400;
}

.salary-card {
  background: white;
  border-radius: var(--salary-radius);
  padding: 24px;
  box-shadow: var(--salary-shadow);
  margin-bottom: 24px;
}

.salary-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--salary-border);
}

.salary-card-title {
  font-size: 18px;
  font-weight: 600;
  color: var(--salary-text);
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  border: none;
  border-radius: var(--salary-radius);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
  font-size: 14px;
}

.btn-primary {
  background: var(--salary-primary);
  color: white;
  box-shadow: var(--salary-shadow);
}

.btn-primary:hover {
  background: var(--salary-primary-dark);
  transform: translateY(-1px);
  box-shadow: var(--salary-shadow-lg);
}

.btn-success {
  background: var(--salary-success);
  color: white;
}

.btn-warning {
  background: var(--salary-warning);
  color: white;
}

.btn-danger {
  background: var(--salary-danger);
  color: white;
}

.btn-sm {
  padding: 8px 16px;
  font-size: 12px;
}

.table-container {
  overflow: hidden;
  border-radius: var(--salary-radius);
  border: 1px solid var(--salary-border);
  box-shadow: var(--salary-shadow);
  background: var(--salary-surface);
}

.table {
  width: 100%;
  border-collapse: collapse;
  background: var(--salary-surface);
  margin-bottom: 0;
}

.table th,
.table td {
  padding: 12px 16px;
  text-align: left;
  border-bottom: 1px solid var(--salary-border);
  vertical-align: middle;
}

.table th {
  background: var(--salary-surface-light);
  font-weight: 600;
  color: var(--salary-text-secondary);
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 0.5px;
  position: sticky;
  top: 0;
  z-index: 10;
}

.table tbody tr {
  transition: all 0.2s ease;
}

.table tbody tr:hover {
  background: var(--salary-surface-light);
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.table tbody tr:last-child td {
  border-bottom: none;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  min-width: 40px;
  justify-content: center;
}

.status-active {
  background: rgba(34, 197, 94, 0.15);
  color: var(--salary-success);
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.status-pending {
  background: rgba(245, 158, 11, 0.15);
  color: var(--salary-warning);
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.status-inactive {
  background: rgba(239, 68, 68, 0.15);
  color: var(--salary-danger);
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.search-box {
  position: relative;
  width: 300px;
}

.search-box input {
  width: 100%;
  padding: 12px 16px 12px 40px;
  background: var(--salary-surface);
  border: 1px solid var(--salary-border);
  border-radius: var(--salary-radius);
  color: var(--salary-text);
  font-size: 14px;
  transition: all 0.2s ease;
}

.search-box input:focus {
  outline: none;
  border-color: var(--salary-primary);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-box i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--salary-text-muted);
}

.header-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

/* Responsive Design */
@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .page-header {
    padding: 16px 20px;
  }
  
  .page-header h1 {
    font-size: 24px;
  }
  
  .salary-content {
    padding: 0 16px 16px 16px;
  }
  
  .salary-card-header {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
  }
  
  .search-box {
    width: 100%;
  }
  
  .header-actions {
    flex-direction: column;
    gap: 8px;
    width: 100%;
  }
  
  .btn {
    width: 100%;
    justify-content: center;
  }
}

/* Loading states */
.loading {
  opacity: 0.6;
  pointer-events: none;
}

.spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid #f3f3f3;
  border-top: 2px solid var(--salary-primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Enhanced focus states for accessibility */
.btn:focus,
.search-box input:focus {
  outline: 2px solid var(--salary-primary);
  outline-offset: 2px;
}
</style>
@endsection

@section('content')
<div class="salary-dashboard">
    <!-- Page Header -->
    <div class="page-header">
        <h1>
            <i class="fas fa-money-bill-wave text-primary"></i>
            Salary Management
        </h1>
        <p>Comprehensive salary tracking and management system</p>
    </div>

    <!-- Main Content -->
    <div class="salary-content">
        <!-- Related Systems section removed as requested -->

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card employees">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value" id="totalEmployees">{{ $stats['total_employees'] ?? 0 }}</div>
                <div class="stat-label">Total Employees</div>
            </div>
            
            <div class="stat-card total-salary">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value" id="totalSalary">{{ $stats['total_salary'] ?? '₦0' }}</div>
                <div class="stat-label">Total Monthly Salary</div>
            </div>
            
            <div class="stat-card average-salary">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-value" id="averageSalary">{{ $stats['average_salary'] ?? '₦0' }}</div>
                <div class="stat-label">Average Salary</div>
            </div>
            
            <div class="stat-card pending">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value" id="pendingSalaries">{{ $stats['pending_salaries'] ?? 0 }}</div>
                <div class="stat-label">Pending Approvals</div>
            </div>
        </div>

        <!-- Salary Management Section -->
        <div class="salary-card">
            <div class="salary-card-header">
                <h5 class="salary-card-title">
                    <i class="fas fa-list"></i>
                    Employee Salaries
                </h5>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="salarySearch" placeholder="Search employees...">
                    </div>
                    <button class="btn btn-primary" onclick="addNewSalary()">
                        <i class="fas fa-plus"></i>
                        Add Salary
                    </button>
                    <button class="btn btn-info" onclick="downloadTemplate()">
                        <i class="fas fa-download"></i>
                        Download Template
                    </button>
                    <button class="btn btn-success" onclick="importSalaries()">
                        <i class="fas fa-file-import"></i>
                        Import
                    </button>
                    <button class="btn btn-warning" onclick="exportSalaries()">
                        <i class="fas fa-file-export"></i>
                        Export
                    </button>
                </div>
            </div>
            
            <!-- Salary Table -->
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Position</th>
                            <th>Basic Salary</th>
                            <th>Allowances</th>
                            <th>Deductions</th>
                            <th>Net Salary</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="salaryTableBody">
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--salary-text-muted); padding: 40px;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                                No salary records found. Click "Add Salary" to get started.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="salary-card">
            <div class="salary-card-header">
                <h5 class="salary-card-title">
                    <i class="fas fa-history"></i>
                    Recent Salary Activities
                </h5>
            </div>
            
            <div id="recentActivities">
                <p style="text-align: center; color: var(--salary-text-muted); padding: 20px;">
                    No recent activities to display.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Salary Modal -->
<div class="modal fade" id="salaryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="salaryModalTitle">Add New Salary</h5>
                <button type="button" class="btn-close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="salaryForm">
                    @csrf
                    <!-- Employee Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employeeSelect">Employee *</label>
                                <select class="form-control" id="employeeSelect" name="user_id" required>
                                    <option value="">Select Employee</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employeeId">Employee ID</label>
                                <input type="text" class="form-control" id="employeeId" name="employee_id">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="department">Department</label>
                                <input type="text" class="form-control" id="department" name="department">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="position">Position</label>
                                <input type="text" class="form-control" id="position" name="position">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Salary Details -->
                    <h6>Salary Components</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="basicSalary">Basic Salary *</label>
                                <input type="number" class="form-control" id="basicSalary" name="basic_salary" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="salaryType">Salary Type</label>
                                <select class="form-control" id="salaryType" name="salary_type">
                                    <option value="monthly">Monthly</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="daily">Daily</option>
                                    <option value="hourly">Hourly</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Allowances -->
                    <h6>Allowances</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="houseAllowance">House Allowance</label>
                                <input type="number" class="form-control" id="houseAllowance" name="house_allowance" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="transportAllowance">Transport Allowance</label>
                                <input type="number" class="form-control" id="transportAllowance" name="transport_allowance" step="0.01" value="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mealAllowance">Meal Allowance</label>
                                <input type="number" class="form-control" id="mealAllowance" name="meal_allowance" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="medicalAllowance">Medical Allowance</label>
                                <input type="number" class="form-control" id="medicalAllowance" name="medical_allowance" step="0.01" value="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="otherAllowances">Other Allowances</label>
                                <input type="number" class="form-control" id="otherAllowances" name="other_allowances" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="grossSalary">Gross Salary (Calculated)</label>
                                <input type="number" class="form-control" id="grossSalary" name="gross_salary" step="0.01" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Deductions -->
                    <h6>Deductions</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="taxDeduction">Tax Deduction</label>
                                <input type="number" class="form-control" id="taxDeduction" name="tax_deduction" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pensionDeduction">Pension Deduction</label>
                                <input type="number" class="form-control" id="pensionDeduction" name="pension_deduction" step="0.01" value="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="insuranceDeduction">Insurance Deduction</label>
                                <input type="number" class="form-control" id="insuranceDeduction" name="insurance_deduction" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="otherDeductions">Other Deductions</label>
                                <input type="number" class="form-control" id="otherDeductions" name="other_deductions" step="0.01" value="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="totalDeductions">Total Deductions (Calculated)</label>
                                <input type="number" class="form-control" id="totalDeductions" name="total_deductions" step="0.01" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="netSalary">Net Salary (Calculated)</label>
                                <input type="number" class="form-control" id="netSalary" name="net_salary" step="0.01" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="effectiveDate">Effective Date</label>
                        <input type="date" class="form-control" id="effectiveDate" name="effective_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveSalary()">Save Salary</button>
            </div>
        </div>
    </div>
</div>

<script>
// Salary Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeSalaryManagement();
});

function initializeSalaryManagement() {
    // Set default effective date to current date
    const effectiveDateInput = document.getElementById('effectiveDate');
    if (effectiveDateInput) {
        effectiveDateInput.value = new Date().toISOString().split('T')[0];
    }
    
    // Setup calculation event listeners
    setupSalaryCalculations();
    
    // Add search functionality
    const searchInput = document.getElementById('salarySearch');
    if (searchInput) {
        searchInput.addEventListener('input', filterSalaries);
    }
    
    // Load initial data
    loadSalaryData();
    loadEmployees();
}

function setupSalaryCalculations() {
    // Get all input fields for calculation
    const salaryInputs = [
        'basicSalary', 'houseAllowance', 'transportAllowance', 'mealAllowance', 
        'medicalAllowance', 'otherAllowances', 'taxDeduction', 'pensionDeduction', 
        'insuranceDeduction', 'otherDeductions'
    ];
    
    salaryInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', calculateSalaryTotals);
            input.addEventListener('blur', calculateSalaryTotals);
        }
    });
}

function calculateSalaryTotals() {
    // Get all values
    const basicSalary = parseFloat(document.getElementById('basicSalary')?.value) || 0;
    const houseAllowance = parseFloat(document.getElementById('houseAllowance')?.value) || 0;
    const transportAllowance = parseFloat(document.getElementById('transportAllowance')?.value) || 0;
    const mealAllowance = parseFloat(document.getElementById('mealAllowance')?.value) || 0;
    const medicalAllowance = parseFloat(document.getElementById('medicalAllowance')?.value) || 0;
    const otherAllowances = parseFloat(document.getElementById('otherAllowances')?.value) || 0;
    
    const taxDeduction = parseFloat(document.getElementById('taxDeduction')?.value) || 0;
    const pensionDeduction = parseFloat(document.getElementById('pensionDeduction')?.value) || 0;
    const insuranceDeduction = parseFloat(document.getElementById('insuranceDeduction')?.value) || 0;
    const otherDeductions = parseFloat(document.getElementById('otherDeductions')?.value) || 0;
    
    // Calculate totals
    const grossSalary = basicSalary + houseAllowance + transportAllowance + mealAllowance + medicalAllowance + otherAllowances;
    const totalDeductions = taxDeduction + pensionDeduction + insuranceDeduction + otherDeductions;
    const netSalary = grossSalary - totalDeductions;
    
    // Update calculated fields
    const grossSalaryInput = document.getElementById('grossSalary');
    const totalDeductionsInput = document.getElementById('totalDeductions');
    const netSalaryInput = document.getElementById('netSalary');
    
    if (grossSalaryInput) grossSalaryInput.value = grossSalary.toFixed(2);
    if (totalDeductionsInput) totalDeductionsInput.value = totalDeductions.toFixed(2);
    if (netSalaryInput) netSalaryInput.value = netSalary.toFixed(2);
}

function addNewSalary() {
    document.getElementById('salaryModalTitle').textContent = 'Add New Salary';
    document.getElementById('salaryForm').reset();
    document.getElementById('effectiveDate').value = new Date().toISOString().split('T')[0];
    $('#salaryModal').modal('show');
}

function saveSalary() {
    const form = document.getElementById('salaryForm');
    const formData = new FormData(form);
    
    // Add CSRF token
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    // Show loading state
    const saveBtn = document.querySelector('#salaryModal .btn-primary');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    saveBtn.disabled = true;
    
    fetch('{{ route("hrm.enhanced.salary.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#salaryModal').modal('hide');
            showNotification('Salary added successfully!', 'success');
            loadSalaryData();
        } else {
            showNotification(data.message || 'Error saving salary', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving salary', 'error');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

function loadSalaryData() {
    console.log('Loading salary data...');
    
    // Fetch salary data from the server
    fetch('/hrm-enhanced/salary/settings')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateSalaryTable(data.data);
                updateDashboardStats(data.data);
            } else {
                console.error('Failed to load salary data:', data.message);
                showNotification('Failed to load salary data', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading salary data:', error);
            showNotification('Error loading salary data', 'error');
        });
}

function populateSalaryTable(salaryData) {
    const tbody = document.getElementById('salaryTableBody');
    
    if (!salaryData || salaryData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; color: var(--salary-text-muted); padding: 40px;">
                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                    No salary records found. Click "Add Salary" to get started.
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = '';
    
    salaryData.forEach(salary => {
        // Parse all values as numbers to avoid string concatenation issues
        const basicSalary = parseFloat(salary.basic_salary) || 0;
        const houseAllowance = parseFloat(salary.house_allowance) || 0;
        const transportAllowance = parseFloat(salary.transport_allowance) || 0;
        const mealAllowance = parseFloat(salary.meal_allowance) || 0;
        const medicalAllowance = parseFloat(salary.medical_allowance) || 0;
        const otherAllowances = parseFloat(salary.other_allowances) || 0;
        const taxDeduction = parseFloat(salary.tax_deduction) || 0;
        const pensionDeduction = parseFloat(salary.pension_deduction) || 0;
        const insuranceDeduction = parseFloat(salary.insurance_deduction) || 0;
        const otherDeductions = parseFloat(salary.other_deductions) || 0;
        
        const allowances = houseAllowance + transportAllowance + mealAllowance + medicalAllowance + otherAllowances;
        const deductions = taxDeduction + pensionDeduction + insuranceDeduction + otherDeductions;
        const netSalary = basicSalary + allowances - deductions;
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="employee-info">
                    <div class="employee-name">${salary.employee_name}</div>
                    <div class="employee-id">ID: ${salary.employee_id || 'N/A'}</div>
                </div>
            </td>
            <td>${salary.position || 'N/A'}</td>
            <td>₦${basicSalary.toLocaleString()}</td>
            <td>₦${allowances.toLocaleString()}</td>
            <td>₦${deductions.toLocaleString()}</td>
            <td class="net-salary">₦${netSalary.toLocaleString()}</td>
            <td>
                <span class="status-badge ${salary.active ? 'active' : 'inactive'}">
                    ${salary.active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-primary" onclick="showSalaryDetailsModal(${salary.id})" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editSalary(${salary.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteSalary(${salary.id})" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function updateDashboardStats(salaryData) {
    console.log('Updating dashboard stats with data:', salaryData);
    
    if (!salaryData || salaryData.length === 0) {
        // Set default values when no data
        const totalEmployeesElement = document.getElementById('totalEmployees');
        const totalSalaryElement = document.getElementById('totalSalary');
        const averageSalaryElement = document.getElementById('averageSalary');
        
        if (totalEmployeesElement) totalEmployeesElement.textContent = '0';
        if (totalSalaryElement) totalSalaryElement.textContent = '₦0';
        if (averageSalaryElement) averageSalaryElement.textContent = '₦0';
        return;
    }

    const totalEmployees = salaryData.length;
    const totalSalary = salaryData.reduce((sum, salary) => {
        // Parse all values as numbers to ensure proper calculation
        const basicSalary = parseFloat(salary.basic_salary) || 0;
        const houseAllowance = parseFloat(salary.house_allowance) || 0;
        const transportAllowance = parseFloat(salary.transport_allowance) || 0;
        const mealAllowance = parseFloat(salary.meal_allowance) || 0;
        const medicalAllowance = parseFloat(salary.medical_allowance) || 0;
        const otherAllowances = parseFloat(salary.other_allowances) || 0;
        const taxDeduction = parseFloat(salary.tax_deduction) || 0;
        const pensionDeduction = parseFloat(salary.pension_deduction) || 0;
        const insuranceDeduction = parseFloat(salary.insurance_deduction) || 0;
        const otherDeductions = parseFloat(salary.other_deductions) || 0;
        
        const grossSalary = basicSalary + houseAllowance + transportAllowance + mealAllowance + medicalAllowance + otherAllowances;
        const totalDeductions = taxDeduction + pensionDeduction + insuranceDeduction + otherDeductions;
        const netSalary = grossSalary - totalDeductions;
        
        console.log(`Employee ${salary.employee_name}: Basic=${basicSalary}, Gross=${grossSalary}, Deductions=${totalDeductions}, Net=${netSalary}`);
        
        return sum + netSalary;
    }, 0);
    
    const averageSalary = totalEmployees > 0 ? totalSalary / totalEmployees : 0;
    
    // Update dashboard cards using the correct IDs
    const totalEmployeesElement = document.getElementById('totalEmployees');
    const totalSalaryElement = document.getElementById('totalSalary');
    const averageSalaryElement = document.getElementById('averageSalary');
    
    console.log('Found elements:', {
        totalEmployees: !!totalEmployeesElement,
        totalSalary: !!totalSalaryElement,
        averageSalary: !!averageSalaryElement
    });
    
    if (totalEmployeesElement) {
        totalEmployeesElement.textContent = totalEmployees.toLocaleString();
        console.log('Updated total employees to:', totalEmployees);
    }
    
    if (totalSalaryElement) {
        totalSalaryElement.textContent = '₦' + totalSalary.toLocaleString('en-NG');
        console.log('Updated total salary to:', totalSalary);
    }
    
    if (averageSalaryElement) {
        averageSalaryElement.textContent = '₦' + Math.round(averageSalary).toLocaleString('en-NG');
        console.log('Updated average salary to:', averageSalary);
    }
    
    console.log('Dashboard stats updated successfully:', { totalEmployees, totalSalary, averageSalary });
}

function loadEmployees() {
    const select = document.getElementById('employeeSelect');
    select.innerHTML = '<option value="">Select Employee</option>';
    
    // Fetch employees without salary settings
    fetch('/hrm-enhanced/salary/employees-without-salary')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                data.data.forEach(employee => {
                    const option = document.createElement('option');
                    option.value = employee.id;
                    option.textContent = employee.name || `${employee.first_name} ${employee.last_name}`;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading employees:', error);
        });
}

function viewSalary(salaryId) {
    // Fetch salary details and show in modal
    fetch(`/hrm-enhanced/salary/${salaryId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSalaryDetailsModal(data.data);
            } else {
                showNotification('Failed to load salary details', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading salary details:', error);
            showNotification('Error loading salary details', 'error');
        });
}

function editSalary(salaryId) {
    // Fetch salary details and populate edit form
    fetch(`/hrm-enhanced/salary/${salaryId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateEditForm(data.data);
                $('#salaryModal').modal('show');
            } else {
                showNotification('Failed to load salary details', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading salary details:', error);
            showNotification('Error loading salary details', 'error');
        });
}

function deleteSalary(salaryId) {
    if (confirm('Are you sure you want to delete this salary record?')) {
        fetch(`/hrm-enhanced/salary/${salaryId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Salary record deleted successfully', 'success');
                loadSalaryData(); // Reload the table
            } else {
                showNotification('Failed to delete salary record', 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting salary record:', error);
            showNotification('Error deleting salary record', 'error');
        });
    }
}

function filterSalaries() {
    const searchTerm = document.getElementById('salarySearch').value.toLowerCase();
    const rows = document.querySelectorAll('#salaryTableBody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

function downloadTemplate() {
    // Show loading notification
    showNotification('Downloading salary template...', 'info');
    
    // Use fetch API for better error handling
    fetch('/hrm-enhanced/salary/template', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/csv, application/csv, */*'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.blob();
    })
    .then(blob => {
        // Create download link
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'salary_import_template.csv';
        link.style.display = 'none';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Clean up the URL object
        window.URL.revokeObjectURL(url);
        
        showNotification('Template downloaded successfully!', 'success');
    })
    .catch(error => {
        console.error('Error downloading template:', error);
        showNotification('Error downloading template: ' + error.message, 'error');
        
        // Fallback to direct link method
        console.log('Trying fallback download method...');
        const link = document.createElement('a');
        link.href = '/hrm-enhanced/salary/template';
        link.download = 'salary_import_template.csv';
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
}

function importSalaries() {
    // Create file input for CSV import
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = '.csv,.xlsx,.xls';
    fileInput.style.display = 'none';
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const formData = new FormData();
            formData.append('salary_file', file);
            
            // Show loading notification
            showNotification('Importing salary data...', 'info');
            
            fetch('/hrm-enhanced/salary/import', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => {
                // Check if response is actually JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned non-JSON response');
                }
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showNotification('Salary data imported successfully!', 'success');
                    loadSalaryData(); // Reload the table
                } else {
                    showNotification('Import failed: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Import error:', error);
                showNotification('Error importing salary data: ' + error.message, 'error');
            });
        }
    });
    
    document.body.appendChild(fileInput);
    fileInput.click();
    document.body.removeChild(fileInput);
}

function exportSalaries() {
    // Show loading notification
    showNotification('Exporting salary data...', 'info');
    
    fetch('/hrm-enhanced/salary/export', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'text/csv,application/csv'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // Check if it's actually a CSV file
            const contentType = response.headers.get('content-type');
            if (contentType && !contentType.includes('text/csv') && !contentType.includes('application/csv')) {
                console.warn('Unexpected content type:', contentType);
            }
            
            return response.blob();
        })
        .then(blob => {
            // Create download link
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'salary_data_' + new Date().toISOString().split('T')[0] + '.csv';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            showNotification('Salary data exported successfully!', 'success');
        })
        .catch(error => {
            console.error('Export error:', error);
            showNotification('Error exporting salary data: ' + error.message, 'error');
        });
}

function showNotification(message, type) {
    // Use toastr if available, otherwise use alert
    if (typeof toastr !== 'undefined') {
        toastr[type](message);
    } else {
        alert(message);
    }
}

/**
 * Show salary details in a modal
 */
function showSalaryDetailsModal(salaryId) {
    if (!salaryId) {
        showNotification('Invalid salary ID', 'error');
        return;
    }

    // Show loading state
    showNotification('Loading salary details...', 'info');

    // Fetch salary details
    fetch(`/hrm-enhanced/salary/${salaryId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySalaryDetailsModal(data.data);
            } else {
                showNotification('Failed to load salary details: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error loading salary details:', error);
            showNotification('Error loading salary details', 'error');
        });
}

/**
 * Display salary details in modal
 */
function displaySalaryDetailsModal(salaryData) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('salaryDetailsModal');
    if (!modal) {
        modal = createSalaryDetailsModal();
    }

    // Populate modal with data
    populateSalaryDetailsModal(modal, salaryData);

    // Show modal
    $(modal).modal('show');
}

/**
 * Create salary details modal
 */
function createSalaryDetailsModal() {
    const modalHtml = `
        <div class="modal fade" id="salaryDetailsModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Salary Details</h5>
                        <button type="button" class="btn-close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Employee Information</h6>
                                <table class="table table-sm">
                                    <tr><td><strong>Name:</strong></td><td id="detail-employee-name"></td></tr>
                                    <tr><td><strong>Employee ID:</strong></td><td id="detail-employee-id"></td></tr>
                                    <tr><td><strong>Department:</strong></td><td id="detail-department"></td></tr>
                                    <tr><td><strong>Position:</strong></td><td id="detail-position"></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Salary Information</h6>
                                <table class="table table-sm">
                                    <tr><td><strong>Basic Salary:</strong></td><td id="detail-basic-salary"></td></tr>
                                    <tr><td><strong>Gross Salary:</strong></td><td id="detail-gross-salary"></td></tr>
                                    <tr><td><strong>Total Deductions:</strong></td><td id="detail-total-deductions"></td></tr>
                                    <tr><td><strong>Net Salary:</strong></td><td id="detail-net-salary"></td></tr>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Allowances</h6>
                                <table class="table table-sm">
                                    <tr><td>House Allowance:</td><td id="detail-house-allowance"></td></tr>
                                    <tr><td>Transport Allowance:</td><td id="detail-transport-allowance"></td></tr>
                                    <tr><td>Meal Allowance:</td><td id="detail-meal-allowance"></td></tr>
                                    <tr><td>Medical Allowance:</td><td id="detail-medical-allowance"></td></tr>
                                    <tr><td>Other Allowances:</td><td id="detail-other-allowances"></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Deductions</h6>
                                <table class="table table-sm">
                                    <tr><td>Tax Deduction:</td><td id="detail-tax-deduction"></td></tr>
                                    <tr><td>Pension Deduction:</td><td id="detail-pension-deduction"></td></tr>
                                    <tr><td>Insurance Deduction:</td><td id="detail-insurance-deduction"></td></tr>
                                    <tr><td>Other Deductions:</td><td id="detail-other-deductions"></td></tr>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <h6>Additional Information</h6>
                                <table class="table table-sm">
                                    <tr><td><strong>Effective Date:</strong></td><td id="detail-effective-date"></td></tr>
                                    <tr><td><strong>Status:</strong></td><td id="detail-status"></td></tr>
                                    <tr><td><strong>Created:</strong></td><td id="detail-created-at"></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="editSalaryFromModal()">Edit Salary</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    return document.getElementById('salaryDetailsModal');
}

/**
 * Populate salary details modal with data
 */
function populateSalaryDetailsModal(modal, data) {
    // Employee Information
    modal.querySelector('#detail-employee-name').textContent = data.employee_name || 'N/A';
    modal.querySelector('#detail-employee-id').textContent = data.employee_id || 'N/A';
    modal.querySelector('#detail-department').textContent = data.department || 'N/A';
    modal.querySelector('#detail-position').textContent = data.position || 'N/A';

    // Salary Information
    modal.querySelector('#detail-basic-salary').textContent = '₦' + (parseFloat(data.basic_salary) || 0).toLocaleString();
    modal.querySelector('#detail-gross-salary').textContent = '₦' + (parseFloat(data.gross_salary) || 0).toLocaleString();
    
    // Calculate total deductions if not provided
    const totalDeductions = (parseFloat(data.tax_deduction) || 0) + 
                           (parseFloat(data.pension_deduction) || 0) + 
                           (parseFloat(data.insurance_deduction) || 0) + 
                           (parseFloat(data.other_deductions) || 0);
    
    modal.querySelector('#detail-total-deductions').textContent = '₦' + totalDeductions.toLocaleString();
    modal.querySelector('#detail-net-salary').textContent = '₦' + (parseFloat(data.net_salary) || 0).toLocaleString();

    // Allowances
    modal.querySelector('#detail-house-allowance').textContent = '₦' + (parseFloat(data.house_allowance) || 0).toLocaleString();
    modal.querySelector('#detail-transport-allowance').textContent = '₦' + (parseFloat(data.transport_allowance) || 0).toLocaleString();
    modal.querySelector('#detail-meal-allowance').textContent = '₦' + (parseFloat(data.meal_allowance) || 0).toLocaleString();
    modal.querySelector('#detail-medical-allowance').textContent = '₦' + (parseFloat(data.medical_allowance) || 0).toLocaleString();
    modal.querySelector('#detail-other-allowances').textContent = '₦' + (parseFloat(data.other_allowances) || 0).toLocaleString();

    // Deductions
    modal.querySelector('#detail-tax-deduction').textContent = '₦' + (parseFloat(data.tax_deduction) || 0).toLocaleString();
    modal.querySelector('#detail-pension-deduction').textContent = '₦' + (parseFloat(data.pension_deduction) || 0).toLocaleString();
    modal.querySelector('#detail-insurance-deduction').textContent = '₦' + (parseFloat(data.insurance_deduction) || 0).toLocaleString();
    modal.querySelector('#detail-other-deductions').textContent = '₦' + (parseFloat(data.other_deductions) || 0).toLocaleString();

    // Additional Information
    modal.querySelector('#detail-effective-date').textContent = data.salary_effective_date || 'N/A';
    modal.querySelector('#detail-status').textContent = data.active ? 'Active' : 'Inactive';
    modal.querySelector('#detail-created-at').textContent = data.created_at || 'N/A';

    // Store salary ID for edit functionality
    modal.setAttribute('data-salary-id', data.id);
}

/**
 * Populate edit form with salary data
 */
function populateEditForm(salaryData) {
    if (!salaryData) {
        showNotification('No salary data provided', 'error');
        return;
    }

    try {
        // Basic information
        const employeeSelect = document.getElementById('employeeSelect');
        const employeeIdField = document.getElementById('employeeId');
        const departmentField = document.getElementById('department');
        const positionField = document.getElementById('position');

        if (employeeSelect) employeeSelect.value = salaryData.user_id || '';
        if (employeeIdField) employeeIdField.value = salaryData.employee_id || '';
        if (departmentField) departmentField.value = salaryData.department || '';
        if (positionField) positionField.value = salaryData.position || '';

        // Salary components
        const basicSalaryField = document.getElementById('basicSalary');
        const salaryTypeField = document.getElementById('salaryType');

        if (basicSalaryField) basicSalaryField.value = salaryData.basic_salary || '';
        if (salaryTypeField) salaryTypeField.value = salaryData.salary_type || 'monthly';

        // Allowances
        const allowanceFields = [
            { id: 'houseAllowance', value: salaryData.house_allowance },
            { id: 'transportAllowance', value: salaryData.transport_allowance },
            { id: 'mealAllowance', value: salaryData.meal_allowance },
            { id: 'medicalAllowance', value: salaryData.medical_allowance },
            { id: 'otherAllowances', value: salaryData.other_allowances }
        ];

        allowanceFields.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) element.value = field.value || '';
        });

        // Deductions
        const deductionFields = [
            { id: 'taxDeduction', value: salaryData.tax_deduction },
            { id: 'pensionDeduction', value: salaryData.pension_deduction },
            { id: 'insuranceDeduction', value: salaryData.insurance_deduction },
            { id: 'otherDeductions', value: salaryData.other_deductions }
        ];

        deductionFields.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) element.value = field.value || '';
        });

        // Dates
        const effectiveDateField = document.getElementById('effectiveDate');
        if (effectiveDateField) effectiveDateField.value = salaryData.salary_effective_date || '';

        // Bank details (if fields exist)
        const bankFields = [
            { id: 'bankName', value: salaryData.bank_name },
            { id: 'accountNumber', value: salaryData.account_number },
            { id: 'accountName', value: salaryData.account_name }
        ];

        bankFields.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) element.value = field.value || '';
        });

        // Notes
        const notesField = document.getElementById('notes');
        if (notesField) notesField.value = salaryData.notes || '';

        // Trigger calculation
        if (typeof calculateSalaryTotals === 'function') {
            calculateSalaryTotals();
        }

        // Update modal title and store ID for updates
        const modalTitle = document.getElementById('salaryModalTitle');
        if (modalTitle) modalTitle.textContent = 'Edit Salary';
        
        const form = document.getElementById('salaryForm');
        if (form) form.setAttribute('data-salary-id', salaryData.id);

        console.log('Form populated with salary data:', salaryData);
        showNotification('Form populated successfully', 'success');
    } catch (error) {
        console.error('Error populating edit form:', error);
        showNotification('Error populating form data', 'error');
    }
}

/**
 * Edit salary from details modal
 */
function editSalaryFromModal() {
    const modal = document.getElementById('salaryDetailsModal');
    const salaryId = modal.getAttribute('data-salary-id');
    
    if (salaryId) {
        // Close details modal
        $(modal).modal('hide');
        
        // Load salary data and open edit form
        editSalary(salaryId);
    }
}
</script>
@endsection
