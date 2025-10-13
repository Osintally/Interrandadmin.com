@extends('layouts.app')

@section('title', __('Attendance & Payroll Management'))

@section('css')
  <!-- AttendanceMS Dashboard Styles -->
  <style>
    /* AttendanceMS Variables */
    :root {
      --attendance-primary: #4285f4;
      --attendance-primary-dark: #3367d6;
      --attendance-secondary: #34a853;
      --attendance-warning: #fbbc04;
      --attendance-danger: #ea4335;
      --attendance-success: #34a853;
      --attendance-surface: #ffffff;
      --attendance-surface-light: #f8f9fa;
      --attendance-text: #202124;
      --attendance-text-secondary: #5f6368;
      --attendance-text-muted: #9aa0a6;
      --attendance-border: #dadce0;
      --attendance-radius: 8px;
      --attendance-shadow: 0 1px 2px 0 rgba(60, 64, 67, .3), 0 1px 3px 1px rgba(60, 64, 67, .15);
      --attendance-shadow-lg: 0 2px 6px 2px rgba(60, 64, 67, .15), 0 1px 2px 0 rgba(60, 64, 67, .3);
    }

    /* Main Layout */
    .attendance-dashboard {
      background: var(--attendance-surface-light);
      min-height: 100vh;
      padding: 0;
    }

    /* Horizontal Navigation Bar */
    .attendance-nav-tabs {
      display: flex;
      background: var(--attendance-surface);
      border: 1px solid var(--attendance-border);
      border-radius: var(--attendance-radius);
      margin-bottom: 24px;
      overflow: hidden;
      box-shadow: var(--attendance-shadow);
    }

    .attendance-nav-tab {
      flex: 1;
      padding: 16px 24px;
      background: none;
      border: none;
      cursor: pointer;
      transition: all 0.2s ease;
      text-align: center;
      font-weight: 500;
      color: var(--attendance-text-secondary);
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .attendance-nav-tab:hover {
      background: var(--attendance-surface-light);
      color: var(--attendance-text);
    }

    .attendance-nav-tab.active {
      background: var(--attendance-primary);
      color: white;
    }

    .attendance-nav-tab.active::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--attendance-primary), var(--attendance-secondary));
    }

    .attendance-nav-tab i {
      font-size: 16px;
    }

    /* Main Content */
    .attendance-main {
      padding: 0;
      background: var(--attendance-surface-light);
    }

    /* Page Header */
    .page-header {
      background: white;
      padding: 20px 24px;
      border-bottom: 1px solid var(--attendance-border);
      margin-bottom: 24px;
      box-shadow: var(--attendance-shadow);
    }

    .page-header h1 {
      font-size: 28px;
      font-weight: 600;
      color: var(--attendance-text);
      margin: 0 0 8px 0;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .page-header p {
      color: var(--attendance-text-secondary);
      margin: 0;
      font-size: 16px;
    }


    /* Content Area */
    .attendance-content {
      padding: 0 24px 24px 24px;
    }

    /* Stats Cards */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 32px;
    }

    .stat-card {
      background: white;
      border-radius: var(--attendance-radius);
      padding: 24px;
      box-shadow: var(--attendance-shadow);
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

    .stat-card.employees::before {
      background: var(--attendance-primary);
    }

    .stat-card.punctual::before {
      background: var(--attendance-secondary);
    }

    .stat-card.inactive::before {
      background: var(--attendance-warning);
    }

    .stat-card.attendance::before {
      background: var(--attendance-success);
    }

    .stat-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 16px;
    }


    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: var(--attendance-radius);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      color: white;
    }

    .stat-card.employees .stat-icon {
      background: var(--attendance-primary);
    }

    .stat-card.punctual .stat-icon {
      background: var(--attendance-secondary);
    }

    .stat-card.inactive .stat-icon {
      background: var(--attendance-warning);
    }

    .stat-card.attendance .stat-icon {
      background: var(--attendance-success);
    }

    .stat-value {
      font-size: 32px;
      font-weight: 600;
      color: var(--attendance-text);
      margin-bottom: 4px;
    }

    .stat-label {
      color: var(--attendance-text-secondary);
      font-size: 14px;
      font-weight: 400;
    }

    /* Data Upload Section */
    .upload-section {
      background: white;
      border-radius: var(--attendance-radius);
      padding: 24px;
      box-shadow: var(--attendance-shadow);
    }

    .upload-section h5 {
      color: var(--attendance-text);
      font-weight: 600;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .upload-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      margin-bottom: 24px;
    }

    .upload-item {
      display: flex;
      flex-direction: column;
    }

    .upload-item label {
      color: var(--attendance-text-secondary);
      font-size: 14px;
      font-weight: 500;
      margin-bottom: 8px;
    }

    .file-input-wrapper {
      position: relative;
    }

    .file-input {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid var(--attendance-border);
      border-radius: var(--attendance-radius);
      background: white;
      color: var(--attendance-text);
      font-size: 14px;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .file-input:hover {
      border-color: var(--attendance-primary);
    }

    .file-input:focus {
      outline: none;
      border-color: var(--attendance-primary);
      box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
    }

    /* Settings Row */
    .settings-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 24px;
    }

    .setting-item {
      display: flex;
      flex-direction: column;
    }

    .setting-item label {
      color: var(--attendance-text-secondary);
      font-size: 14px;
      font-weight: 500;
      margin-bottom: 8px;
    }

    .setting-input {
      padding: 12px 16px;
      border: 1px solid var(--attendance-border);
      border-radius: var(--attendance-radius);
      background: white;
      color: var(--attendance-text);
      font-size: 14px;
      transition: all 0.2s ease;
    }

    .setting-input:focus {
      outline: none;
      border-color: var(--attendance-primary);
      box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
    }

    /* Action Buttons */
    .action-buttons {
      display: flex;
      gap: 12px;
      align-items: center;
      flex-wrap: wrap;
    }

    /* Payroll Action Buttons */
    .payroll-action-btn {
      transition: all 0.2s ease;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .payroll-action-btn:hover:not(:disabled) {
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .payroll-action-btn:disabled {
      cursor: not-allowed !important;
      opacity: 0.6 !important;
    }

    .payroll-action-btn:disabled:hover {
      transform: none !important;
      box-shadow: none !important;
    }

    /* Edit and Delete Button Styles */
    .edit-payroll-btn, .delete-payroll-btn {
      transition: all 0.2s ease;
      border-radius: 4px;
      font-size: 11px;
      padding: 4px 8px;
      min-width: 32px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .edit-payroll-btn:hover:not(:disabled) {
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .delete-payroll-btn:hover:not(:disabled) {
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .edit-payroll-btn:disabled, .delete-payroll-btn:disabled {
      cursor: not-allowed !important;
      opacity: 0.6 !important;
    }

    .edit-payroll-btn:disabled:hover, .delete-payroll-btn:disabled:hover {
      transform: none !important;
      box-shadow: none !important;
    }

    /* Modal Styles for Edit Payroll */
    .modal-lg {
      max-width: 800px;
    }

    .form-label {
      font-weight: 600;
      color: var(--attendance-text-secondary);
      margin-bottom: 5px;
    }

    .form-control {
      border: 1px solid var(--attendance-border);
      border-radius: var(--attendance-radius);
      padding: 8px 12px;
      font-size: 14px;
      transition: all 0.2s ease;
    }

    .form-control:focus {
      border-color: var(--attendance-primary);
      box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
      outline: none;
    }

    .form-control:read-only {
      background-color: var(--attendance-surface-light);
      color: var(--attendance-text-muted);
    }

    .mb-3 {
      margin-bottom: 1rem;
    }

    .btn-process {
      background: var(--attendance-primary);
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: var(--attendance-radius);
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-process:hover {
      background: var(--attendance-primary-dark);
      transform: translateY(-1px);
    }

    .btn-clear {
      background: white;
      color: var(--attendance-text-secondary);
      border: 1px solid var(--attendance-border);
      padding: 12px 24px;
      border-radius: var(--attendance-radius);
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .btn-clear:hover {
      background: var(--attendance-surface-light);
      border-color: var(--attendance-text-secondary);
    }

    .btn-template {
      background: var(--attendance-secondary);
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: var(--attendance-radius);
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-template:hover {
      background: #2d8f47;
      transform: translateY(-1px);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .attendance-nav-tabs {
        flex-direction: column;
      }

      .attendance-nav-tab {
        padding: 12px 16px;
      }

      .stats-grid {
        grid-template-columns: 1fr;
        gap: 16px;
      }

      .upload-grid {
        grid-template-columns: 1fr;
      }

      .settings-grid {
        grid-template-columns: 1fr;
      }

      .page-header {
        padding: 16px 20px;
      }

      .page-header h1 {
        font-size: 24px;
      }

      .attendance-content {
        padding: 0 16px 16px 16px;
      }

      .action-buttons {
        flex-direction: column;
        gap: 8px;
      }

      .btn-process,
      .btn-clear,
      .btn-template {
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
      border-top: 2px solid var(--attendance-primary);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    /* Enhanced focus states for accessibility */
    .attendance-nav-tab:focus,
    .btn-process:focus,
    .btn-clear:focus,
    .btn-template:focus,
    .file-input:focus {
      outline: 2px solid var(--attendance-primary);
      outline-offset: 2px;
    }

    /* Print styles */
    @media print {

      .page-header,
      .attendance-nav-tabs,
      .upload-section {
        display: none;
      }
    }

    /* Status Pills for Attendance Data */
    .status-pill {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
    }

    .status-present {
      background: rgba(34, 197, 94, 0.2);
      color: #22c55e;
    }

    .status-absent {
      background: rgba(239, 68, 68, 0.2);
      color: #ef4444;
    }

    .status-late {
      background: rgba(245, 158, 11, 0.2);
      color: #f59e0b;
    }

    .status-success {
      background: rgba(34, 197, 94, 0.2);
      color: #22c55e;
    }

    .status-warning {
      background: rgba(245, 158, 11, 0.2);
      color: #f59e0b;
    }

    /* Table Container - Enhanced Style */
    .table-container {
      overflow: hidden;
      border-radius: var(--attendance-radius);
      border: 1px solid var(--attendance-border);
      box-shadow: var(--attendance-shadow);
      background: var(--attendance-surface);
    }

    .table {
      width: 100%;
      border-collapse: collapse;
      background: var(--attendance-surface);
      margin-bottom: 0;
    }

    .table th,
    .table td {
      padding: 12px 16px;
      text-align: left;
      border-bottom: 1px solid var(--attendance-border);
      vertical-align: middle;
    }

    .table th {
      background: var(--attendance-surface-light);
      font-weight: 600;
      color: var(--attendance-text-secondary);
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
      background: var(--attendance-surface-light);
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .table tbody tr:last-child td {
      border-bottom: none;
    }

    /* Button Styling */
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      border: none;
      border-radius: var(--attendance-radius);
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s ease;
      text-decoration: none;
      font-size: 13px;
      line-height: 1;
    }

    .btn-primary {
      background: var(--attendance-primary);
      color: white;
      box-shadow: var(--attendance-shadow);
    }

    .btn-primary:hover {
      background: var(--attendance-primary-dark);
      transform: translateY(-1px);
      box-shadow: var(--attendance-shadow-lg);
    }

    .btn-sm {
      padding: 6px 12px;
      font-size: 12px;
    }

    /* Modal */
    .modal {
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      right: 0 !important;
      bottom: 0 !important;
      background: rgba(0, 0, 0, 0.5) !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      z-index: 9999 !important;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    }

    .modal.show {
      opacity: 1 !important;
      visibility: visible !important;
    }

    .modal-content {
      background: var(--attendance-surface) !important;
      border-radius: var(--attendance-radius) !important;
      width: 90% !important;
      max-width: 600px !important;
      max-height: 90vh !important;
      overflow-y: auto !important;
      transform: scale(0.9);
      transition: transform 0.3s ease;
      box-shadow: var(--attendance-shadow-lg) !important;
      position: relative !important;
    }

    .modal.show .modal-content {
      transform: scale(1);
    }

    .modal-header {
      padding: 24px !important;
      border-bottom: 1px solid var(--attendance-border) !important;
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
      background: var(--attendance-surface-light) !important;
    }

    .modal-title {
      font-size: 20px !important;
      font-weight: 600 !important;
      color: var(--attendance-text) !important;
      margin: 0 !important;
    }

    .modal-close,
    .btn-close {
      background: none !important;
      border: none !important;
      color: var(--attendance-text-muted) !important;
      cursor: pointer !important;
      font-size: 24px !important;
      padding: 8px !important;
      border-radius: 4px !important;
      transition: all 0.2s ease !important;
    }

    .modal-close:hover,
    .btn-close:hover {
      background: var(--attendance-surface-light) !important;
      color: var(--attendance-text) !important;
    }

    .modal-body {
      padding: 24px !important;
      background: var(--attendance-surface) !important;
    }

    /* Attendance Content Views */
    .attendance-view {
      display: none;
      animation: fadeIn 0.3s ease-in-out;
    }

    .attendance-view.active {
      display: block;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Form Input Styling */
    .form-input {
      width: 100%;
      padding: 12px 16px;
      background: var(--attendance-surface);
      border: 1px solid var(--attendance-border);
      border-radius: var(--attendance-radius);
      color: var(--attendance-text);
      font-size: 14px;
      transition: all 0.2s ease;
    }

    .form-input:focus {
      outline: none;
      border-color: var(--attendance-primary);
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* File feedback styling */
    .file-feedback {
      margin-top: 0.25rem;
      font-size: 0.875rem;
    }

    .file-feedback.text-success {
      color: var(--attendance-success) !important;
    }

    .file-feedback.text-danger {
      color: var(--attendance-danger) !important;
    }

    .file-feedback.text-muted {
      color: var(--attendance-text-muted) !important;
    }

    /* Additional responsive adjustments */
    @media (max-width: 480px) {

      .upload-grid,
      .settings-grid {
        grid-template-columns: 1fr;
        gap: 12px;
      }

      .stats-grid {
        gap: 12px;
      }

      .stat-card {
        padding: 16px;
      }

      .upload-section {
        padding: 16px;
      }
    }
  </style>
  <!-- Chart.js for analytics -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- SheetJS for Excel processing -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <!-- jQuery for toastr dependency -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <!-- Toastr for notifications -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
@endsection

@section('content')
  <div class="attendance-dashboard">
    <!-- Page Header -->
    <div class="page-header">
      <h1>
        <i class="fas fa-calendar-check text-primary"></i>
        AttendanceMS
      </h1>
      <p>Comprehensive attendance tracking and payroll processing system</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="attendance-content">
      <div class="attendance-nav-tabs">
        <button class="attendance-nav-tab active" data-tab="dashboard">
          <i class="fas fa-chart-line"></i>
          <span>Dashboard</span>
        </button>
        <button class="attendance-nav-tab" data-tab="attendance">
          <i class="fas fa-calendar-check"></i>
          <span>Attendance</span>
        </button>
        <button class="attendance-nav-tab" data-tab="payroll">
          <i class="fas fa-money-bill-wave"></i>
          <span>Payroll</span>
        </button>
        <button class="attendance-nav-tab" data-tab="settings">
          <i class="fas fa-cog"></i>
          <span>Settings</span>
        </button>
      </div>

      <!-- Tab Content -->
      <div class="attendance-view active" id="dashboard">
        <!-- Stats Cards -->
        <div class="stats-grid">
          <div class="stat-card employees">
            <div class="stat-header">
              <div class="stat-icon">
                <i class="fas fa-users"></i>
              </div>
            </div>
            <div class="stat-value" id="totalEmployees">{{ $stats['total_employees'] ?? 50 }}</div>
            <div class="stat-label">Total Employees</div>
          </div>

          <div class="stat-card punctual">
            <div class="stat-header">
              <div class="stat-icon">
                <i class="fas fa-medal"></i>
              </div>
            </div>
            <div class="stat-value" id="mostPunctual">{{ $stats['most_punctual'] ?? 'Suleiman' }}</div>
            <div class="stat-label">Most Punctual</div>
          </div>

          <div class="stat-card inactive">
            <div class="stat-header">
              <div class="stat-icon">
                <i class="fas fa-user-slash"></i>
              </div>
            </div>
            <div class="stat-value" id="inactiveUsers">{{ $stats['inactive_users'] ?? 21 }}</div>
            <div class="stat-label">Inactive Users</div>
          </div>

          <div class="stat-card attendance">
            <div class="stat-header">
              <div class="stat-icon">
                <i class="fas fa-percentage"></i>
              </div>
            </div>
            <div class="stat-value" id="avgAttendance">{{ $stats['attendance_rate'] ?? '22.2' }}%</div>
            <div class="stat-label">Avg Attendance</div>
          </div>
        </div>

        <!-- Data Upload Section -->
        <div class="upload-section" id="uploadSection">
          <h5>
            <i class="fas fa-upload"></i>
            Data Upload
          </h5>

          <form id="uploadForm" method="POST" action="{{ route('hrm.enhanced.attendance.process') }}"
            enctype="multipart/form-data">
            @csrf
            <!-- File Upload Grid -->
            <div class="upload-grid">
              <div class="upload-item">
                <label>User mapping file (user.dat)</label>
                <div class="file-input-wrapper">
                  <input type="file" id="userFile" name="user_file" class="file-input" accept=".dat,.txt"
                    placeholder="Choose file user.dat" />
                </div>
              </div>

              <div class="upload-item">
                <label>Attendance logs (.dat)</label>
                <div class="file-input-wrapper">
                  <input type="file" id="attFiles" name="attendance_files[]" class="file-input" accept=".dat,.txt"
                    multiple placeholder="ECV323630145_attlog.dat" />
                </div>
              </div>

              <div class="upload-item">
                <label>Payroll file (.xlsx)</label>
                <div class="file-input-wrapper">
                  <input type="file" id="payrollFile" name="payroll_file" class="file-input" accept=".xlsx,.xls"
                    placeholder="INTERRAND ATTEND...P LOG Sept 2025.xlsx" />
                </div>
                <div class="file-feedback text-muted mt-1">
                  <small><i class="fas fa-info-circle"></i> Upload your payroll Excel file or <a href="#"
                      id="downloadTemplate" class="text-primary">download our template</a></small>
                </div>
              </div>
            </div>

            <!-- Settings Grid -->
            <div class="settings-grid">
              <div class="setting-item">
                <label>Official start time</label>
                <input type="time" id="startTime" name="start_time" class="setting-input" value="08:00" />
              </div>

              <div class="setting-item">
                <label>Grace minutes</label>
                <input type="number" id="graceMin" name="grace_minutes" class="setting-input" value="15" min="0" />
              </div>

              <div class="setting-item">
                <label>End of work time</label>
                <input type="time" id="endTime" name="end_time" class="setting-input" value="17:00" />
              </div>

              <div class="setting-item">
                <label>Working Days per Month</label>
                <input type="number" name="working_days" class="setting-input" value="21" min="1" max="31" />
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
              <button type="submit" class="btn-process" id="processBtn">
                <i class="fas fa-play"></i>
                Process Data
              </button>
              <button type="button" class="btn-template" id="downloadTemplateBtn">
                <i class="fas fa-download"></i>
                Download Template
              </button>
              <button type="button" class="btn-clear" id="clearBtn">
                <i class="fas fa-times"></i>
                Clear All
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Attendance Tab Content -->
      <div class="attendance-view" id="attendance" style="display: none;">
        <div class="upload-section">
          <h5>
            <i class="fas fa-calendar-check"></i>
            Attendance Records
          </h5>

          <!-- Attendance table will be rendered here -->
          <div id="attendanceTableContainer">
            <p class="text-muted">No attendance data available. Please upload and process attendance files first.</p>
          </div>
        </div>
      </div>

      <!-- Payroll Tab Content -->
      <div class="attendance-view" id="payroll" style="display: none;">
        <div class="upload-section">
          <h5>
            <i class="fas fa-money-bill-wave"></i>
            Payroll Management
          </h5>

          <!-- Payroll table will be rendered here -->
          <div id="payrollTableContainer">
            <p class="text-muted">No payroll data available. Please upload and process payroll files first.</p>
          </div>
        </div>
      </div>

      <!-- Settings Tab Content -->
      <div class="attendance-view" id="settings" style="display: none;">
        <div class="upload-section">
          <h5>
            <i class="fas fa-cog"></i>
            System Settings
          </h5>

          <form id="settingsForm">
            @csrf
            <div class="settings-grid">
              <div class="setting-item">
                <label>Default Start Time</label>
                <input type="time" name="default_start_time" class="setting-input" value="08:00" />
              </div>

              <div class="setting-item">
                <label>Default End Time</label>
                <input type="time" name="default_end_time" class="setting-input" value="17:00" />
              </div>

              <div class="setting-item">
                <label>Grace Period (minutes)</label>
                <input type="number" name="grace_period" class="setting-input" value="15" min="0" max="60" />
              </div>

              <div class="setting-item">
                <label>Late Threshold (minutes)</label>
                <input type="number" name="late_threshold" class="setting-input" value="30" min="1" max="120" />
              </div>

              <div class="setting-item">
                <label>Working Days per Week</label>
                <select name="working_days" class="setting-input">
                  <option value="5">5 Days</option>
                  <option value="6">6 Days</option>
                  <option value="7">7 Days</option>
                </select>
              </div>

              <div class="setting-item">
                <label>Overtime Rate (%)</label>
                <input type="number" name="overtime_rate" class="setting-input" value="150" min="100" max="300" />
              </div>
            </div>

            <div class="action-buttons">
              <button type="button" class="btn-process" onclick="saveSettingsLaravel()">
                <i class="fas fa-save"></i>
                Save Settings
              </button>
              <button type="button" class="btn-clear" onclick="resetSettings()">
                <i class="fas fa-undo"></i>
                Reset to Default
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for detailed attendance view -->
  <div class="modal" id="attendanceModal">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Employee Attendance Details</h5>
        <button type="button" class="btn-close" onclick="closeModal()">&times;</button>
      </div>
      <div class="modal-body" id="modalBody">
        <!-- Modal content will be populated here -->
      </div>
    </div>
  </div>

  <script>
    // User permissions for payroll approval workflow
    window.userPermissions = {
      @auth
        @can('payroll.edit')
          'payroll.edit': true,
        @endcan
        @can('payroll.delete')
          'payroll.delete': true,
        @endcan
        @can('payroll.submit_for_review')
          'payroll.submit_for_review': true,
        @endcan
        @can('payroll.review')
          'payroll.review': true,
        @endcan
        @can('payroll.approve')
          'payroll.approve': true,
        @endcan
        @can('payroll.reject')
          'payroll.reject': true,
        @endcan
        @can('payroll.export')
          'payroll.export': true,
        @endcan
        @can('payroll.view_approval_status')
          'payroll.view_approval_status': true,
        @endcan
      @endauth
    };

    // Tab switching functionality
    function initTabSwitching() {
      const navTabs = document.querySelectorAll('.attendance-nav-tab');

      navTabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
          const tabName = this.dataset.tab;

          // Update active nav tab
          navTabs.forEach(function (t) { t.classList.remove('active'); });
          this.classList.add('active');

          // Show/hide content sections
          showTabContent(tabName);
        });
      });
    }

    function showTabContent(tab) {
      // Hide all content sections
      const allSections = document.querySelectorAll('.attendance-view');
      allSections.forEach(function (section) {
        section.style.display = 'none';
        section.classList.remove('active');
      });

      // Show selected section
      const activeSection = document.getElementById(tab);
      if (activeSection) {
        activeSection.style.display = 'block';
        activeSection.classList.add('active');
      }
    }

    // Modal functions
    function closeModal() {
      const modal = document.getElementById('attendanceModal');
      if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
      }
    }

    // ATTENDANCE CONFIGURATION - Centralized settings
    const ATTENDANCE_CONFIG = {
      START_TIME_MINUTES: 480,    // 8:00 AM
      LOW_LATE_THRESHOLD: 510,    // 8:30 AM
      DEDUCTIONS: {
        ABSENT: 2000,
        LATE: 1000,
        LOW_LATE: 500
      },
      STATUS: {
        NO_LATE: 'No',
        LOW_LATE: 'Low_Late',
        LATE: 'Late'
      }
    };

    // UNIFIED LATE LOGIC - Single source of truth
    function calculateLateStatus(clockInTime) {
      if (!clockInTime || clockInTime === 'N/A') {
        return ATTENDANCE_CONFIG.STATUS.NO_LATE;
      }
      
      const clockIn = new Date(clockInTime);
      const clockInMinutes = clockIn.getHours() * 60 + clockIn.getMinutes();
      
      if (clockInMinutes <= ATTENDANCE_CONFIG.START_TIME_MINUTES) {
        return ATTENDANCE_CONFIG.STATUS.NO_LATE;
      } else if (clockInMinutes <= ATTENDANCE_CONFIG.LOW_LATE_THRESHOLD) {
        return ATTENDANCE_CONFIG.STATUS.LOW_LATE;
      } else {
        return ATTENDANCE_CONFIG.STATUS.LATE;
      }
    }

    // Data Integrity Function - Proper Late Logic (DEPRECATED - Use calculateLateStatus instead)
    function processAttendanceDataWithIntegrity(userDetails) {
      return userDetails.map(record => {
        const processed = { ...record };
        
        // Use unified late logic
        processed.Late = calculateLateStatus(record.Clockin);
        
        // Calculate proper work hours
        if (record.Clockin && record.Clockout && 
            record.Clockin !== 'N/A' && record.Clockout !== 'N/A') {
          const clockIn = new Date(record.Clockin);
          const clockOut = new Date(record.Clockout);
          
          if (!isNaN(clockIn.getTime()) && !isNaN(clockOut.getTime())) {
            const diffMs = clockOut.getTime() - clockIn.getTime();
            const hours = Math.max(0, diffMs / (1000 * 60 * 60));
            processed.TotalHours = hours.toFixed(2);
            
            // Ensure status is correct based on work hours
            if (hours > 0) {
              processed.Status = 'Present';
            } else {
              processed.Status = 'Absent';
            }
          }
        }
        
        return processed;
      });
    }

    // Attendance Detail Modal Functions
    function showAttendanceDetailModal(userId, username) {
      console.log('Opening detail modal for user:', userId, username);

      if (!state.processedResults || !state.processedResults.detail) {
        showUserNotification('No detailed data available', 'warning');
        return;
      }

      // Filter details for this user
      const userDetails = state.processedResults.detail.filter(function (record) {
        return record.UserId === userId;
      });

      if (userDetails.length === 0) {
        showUserNotification('No attendance details found for this user', 'warning');
        return;
      }

      // Create or update modal
      let modal = document.getElementById('attendanceDetailModal');
      if (!modal) {
        modal = createAttendanceDetailModal();
        document.body.appendChild(modal);
      }

      // Update modal content
      updateAttendanceDetailModal(modal, userId, username, userDetails);

      // Show modal with Bootstrap classes
      modal.style.display = 'block';
      modal.classList.add('show');
      document.body.classList.add('modal-open');

      // Add backdrop
      if (!document.querySelector('.modal-backdrop')) {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
      }

      console.log('Detail modal opened with', userDetails.length, 'records');
    }

    function createAttendanceDetailModal() {
      const modal = document.createElement('div');
      modal.id = 'attendanceDetailModal';
      modal.className = 'modal fade';
      modal.setAttribute('tabindex', '-1');
      modal.setAttribute('role', 'dialog');
      modal.style.display = 'none';
      modal.innerHTML = `
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="margin: 1.75rem auto;">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Attendance Details</h5>
                        <button type="button" class="btn-close btn-close-white" onclick="closeAttendanceDetailModal()" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <div id="attendanceDetailContent">
                            <div class="text-center">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeAttendanceDetailModal()">Close</button>
                    </div>
                </div>
            </div>
        `;

      // Add click outside to close
      modal.addEventListener('click', function (e) {
        if (e.target === modal) {
          closeAttendanceDetailModal();
        }
      });

      return modal;
    }

    function updateAttendanceDetailModal(modal, userId, username, userDetails) {
      const title = modal.querySelector('.modal-title');
      title.textContent = `21-Day Attendance Details - ${username} (ID: ${userId})`;

      const content = modal.querySelector('#attendanceDetailContent');

      // Use the already processed data from state.processedResults.detail
      // This data already has the correct late logic applied from buildOutputs function
      
      // DEBUG: Log the first few records to see what data we're getting
      console.log('Modal userDetails (first 3 records):', userDetails.slice(0, 3));
      
      const presentDays = userDetails.filter(r => r.Status === 'Present').length;
      const absentDays = userDetails.filter(r => r.Status === 'Absent').length;
      const lateDays = userDetails.filter(r => r.Late === 'Late').length;
      const lowLateDays = userDetails.filter(r => r.Late === 'Low_Late').length;
      
      // Calculate total work hours
      const totalWorkHours = userDetails
        .filter(r => r.Status === 'Present')
        .reduce((sum, r) => sum + parseFloat(r.TotalHours || 0), 0);
      const averageWorkHours = presentDays > 0 ? (totalWorkHours / presentDays).toFixed(2) : 0;

      const summaryHTML = `
            <h6>Daily Attendance Records:</h6>
        `;

      // Create cards panel
      const cardsPanel = document.createElement('div');
      cardsPanel.className = 'row mb-4';
      cardsPanel.innerHTML = `
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h4>${presentDays}</h4>
                        <p class="mb-0">Present Days</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h4>${absentDays}</h4>
                        <p class="mb-0">Absent Days</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h4>${lateDays}</h4>
                        <p class="mb-0">Late Days</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h4>${lowLateDays}</h4>
                        <p class="mb-0">Minor Late</p>
                    </div>
                </div>
            </div>
        `;

      const table = createDetailedAttendanceTable(userDetails);
      content.innerHTML = summaryHTML;
      content.appendChild(cardsPanel);
      content.appendChild(table);
    }

    function closeAttendanceDetailModal() {
      const modal = document.getElementById('attendanceDetailModal');
      if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');

        // Remove backdrop
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
          backdrop.remove();
        }
      }
      console.log('Detail modal closed');
    }


    // Function to refresh attendance summary after updates
    function refreshAttendanceSummary() {
      // Reload the attendance data to get updated values
      if (state.processedResults && state.processedResults.summary) {
        // Re-render the summary table with updated data
        const summaryContainer = document.getElementById('summaryTableContainer');
        if (summaryContainer) {
          const table = createAttendanceSummaryTable(state.processedResults.summary);
          summaryContainer.innerHTML = '';
          summaryContainer.appendChild(table);
        }
        
        // Also refresh the attendance table
        const attendanceContainer = document.getElementById('attendanceTableContainer');
        if (attendanceContainer) {
          const table = createAttendanceSummaryTable(state.processedResults.summary);
          attendanceContainer.innerHTML = '';
          attendanceContainer.appendChild(table);
        }
      }
    }

    // Settings functions
    function resetSettings() {
      const form = document.getElementById('settingsForm');
      if (form) {
        form.reset();
        // Set default values
        const defaultFields = [
          ['[name="default_start_time"]', '08:00'],
          ['[name="default_end_time"]', '17:00'],
          ['[name="grace_period"]', '15'],
          ['[name="late_threshold"]', '30'],
          ['[name="working_days"]', '5'],
          ['[name="overtime_rate"]', '150']
        ];

        defaultFields.forEach(function ([selector, value]) {
          const field = form.querySelector(selector);
          if (field) field.value = value;
        });

        if (typeof toastr !== 'undefined') {
          toastr.info('Settings reset to default values', 'Settings Reset');
        }
      }
    }

    // Close modal when clicking outside
    document.addEventListener('click', function (e) {
      const modal = document.getElementById('attendanceModal');
      if (e.target === modal) {
        closeModal();
      }
    });

    // Application State (from original script)
    const state = {
      currentView: 'dashboard',
      users: [],
      attendanceData: [],
      summaryData: [],
      payrollData: [],
      processedResults: null,
      payrollApproved: false,
      payrollApprovedDate: null,
      settings: {
        startTime: '08:00',
        endTime: '16:00',
        graceMinutes: 15,
        workingDays: [1, 2, 3, 4, 5], // Mon-Fri
        workDaysCount: 21,
        absentDeduction: 5,
        lateDeduction: 1,
        currency: 'NGN'
      }
    };

    // File Processing - ENHANCED VERSION WITH COMPREHENSIVE ERROR HANDLING
    function readFile(file) {
      return new Promise(function (resolve, reject) {
        if (!file) {
          console.warn('No file provided to readFile');
          return resolve('');
        }

        const fr = new FileReader();

        fr.onload = function () {
          try {
            const content = file.name.endsWith('.xlsx') || file.name.endsWith('.xls') ? fr.result : String(fr.result || '');
            console.log(`Successfully read file: ${file.name}, size: ${typeof content === 'string' ? content.length + ' characters' : content.byteLength + ' bytes'}`);
            resolve(content);
          } catch (error) {
            console.error('Error processing file content:', error);
            reject(new Error('Failed to process file content: ' + error.message));
          }
        };

        fr.onerror = function () {
          console.error('FileReader error for file:', file.name);
          reject(new Error('Failed to read file: ' + file.name));
        };

        fr.onabort = function () {
          console.warn('File reading aborted for:', file.name);
          reject(new Error('File reading was aborted: ' + file.name));
        };

        try {
          if (file.name.endsWith('.xlsx') || file.name.endsWith('.xls')) {
            fr.readAsArrayBuffer(file);
          } else {
            fr.readAsText(file, 'UTF-8');
          }
        } catch (error) {
          console.error('Error starting file read:', error);
          reject(new Error('Failed to start reading file: ' + error.message));
        }
      });
    }

    // ENHANCED: Parse Excel payroll file with REAL DATA PARSING
    function parsePayrollExcel(arrayBuffer, users) {
      try {
        console.log('Parsing Excel payroll file with real data...');

        if (!arrayBuffer) {
          console.log('No Excel file provided, generating sample data...');
          return generateSamplePayrollData(users);
        }

        // Parse the Excel file using SheetJS
        const workbook = XLSX.read(arrayBuffer, {
          cellStyles: true,
          cellFormulas: true,
          cellDates: true,
          cellNF: true,
          sheetStubs: true
        });

        console.log('Excel workbook parsed successfully');
        console.log('Available sheets:', workbook.SheetNames);

        // Get the first sheet or look for a payroll sheet
        let sheetName = workbook.SheetNames[0];
        const payrollSheetNames = ['payroll', 'Payroll', 'PAYROLL', 'Employee', 'Employees', 'Staff'];

        for (const name of payrollSheetNames) {
          if (workbook.SheetNames.includes(name)) {
            sheetName = name;
            break;
          }
        }

        console.log('Using sheet:', sheetName);
        const worksheet = workbook.Sheets[sheetName];

        if (!worksheet) {
          throw new Error('Could not find valid worksheet in Excel file');
        }

        // Convert worksheet to JSON
        const jsonData = XLSX.utils.sheet_to_json(worksheet, {
          header: 1, // Get array of arrays
          defval: '', // Default value for empty cells
          blankrows: false // Skip blank rows
        });

        console.log('Raw Excel data rows:', jsonData.length);

        if (jsonData.length < 2) {
          throw new Error('Excel file must contain at least a header row and one data row');
        }

        // Get headers from first row
        const headers = jsonData[0].map(h => String(h || '').trim().toLowerCase());
        console.log('Excel headers:', headers);

        // Map common column variations to our standard fields
        const columnMapping = {
          // Employee identification
          userid: ['userid', 'user_id', 'id', 'employee_id', 'emp_id', 'staff_id'],
          username: ['username', 'user_name', 'name', 'employee_name', 'emp_name', 'staff_name', 'full_name'],

          // Basic info
          department: ['department', 'dept', 'division', 'unit'],
          position: ['position', 'title', 'job_title', 'designation', 'role'],

          // Salary components
          basicsalary: ['basic_salary', 'basic', 'base_salary', 'salary', 'basic_pay'],
          transportallowance: ['transport_allowance', 'transport', 'conveyance', 'travel_allowance'],
          mealallowance: ['meal_allowance', 'meal', 'lunch_allowance', 'food_allowance'],
          medicalallowance: ['medical_allowance', 'medical', 'health_allowance', 'healthcare'],
          totalallowances: ['total_allowances', 'allowances', 'total_allow'],
          grosssalary: ['gross_salary', 'gross', 'total_salary', 'gross_pay'],

          // Deductions
          taxdeduction: ['tax_deduction', 'tax', 'income_tax', 'paye', 'withholding_tax'],
          pensiondeduction: ['pension_deduction', 'pension', 'retirement', 'pension_contrib'],
          absentdeduction: ['absent_deduction', 'absent', 'absence_deduction'],
          latededuction: ['late_deduction', 'late', 'lateness_deduction'],
          totaldeductions: ['total_deductions', 'deductions', 'total_ded'],

          // Final salary
          netsalary: ['net_salary', 'net', 'take_home', 'net_pay'],

          // Attendance info
          workingdays: ['working_days', 'work_days', 'total_days'],
          presentdays: ['present_days', 'days_present', 'attendance_days'],
          absentdays: ['absent_days', 'days_absent'],
          latedays: ['late_days', 'days_late'],

          // Additional info
          bankaccount: ['bank_account', 'account_number', 'bank_no', 'account'],
          payrollmonth: ['payroll_month', 'month', 'pay_period', 'period']
        };

        // Find column indices
        const columnIndices = {};
        for (const [fieldName, variations] of Object.entries(columnMapping)) {
          for (const variation of variations) {
            const index = headers.findIndex(h => h === variation || h.includes(variation));
            if (index !== -1) {
              columnIndices[fieldName] = index;
              console.log(`Mapped ${fieldName} to column ${index} (${headers[index]})`);
              break;
            }
          }
        }

        console.log('Column mapping:', columnIndices);

        // Parse data rows
        const payrollData = [];
        for (let i = 1; i < jsonData.length; i++) {
          const row = jsonData[i];
          if (!row || row.length === 0) continue;

          try {
            // Helper function to get cell value with fallback
            const getCellValue = (fieldName, defaultValue = '') => {
              const index = columnIndices[fieldName];
              if (index !== undefined && index < row.length) {
                const value = row[index];
                return value !== null && value !== undefined ? String(value).trim() : defaultValue;
              }
              return defaultValue;
            };

            // Helper function to parse numeric values
            const parseNumeric = (fieldName, defaultValue = 0) => {
              const value = getCellValue(fieldName);
              if (!value) return defaultValue;

              // Remove currency symbols and commas
              const cleaned = String(value).replace(/[₦$,\s]/g, '');
              const parsed = parseFloat(cleaned);
              return isNaN(parsed) ? defaultValue : parsed;
            };

            // Get user ID - try to match with existing users
            let userId = getCellValue('userid');
            let username = getCellValue('username');

            // If we have users from attendance, try to match
            if (users && users.length > 0) {
              const matchedUser = users.find(u =>
                u.UserId === userId ||
                u.Username.toLowerCase() === username.toLowerCase()
              );

              if (matchedUser) {
                userId = matchedUser.UserId;
                username = matchedUser.Username;
              } else if (!userId && username) {
                // Try to find user by name
                const foundUser = users.find(u =>
                  u.Username.toLowerCase().includes(username.toLowerCase()) ||
                  username.toLowerCase().includes(u.Username.toLowerCase())
                );
                if (foundUser) {
                  userId = foundUser.UserId;
                  username = foundUser.Username;
                }
              }
            }

            // Skip row if no valid identifier
            if (!userId && !username) {
              console.warn(`Skipping row ${i + 1}: No valid user identifier`);
              continue;
            }

            // Calculate attendance-based deductions if we have attendance data
            let absentDeduction = parseNumeric('absentdeduction', 0);
            let lateDeduction = parseNumeric('latededuction', 0);
            let presentDays = parseNumeric('presentdays', 21);
            let absentDays = parseNumeric('absentdays', 0);
            let lateDays = parseNumeric('latedays', 0);

            // If we have attendance summary, use it to calculate deductions
            if (state.summaryData && state.summaryData.length > 0) {
              const userSummary = state.summaryData.find(s => s.UserId === userId);
              if (userSummary) {
                presentDays = userSummary.Total_Present;
                absentDays = userSummary.Total_Absent;
                lateDays = userSummary.Total_Late + userSummary.Total_Low_Late;

                // Calculate deductions based on attendance
                absentDeduction = absentDays * 2000; // 2000 NGN per absent day
                lateDeduction = (userSummary.Total_Late * 1000) + (userSummary.Total_Low_Late * 500);
              }
            }

            // Parse salary components
            const basicSalary = parseNumeric('basicsalary', 50000);
            const transportAllowance = parseNumeric('transportallowance', Math.floor(basicSalary * 0.15));
            const mealAllowance = parseNumeric('mealallowance', Math.floor(basicSalary * 0.1));
            const medicalAllowance = parseNumeric('medicalallowance', Math.floor(basicSalary * 0.05));

            let totalAllowances = parseNumeric('totalallowances');
            if (totalAllowances === 0) {
              totalAllowances = transportAllowance + mealAllowance + medicalAllowance;
            }

            let grossSalary = parseNumeric('grosssalary');
            if (grossSalary === 0) {
              grossSalary = basicSalary + totalAllowances;
            }

            // Parse deductions
            const taxDeduction = parseNumeric('taxdeduction', Math.floor(grossSalary * 0.075));
            const pensionDeduction = parseNumeric('pensiondeduction', Math.floor(basicSalary * 0.08));

            let totalDeductions = parseNumeric('totaldeductions');
            if (totalDeductions === 0) {
              totalDeductions = taxDeduction + pensionDeduction + absentDeduction + lateDeduction;
            }

            let netSalary = parseNumeric('netsalary');
            if (netSalary === 0) {
              netSalary = grossSalary - totalDeductions;
            }

            // Create payroll record
            const payrollRecord = {
              UserId: userId || `EMP${String(i).padStart(3, '0')}`,
              Username: username || `Employee ${i}`,
              EmployeeName: username || `Employee ${i}`,
              Department: getCellValue('department', 'General'),
              Position: getCellValue('position', 'Staff'),
              BasicSalary: basicSalary,
              TransportAllowance: transportAllowance,
              MealAllowance: mealAllowance,
              MedicalAllowance: medicalAllowance,
              TotalAllowances: totalAllowances,
              GrossSalary: grossSalary,
              TaxDeduction: taxDeduction,
              PensionDeduction: pensionDeduction,
              AbsentDeduction: absentDeduction,
              LateDeduction: lateDeduction,
              TotalDeductions: totalDeductions,
              NetSalary: netSalary,
              WorkingDays: parseNumeric('workingdays', 21),
              PresentDays: presentDays,
              AbsentDays: absentDays,
              LateDays: lateDays,
              BankAccount: getCellValue('bankaccount', '0123456789'),
              PayrollMonth: getCellValue('payrollmonth', getCurrentMonth())
            };

            payrollData.push(payrollRecord);
            console.log(`Parsed payroll record for ${username} (${userId})`);

          } catch (rowError) {
            console.warn(`Error parsing row ${i + 1}:`, rowError);
            continue;
          }
        }

        console.log(`Successfully parsed ${payrollData.length} payroll records from Excel`);

        if (payrollData.length === 0) {
          console.warn('No valid payroll records found in Excel file, generating sample data');
          return generateSamplePayrollData(users);
        }

        return payrollData;

      } catch (error) {
        console.error('Error parsing payroll Excel:', error);
        showUserNotification('Error parsing Excel file: ' + error.message + '. Using sample data.', 'warning');
        return generateSamplePayrollData(users);
      }
    }

    // Helper function to generate sample payroll data when Excel parsing fails
    function generateSamplePayrollData(users) {
      if (!users || users.length === 0) {
        console.warn('No users provided for payroll generation');
        return [];
      }

      console.log('Generating sample payroll data for', users.length, 'users');

      const payrollData = [];
      const departments = ['IT', 'HR', 'Finance', 'Operations', 'Marketing'];
      const positions = ['Developer', 'Analyst', 'Manager', 'Executive', 'Coordinator'];
      const salaryLevels = [45000, 50000, 55000, 60000, 65000, 70000];

      users.forEach(function (user, index) {
        // Get user's attendance summary if available
        const userSummary = state.summaryData.find(s => s.UserId === user.UserId) || {
          Total_Present: 20,
          Total_Absent: 1,
          Total_Late: 2,
          Total_Low_Late: 1,
          Total_EarlyLeave: 0
        };

        // Calculate basic salary based on position
        const basicSalary = salaryLevels[index % salaryLevels.length];

        // Calculate allowances
        const transportAllowance = Math.floor(basicSalary * 0.15);
        const mealAllowance = Math.floor(basicSalary * 0.1);
        const medicalAllowance = Math.floor(basicSalary * 0.05);
        const totalAllowances = transportAllowance + mealAllowance + medicalAllowance;

        // Calculate deductions
        const taxDeduction = Math.floor((basicSalary + totalAllowances) * 0.075);
        const pensionDeduction = Math.floor(basicSalary * 0.08);
        const absentDeduction = userSummary.Total_Absent * 2000;
        const lateDeduction = (userSummary.Total_Late * 1000) + (userSummary.Total_Low_Late * 500);
        const totalDeductions = taxDeduction + pensionDeduction + absentDeduction + lateDeduction;

        // Calculate net salary
        const grossSalary = basicSalary + totalAllowances;
        const netSalary = grossSalary - totalDeductions;

        payrollData.push({
          UserId: user.UserId,
          Username: user.Username,
          EmployeeName: user.Username,
          Department: departments[index % departments.length],
          Position: positions[index % positions.length],
          BasicSalary: basicSalary,
          TransportAllowance: transportAllowance,
          MealAllowance: mealAllowance,
          MedicalAllowance: medicalAllowance,
          TotalAllowances: totalAllowances,
          GrossSalary: grossSalary,
          TaxDeduction: taxDeduction,
          PensionDeduction: pensionDeduction,
          AbsentDeduction: absentDeduction,
          LateDeduction: lateDeduction,
          TotalDeductions: totalDeductions,
          NetSalary: netSalary,
          WorkingDays: 21,
          PresentDays: userSummary.Total_Present,
          AbsentDays: userSummary.Total_Absent,
          LateDays: userSummary.Total_Late + userSummary.Total_Low_Late,
          BankAccount: '0' + (1234567890 + index),
          PayrollMonth: getCurrentMonth()
        });
      });

      return payrollData;
    }

    // Helper function to get current month name
    function getCurrentMonth() {
      const months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
      ];
      const now = new Date();
      return `${months[now.getMonth()]} ${now.getFullYear()}`;
    }

    // ENHANCED: Download Payroll Template Function
    function downloadPayrollTemplate() {
      console.log('Generating payroll template...');

      try {
        // Create template data with sample rows
        const templateData = [
          // Header row
          [
            'UserId', 'Username', 'Department', 'Position',
            'BasicSalary', 'TransportAllowance', 'MealAllowance', 'MedicalAllowance',
            'TotalAllowances', 'GrossSalary', 'TaxDeduction', 'PensionDeduction',
            'AbsentDeduction', 'LateDeduction', 'TotalDeductions', 'NetSalary',
            'WorkingDays', 'PresentDays', 'AbsentDays', 'LateDays',
            'BankAccount', 'PayrollMonth'
          ],
          // Sample data rows
          [
            'EMP001', 'John Doe', 'IT', 'Developer',
            50000, 7500, 5000, 2500,
            15000, 65000, 4875, 4000,
            0, 0, 8875, 56125,
            21, 21, 0, 0,
            '1234567890', getCurrentMonth()
          ],
          [
            'EMP002', 'Jane Smith', 'HR', 'Manager',
            60000, 9000, 6000, 3000,
            18000, 78000, 5850, 4800,
            2000, 1000, 13650, 64350,
            21, 20, 1, 2,
            '2345678901', getCurrentMonth()
          ],
          [
            'EMP003', 'Mike Johnson', 'Finance', 'Analyst',
            45000, 6750, 4500, 2250,
            13500, 58500, 4387, 3600,
            4000, 1500, 13487, 45013,
            21, 19, 2, 3,
            '3456789012', getCurrentMonth()
          ]
        ];

        // Create workbook and worksheet
        const workbook = XLSX.utils.book_new();
        const worksheet = XLSX.utils.aoa_to_sheet(templateData);

        // Set column widths
        const columnWidths = [
          { wch: 10 }, // UserId
          { wch: 15 }, // Username
          { wch: 12 }, // Department
          { wch: 12 }, // Position
          { wch: 12 }, // BasicSalary
          { wch: 15 }, // TransportAllowance
          { wch: 12 }, // MealAllowance
          { wch: 14 }, // MedicalAllowance
          { wch: 14 }, // TotalAllowances
          { wch: 12 }, // GrossSalary
          { wch: 12 }, // TaxDeduction
          { wch: 15 }, // PensionDeduction
          { wch: 14 }, // AbsentDeduction
          { wch: 12 }, // LateDeduction
          { wch: 14 }, // TotalDeductions
          { wch: 12 }, // NetSalary
          { wch: 12 }, // WorkingDays
          { wch: 12 }, // PresentDays
          { wch: 11 }, // AbsentDays
          { wch: 10 }, // LateDays
          { wch: 14 }, // BankAccount
          { wch: 15 }  // PayrollMonth
        ];
        worksheet['!cols'] = columnWidths;

        // Style the header row
        const headerStyle = {
          font: { bold: true, color: { rgb: "FFFFFF" } },
          fill: { fgColor: { rgb: "4285F4" } },
          alignment: { horizontal: "center", vertical: "center" }
        };

        // Apply header styling
        const headerRange = XLSX.utils.decode_range(worksheet['!ref']);
        for (let col = headerRange.s.c; col <= headerRange.e.c; col++) {
          const cellAddress = XLSX.utils.encode_cell({ r: 0, c: col });
          if (!worksheet[cellAddress]) continue;
          worksheet[cellAddress].s = headerStyle;
        }

        // Add data validation and formatting
        const numberStyle = {
          numFmt: "#,##0.00",
          alignment: { horizontal: "right" }
        };

        // Apply number formatting to salary columns
        const salaryColumns = [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15]; // BasicSalary to NetSalary
        for (let row = 1; row <= 3; row++) { // Sample data rows
          for (const col of salaryColumns) {
            const cellAddress = XLSX.utils.encode_cell({ r: row, c: col });
            if (worksheet[cellAddress]) {
              worksheet[cellAddress].s = numberStyle;
            }
          }
        }

        // Add the worksheet to workbook
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Payroll Template');

        // Create instructions sheet
        const instructionsData = [
          ['PAYROLL TEMPLATE INSTRUCTIONS'],
          [''],
          ['Column Descriptions:'],
          ['UserId', 'Unique employee identifier (required)'],
          ['Username', 'Employee full name (required)'],
          ['Department', 'Employee department'],
          ['Position', 'Job title/position'],
          ['BasicSalary', 'Base monthly salary (required)'],
          ['TransportAllowance', 'Transportation allowance'],
          ['MealAllowance', 'Meal/lunch allowance'],
          ['MedicalAllowance', 'Medical/health allowance'],
          ['TotalAllowances', 'Sum of all allowances (auto-calculated if empty)'],
          ['GrossSalary', 'Basic + Allowances (auto-calculated if empty)'],
          ['TaxDeduction', 'Income tax deduction (auto-calculated if empty)'],
          ['PensionDeduction', 'Pension contribution (auto-calculated if empty)'],
          ['AbsentDeduction', 'Deduction for absent days (auto-calculated from attendance)'],
          ['LateDeduction', 'Deduction for late arrivals (auto-calculated from attendance)'],
          ['TotalDeductions', 'Sum of all deductions (auto-calculated if empty)'],
          ['NetSalary', 'Gross - Deductions (auto-calculated if empty)'],
          ['WorkingDays', 'Total working days in month'],
          ['PresentDays', 'Days employee was present (from attendance data)'],
          ['AbsentDays', 'Days employee was absent (from attendance data)'],
          ['LateDays', 'Days employee was late (from attendance data)'],
          ['BankAccount', 'Employee bank account number'],
          ['PayrollMonth', 'Month and year for this payroll'],
          [''],
          ['Notes:'],
          ['- Required fields: UserId, Username, BasicSalary'],
          ['- Attendance-based fields will be auto-calculated if attendance data is processed'],
          ['- Financial calculations use default rates if not specified'],
          ['- Delete the sample rows and add your actual employee data'],
          ['- Ensure UserId matches your attendance system user IDs'],
          ['- Save as .xlsx format when uploading']
        ];

        const instructionsWorksheet = XLSX.utils.aoa_to_sheet(instructionsData);

        // Style the instructions
        instructionsWorksheet['!cols'] = [{ wch: 20 }, { wch: 50 }];

        // Style the title
        if (instructionsWorksheet['A1']) {
          instructionsWorksheet['A1'].s = {
            font: { bold: true, size: 16, color: { rgb: "4285F4" } },
            alignment: { horizontal: "center" }
          };
        }

        XLSX.utils.book_append_sheet(workbook, instructionsWorksheet, 'Instructions');

        // Generate file name with current date
        const currentDate = new Date();
        const dateStr = currentDate.toISOString().slice(0, 10);
        const fileName = `Payroll_Template_${dateStr}.xlsx`;

        // Write and download the file
        XLSX.writeFile(workbook, fileName);

        console.log('Payroll template downloaded:', fileName);
        showUserNotification('Payroll template downloaded successfully!', 'success');

      } catch (error) {
        console.error('Error generating payroll template:', error);
        showUserNotification('Error generating template: ' + error.message, 'error');
      }
    }

    // Parse user.dat (robust to control chars / variable spacing) - ENHANCED WITH ERROR HANDLING
    function parseUsers(text) {
      try {
        if (!text || typeof text !== 'string') {
          console.warn('Invalid text input for parseUsers:', typeof text);
          return [];
        }

        console.log('Parsing users from text, length:', text.length);

        // Replace control chars with spaces, then split
        const cleaned = text.replace(/[\x00-\x1F]/g, ' ').replace(/\s+/g, ' ').trim();
        const tokens = cleaned.split(' ');

        console.log('Cleaned tokens count:', tokens.length);

        const nameIdPairs = [];
        // Strategy: find sequences where a name token (letters only) is followed later by a numeric token; pair in order.
        // Since original file appears as Name … ID … Name … ID, we zip by alternating name and id appearances.
        const names = [], ids = [];

        for (const t of tokens) {
          if (!t) continue; // Skip empty tokens

          if (/^[A-Za-z]+$/.test(t)) {
            names.push(t);
          } else if (/^\d+$/.test(t)) {
            ids.push(t);
          }
        }

        console.log('Found names:', names.length, 'Found IDs:', ids.length);

        const n = Math.min(names.length, ids.length);
        for (let i = 0; i < n; i++) {
          nameIdPairs.push({ UserId: ids[i], Username: names[i] });
        }

        // Deduplicate by UserId, keep first username encountered
        const seen = new Map();
        for (const r of nameIdPairs) {
          if (!seen.has(r.UserId)) seen.set(r.UserId, r.Username);
        }

        const result = Array.from(seen, function ([UserId, Username]) {
          return { UserId: UserId, Username: Username };
        });

        console.log('Successfully parsed users:', result.length);
        return result;

      } catch (error) {
        console.error('Error parsing users:', error);
        return [];
      }
    }

    // ENHANCED: Parse attendance logs with robust timestamp handling - ENHANCED WITH ERROR HANDLING
    function parseAttendance(text) {
      try {
        if (!text || typeof text !== 'string') {
          console.warn('Invalid text input for parseAttendance:', typeof text);
          return [];
        }

        console.log('Parsing attendance from text, length:', text.length);

        const rows = [];
        const lines = text.split(/\r?\n/);

        console.log('Processing', lines.length, 'lines');

        let processedLines = 0;
        let errorLines = 0;

        for (const line of lines) {
          const trimmed = line.trim();
          if (!trimmed) continue;

          try {
            // Handle malformed concatenated timestamps like "2025-08-01 07:26:012025-08-01 17:03:23"
            let cleanedLine = trimmed;

            // Split concatenated timestamps (look for date patterns)
            const datePattern = /(\d{4}-\d{2}-\d{2})/g;
            const dateMatches = [...trimmed.matchAll(datePattern)];
            if (dateMatches.length > 2) {
              // Multiple dates found, likely concatenated - split them
              const entries = [];
              const parts = trimmed.split(/(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})/);
              for (let i = 1; i < parts.length; i += 2) {
                if (parts[i] && parts[i - 1]) {
                  entries.push(parts[i - 1].trim() + ' ' + parts[i]);
                }
              }
              for (const entry of entries) {
                parseTimestampEntry(entry, rows);
              }
            } else {
              parseTimestampEntry(trimmed, rows);
            }
            processedLines++;
          } catch (error) {
            console.warn('Error processing line:', trimmed, error);
            errorLines++;
          }
        }

        console.log('Attendance parsing complete. Processed:', processedLines, 'Error lines:', errorLines, 'Total records:', rows.length);
        return rows;

      } catch (error) {
        console.error('Error parsing attendance:', error);
        return [];
      }
    }

    // ENHANCED: Helper function to parse individual timestamp entries with timezone fix - ENHANCED WITH ERROR HANDLING
    function parseTimestampEntry(entry, rows) {
      try {
        if (!entry || typeof entry !== 'string') {
          console.warn('Invalid entry for parseTimestampEntry:', typeof entry);
          return;
        }

        // Extract timestamp patterns more robustly
        const parts = entry.split(/\s+/);
        if (parts.length < 2) {
          console.warn('Entry has insufficient parts:', entry);
          return;
        }

        const uid = parts[0];
        if (!/^\d+$/.test(uid)) {
          console.warn('Invalid user ID format:', uid, 'in entry:', entry);
          return;
        }

        // Look for timestamp pattern: YYYY-MM-DD HH:MM:SS or similar
        const timestampMatch = entry.match(/(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2}:\d{2})/);
        if (!timestampMatch) {
          // Try alternative formats
          const altMatch = entry.match(/(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2}:\d{2})/);
          if (altMatch) {
            // Force local time interpretation
            const dateTimeStr = altMatch[1] + ' ' + altMatch[2];
            const d = createLocalDate(dateTimeStr);
            if (!isNaN(d.getTime())) {
              rows.push({ UserId: String(uid), Timestamp: d });
            } else {
              console.warn('Invalid date created from alt format:', dateTimeStr);
            }
          } else {
            console.warn('No valid timestamp pattern found in entry:', entry);
          }
          return;
        }

        const dateStr = timestampMatch[1];
        const timeStr = timestampMatch[2];
        // Force local time interpretation to prevent timezone shifts
        const dateTimeStr = dateStr + ' ' + timeStr;
        const d = createLocalDate(dateTimeStr);

        if (!isNaN(d.getTime())) {
          rows.push({ UserId: String(uid), Timestamp: d });
        } else {
          console.warn('Invalid date created from timestamp:', dateTimeStr);
        }

      } catch (error) {
        console.error('Error parsing timestamp entry:', entry, error);
      }
    }

    // Create date in local timezone to prevent UTC conversion issues
    function createLocalDate(dateTimeString) {
      try {
        if (!dateTimeString || typeof dateTimeString !== 'string') {
          console.warn('Invalid dateTimeString for createLocalDate:', typeof dateTimeString);
          return new Date(); // Return current date as fallback
        }

        // Parse manually to avoid timezone conversion
        const match = dateTimeString.match(/(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2}):(\d{2})/);
        if (!match) {
          console.warn('No valid date pattern found in:', dateTimeString);
          return new Date(dateTimeString); // fallback
        }

        const [, year, month, day, hour, minute, second] = match;

        // Validate date components
        const yearNum = parseInt(year);
        const monthNum = parseInt(month) - 1; // Month is 0-indexed in JS
        const dayNum = parseInt(day);
        const hourNum = parseInt(hour);
        const minuteNum = parseInt(minute);
        const secondNum = parseInt(second);

        // Basic validation
        if (yearNum < 1900 || yearNum > 2100) {
          console.warn('Invalid year:', yearNum);
          return new Date(dateTimeString);
        }

        if (monthNum < 0 || monthNum > 11) {
          console.warn('Invalid month:', monthNum);
          return new Date(dateTimeString);
        }

        if (dayNum < 1 || dayNum > 31) {
          console.warn('Invalid day:', dayNum);
          return new Date(dateTimeString);
        }

        if (hourNum < 0 || hourNum > 23) {
          console.warn('Invalid hour:', hourNum);
          return new Date(dateTimeString);
        }

        if (minuteNum < 0 || minuteNum > 59) {
          console.warn('Invalid minute:', minuteNum);
          return new Date(dateTimeString);
        }

        if (secondNum < 0 || secondNum > 59) {
          console.warn('Invalid second:', secondNum);
          return new Date(dateTimeString);
        }

        // Use local timezone by passing individual components
        const date = new Date(yearNum, monthNum, dayNum, hourNum, minuteNum, secondNum);

        // Validate the created date
        if (isNaN(date.getTime())) {
          console.warn('Created invalid date from components:', dateTimeString);
          return new Date(dateTimeString);
        }

        return date;

      } catch (error) {
        console.error('Error creating local date from:', dateTimeString, error);
        return new Date(dateTimeString); // fallback
      }
    }

    // Compute min/max per user per date - ORIGINAL
    function computeDailyAttendance(att, users) {
      // Union of userIds from both sources
      const userMap = new Map(users.map(function (u) { return [u.UserId, u.Username]; }));
      for (const r of att) {
        if (!userMap.has(r.UserId)) userMap.set(r.UserId, '');
      }
      const minDate = att.reduce(function (m, r) { return r.Timestamp < m ? r.Timestamp : m; }, att[0] && att[0].Timestamp || new Date());

      // Group by userId+date
      const key = function (uid, d) { return uid + '|' + d.toISOString().slice(0, 10); };
      const map = new Map();
      for (const r of att) {
        const day = new Date(r.Timestamp);
        day.setHours(0, 0, 0, 0);
        const k = key(r.UserId, day);
        let o = map.get(k);
        if (!o) o = { UserId: r.UserId, Date: new Date(day), Clockin: r.Timestamp, Clockout: r.Timestamp };
        if (r.Timestamp < o.Clockin) o.Clockin = r.Timestamp;
        if (r.Timestamp > o.Clockout) o.Clockout = r.Timestamp;
        map.set(k, o);
      }
      return { attendanceByDay: map, minDate };
    }

    // Build N business days from start date - ORIGINAL
    function buildBusinessCalendar(startDate, n) {
      const days = [];
      const d = new Date(startDate);
      d.setHours(0, 0, 0, 0);
      while (days.length < n) {
        const wd = d.getDay(); // 0 Sun .. 6 Sat
        if (state.settings.workingDays.includes(wd)) {
          days.push(new Date(d));
        }
        d.setDate(d.getDate() + 1);
      }
      return days;
    }

    // ENHANCED: Build attendance outputs with accurate time comparison - ORIGINAL
    // Extract time calculation logic into separate function
    function calculateTimeThresholds(startTime, graceMin, endTime) {
      const [sh, sm] = startTime.split(':').map(function (x) { return parseInt(x || '0', 10); });
      const [eh, em] = endTime.split(':').map(function (x) { return parseInt(x || '0', 10); });

      return {
        startTimeMinutes: sh * 60 + sm,
        graceTimeMinutes: (sh * 60 + sm) + graceMin,
        lateTimeMinutes: (sh * 60 + sm) + graceMin + 5,
        endTimeMinutes: eh * 60 + em
      };
    }

    // UTILITY FUNCTIONS - Common operations used throughout the application
    
    // Extract utility function for time calculations
    function getMinutesSinceMidnight(date) {
      return date.getHours() * 60 + date.getMinutes() + (date.getSeconds() / 60);
    }

    // Utility function for date formatting
    function formatDate(date, format = 'ISO') {
      if (format === 'ISO') {
        return date.toISOString().slice(0, 10);
      } else if (format === 'GB') {
        return date.toLocaleDateString('en-GB');
      } else if (format === 'US') {
        return date.toLocaleDateString('en-US');
      }
      return date.toISOString().slice(0, 10);
    }

    // Utility function for time formatting
    function formatTime(date, includeSeconds = false) {
      const hours = String(date.getHours()).padStart(2, '0');
      const minutes = String(date.getMinutes()).padStart(2, '0');
      const seconds = String(date.getSeconds()).padStart(2, '0');
      
      if (includeSeconds) {
        return `${hours}:${minutes}:${seconds}`;
      }
      return `${hours}:${minutes}`;
    }

    // Utility function for calculating hours between two dates
    function calculateHoursBetween(startDate, endDate) {
      return (endDate - startDate) / 36e5; // 36e5 = 3600000 milliseconds in an hour
    }

    // Utility function for creating user notification
    function showUserNotification(message, type = 'info') {
      const notification = document.createElement('div');
      notification.className = `alert alert-${type} alert-dismissible fade show`;
      notification.style.position = 'fixed';
      notification.style.top = '20px';
      notification.style.right = '20px';
      notification.style.zIndex = '9999';
      notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;
      
      document.body.appendChild(notification);
      
      // Auto-remove after 5 seconds
      setTimeout(() => {
        if (notification.parentNode) {
          notification.parentNode.removeChild(notification);
        }
      }, 5000);
    }

    // Utility function for element detection with fallback
    function getElementById(id, fallbackMessage = null) {
      const element = document.getElementById(id);
      if (!element && fallbackMessage) {
        console.warn(fallbackMessage);
      }
      return element;
    }

    // Utility function for clearing element content
    function clearElementContent(elementId) {
      const element = document.getElementById(elementId);
      if (element) {
        element.innerHTML = '';
      }
    }

    // Utility function for creating loading spinner
    function createLoadingSpinner(text = 'Loading...') {
      const spinner = document.createElement('div');
      spinner.className = 'text-center p-4';
      spinner.innerHTML = `
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <div class="mt-2">${text}</div>
      `;
      return spinner;
    }

    // PERFORMANCE OPTIMIZATION - Element caching
    const elementCache = new Map();
    
    function getCachedElement(id) {
      if (!elementCache.has(id)) {
        elementCache.set(id, document.getElementById(id));
      }
      return elementCache.get(id);
    }

    function clearElementCache() {
      elementCache.clear();
    }

    // PERFORMANCE OPTIMIZATION - Debounced functions
    function debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    }

    // PERFORMANCE OPTIMIZATION - Throttled functions
    function throttle(func, limit) {
      let inThrottle;
      return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
          func.apply(context, args);
          inThrottle = true;
          setTimeout(() => inThrottle = false, limit);
        }
      };
    }

    // Extract summary building logic
    function buildSummaryFromDetail(detail) {
      const byUser = new Map();
      for (const r of detail) {
        const k = r.UserId + '|' + r.Username;
        let o = byUser.get(k);
        if (!o) o = {
          UserId: r.UserId,
          Username: r.Username,
          Total_Present: 0,
          Total_Absent: 0,
          Total_Late: 0,
          Total_Low_Late: 0,
          Total_EarlyLeave: 0
        };

        if (r.Status === 'Present') {
          o.Total_Present++;
          if (r.Late === 'Late') o.Total_Late++;
          if (r.Late === 'Low_Late') o.Total_Low_Late++;
          if (r.EarlyLeave === 'Yes') o.Total_EarlyLeave++;
        } else if (r.Status === 'Absent') {
          o.Total_Absent++;
        }
        byUser.set(k, o);
      }

      return Array.from(byUser.values()).sort(function (a, b) { 
        return a.UserId.localeCompare(b.UserId, undefined, { numeric: true }); 
      });
    }

    // Extract detail record creation logic
    function createDetailRecord(user, attendanceRecord, day, endTimeMinutes, currentDetailLength) {
      if (attendanceRecord) {
        const clockin = attendanceRecord.Clockin;
        const clockout = attendanceRecord.Clockout;
        const clockinMinutes = getMinutesSinceMidnight(clockin);
        const clockoutMinutes = getMinutesSinceMidnight(clockout);

        // Determine lateness status - Using unified late logic
        const lateStatus = calculateLateStatus(clockin.toISOString().replace('T', ' ').slice(0, 19));
        let lateCategory = '';
        
        // Set category based on late status
        if (lateStatus === ATTENDANCE_CONFIG.STATUS.LOW_LATE) {
          lateCategory = 'Minor';
        } else if (lateStatus === ATTENDANCE_CONFIG.STATUS.LATE) {
          lateCategory = 'Major';
        }

        // DEBUG: Log individual clockin times for first few records
        if (currentDetailLength < 5) {
          const timeStr = String(clockin.getHours()).padStart(2, '0') + ':' + String(clockin.getMinutes()).padStart(2, '0');
          console.log(`User ${user.UserId} clockin: ${timeStr} (${clockinMinutes.toFixed(1)} min) - Late Status: ${lateStatus}`);
        }

        const earlyLeave = clockoutMinutes < endTimeMinutes;
        const hours = calculateHoursBetween(clockin, clockout);

        return {
          UserId: user.UserId,
          Username: user.Username || '',
          Date: formatDate(day),
          WorkingDay: 'Yes',
          Clockin: clockin.toISOString().replace('T', ' ').slice(0, 19),
          Clockout: clockout.toISOString().replace('T', ' ').slice(0, 19),
          Late: lateStatus,
          LateCategory: lateCategory,
          EarlyLeave: earlyLeave ? 'Yes' : 'No',
          TotalHours: hours.toFixed(2),
          Status: 'Present'
        };
      } else {
        return {
          UserId: user.UserId,
          Username: user.Username || '',
          Date: formatDate(day),
          WorkingDay: 'Yes',
          Clockin: '',
          Clockout: '',
          Late: '',
          LateCategory: '',
          EarlyLeave: '',
          TotalHours: '',
          Status: 'Absent'
        };
      }
    }

    function buildOutputs(users, attMap, calendar, startTime, graceMin, endTime) {
      // Calculate time thresholds
      const thresholds = calculateTimeThresholds(startTime, graceMin, endTime);
      const { startTimeMinutes, graceTimeMinutes, lateTimeMinutes, endTimeMinutes } = thresholds;

      // DEBUG: Log the thresholds
      console.log(`Time thresholds: Start=${startTimeMinutes}min (${startTime}), Grace=${graceTimeMinutes}min, Late=${lateTimeMinutes}min, End=${endTimeMinutes}min`);

      const usersSorted = [...users].sort(function (a, b) { return a.UserId.localeCompare(b.UserId, undefined, { numeric: true }); });
      const detail = [];

      for (const u of usersSorted) {
        for (const day of calendar) {
          const key = u.UserId + '|' + day.toISOString().slice(0, 10);
          const rec = attMap.get(key);
          const detailRecord = createDetailRecord(u, rec, day, endTimeMinutes, detail.length);
          detail.push(detailRecord);
        }
      }

      // Build summary from detail records
      const summary = buildSummaryFromDetail(detail);
      return { detail, summary };
    }

    // Display functions - Attendance tab shows SUMMARY with View Details buttons
    function displayAttendanceData(detailData, summaryData) {
      console.log('Displaying attendance data:', summaryData ? summaryData.length : 0, 'summary records');

      // Attendance tab shows summary data with "View Details" buttons
      const attendanceContainer = getCachedElement('attendanceTableContainer');
      if (attendanceContainer && summaryData && summaryData.length > 0) {
        const table = createAttendanceSummaryTable(summaryData); // Changed to summary
        attendanceContainer.innerHTML = '';
        attendanceContainer.appendChild(table);

        // Update tab to show data is available
        updateTabIndicator('attendance', summaryData.length);
      }

      // Summary tab shows statistics/analytics
      const summaryContainer = document.getElementById('summaryTableContainer');
      if (summaryContainer && summaryData && summaryData.length > 0) {
        const statsView = createSummaryStatsView(summaryData);
        summaryContainer.innerHTML = '';
        summaryContainer.appendChild(statsView);

        // Update tab to show data is available
        updateTabIndicator('summary', summaryData.length);
      }
    }

    // UNIFIED TABLE FACTORY - Single function for all table creation
    function createTable(data, columns, options = {}) {
      const {
        className = 'table table-striped table-hover',
        containerClass = 'table-responsive',
        showActions = false,
        actionButton = null,
        onRowClick = null,
        colorCoding = null,
        maxHeight = null
      } = options;

      const container = document.createElement('div');
      container.className = containerClass;
      if (maxHeight) {
        container.style.maxHeight = maxHeight;
        container.style.overflowY = 'auto';
      }

      const table = document.createElement('table');
      table.className = className;

      // Create header
      const thead = document.createElement('thead');
      thead.className = 'table-dark';
      const headerRow = document.createElement('tr');

      columns.forEach(function (column) {
        const th = document.createElement('th');
        th.textContent = column;
        th.style.padding = '12px';
        th.style.fontSize = '14px';
        th.style.fontWeight = 'bold';
        headerRow.appendChild(th);
      });

      if (showActions && actionButton) {
        const actionTh = document.createElement('th');
        actionTh.textContent = 'Actions';
        actionTh.style.padding = '12px';
        actionTh.style.fontSize = '14px';
        actionTh.style.fontWeight = 'bold';
        headerRow.appendChild(actionTh);
      }

      thead.appendChild(headerRow);
      table.appendChild(thead);

      // Create body
      const tbody = document.createElement('tbody');

      data.forEach(function (row, index) {
        const tr = document.createElement('tr');
        if (index % 2 === 0) tr.className = 'table-row-even';
        if (onRowClick) {
          tr.style.cursor = 'pointer';
        }

        // Add data cells
        columns.forEach(function (column, cellIndex) {
          const td = document.createElement('td');
          const cellData = typeof row === 'object' ? row[column] : row[cellIndex];
          td.textContent = cellData;
          td.style.padding = '12px';
          td.style.fontSize = '14px';

          // Apply color coding if specified
          if (colorCoding && colorCoding(cellData, cellIndex, row)) {
            const colors = colorCoding(cellData, cellIndex, row);
            td.style.color = colors.color;
            td.style.fontWeight = colors.fontWeight || 'normal';
          }

          tr.appendChild(td);
        });

        // Add action button if specified
        if (showActions && actionButton) {
          const actionCell = document.createElement('td');
          actionCell.style.padding = '12px';
          const btn = actionButton(row, index);
          actionCell.appendChild(btn);
          tr.appendChild(actionCell);
        }

        // Add row click handler
        if (onRowClick) {
          tr.addEventListener('click', function (e) {
            if (!e.target.closest('button')) {
              onRowClick(row, index);
            }
          });
        }

        tbody.appendChild(tr);
      });

      table.appendChild(tbody);
      container.appendChild(table);

      return container;
    }

    // Create attendance summary table with "View Details" buttons
    function createAttendanceSummaryTable(summaryData) {
      console.log('Creating attendance summary table with', summaryData.length, 'records');

      const columns = [
        'UserId', 'Username', 'Total_Present', 'Total_Absent', 
        'Total_Late', 'Total_Low_Late', 'Total_EarlyLeave'
      ];

      const colorCoding = function (cellData, cellIndex, row) {
        if (cellData > 0) {
          switch (cellIndex) {
            case 2: // Present
              return { color: '#28a745', fontWeight: 'bold' };
            case 3: // Absent
              return { color: '#dc3545', fontWeight: 'bold' };
            case 4: // Late
              return { color: '#dc3545', fontWeight: 'bold' };
            case 5: // Low Late
              return { color: '#ffc107', fontWeight: 'bold' };
            default:
              return null;
          }
        }
        return null;
      };

      const actionButton = function (row, index) {
        const viewBtn = document.createElement('button');
        viewBtn.className = 'btn btn-sm btn-primary view-detail-btn';
        viewBtn.style.fontSize = '12px';
        viewBtn.style.padding = '8px 16px';
        viewBtn.style.backgroundColor = '#007bff';
        viewBtn.style.borderColor = '#007bff';
        viewBtn.style.color = '#ffffff';
        viewBtn.style.borderRadius = '4px';
        viewBtn.style.border = 'none';
        viewBtn.style.cursor = 'pointer';
        viewBtn.style.transition = 'all 0.2s ease';
        viewBtn.innerHTML = '<i class="fas fa-eye" style="margin-right: 4px;"></i>View Details';

        // Add hover effect
        viewBtn.addEventListener('mouseenter', function () {
          this.style.backgroundColor = '#0056b3';
          this.style.borderColor = '#0056b3';
          this.style.transform = 'translateY(-1px)';
        });

        viewBtn.addEventListener('mouseleave', function () {
          this.style.backgroundColor = '#007bff';
          this.style.borderColor = '#007bff';
          this.style.transform = 'translateY(0)';
        });

        viewBtn.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          console.log('View Details clicked for user:', row.UserId, row.Username);
          showAttendanceDetailModal(row.UserId, row.Username);
        });

        return viewBtn;
      };

      return createTable(summaryData, columns, {
        showActions: true,
        actionButton: actionButton,
        colorCoding: colorCoding
      });
    }

    // Create detailed attendance table for modal
    function createDetailedAttendanceTable(detailData) {
      const columns = [
        'Date', 'Day', 'Clock In', 'Clock Out', 'Late Status', 'Early Leave', 'Total Hours', 'Status'
      ];

      const colorCoding = function (cellData, cellIndex, row) {
        if (cellIndex === 7) { // Status column
          if (cellData === 'Present') {
            return { 
              color: '#28a745', 
              fontWeight: '600',
              backgroundColor: '#d4edda'
            };
          } else if (cellData === 'Absent') {
            return { 
              color: '#dc3545', 
              fontWeight: '600',
              backgroundColor: '#f8d7da'
            };
          }
        } else if (cellIndex === 4) { // Late status column
          if (cellData === 'Late') {
            return { 
              color: '#dc3545', 
              fontWeight: '600',
              backgroundColor: '#f8d7da'
            };
          } else if (cellData === 'Low_Late') {
            return { 
              color: '#856404', 
              fontWeight: '600',
              backgroundColor: '#fff3cd'
            };
          } else if (cellData === 'N/A') {
            return { 
              color: '#6c757d', 
              fontStyle: 'italic'
            };
          }
        }
        return null;
      };

      // Transform data to include formatted date and day
      const transformedData = detailData.map(row => {
        const dateObj = new Date(row.Date);
        return {
          ...row,
          Date: dateObj.toLocaleDateString('en-GB'),
          Day: dateObj.toLocaleDateString('en-US', { weekday: 'short' }),
          'Clock In': row.Clockin || 'N/A',
          'Clock Out': row.Clockout || 'N/A',
          'Late Status': row.Late || 'N/A',
          'Early Leave': row.EarlyLeave || 'N/A',
          'Total Hours': row.TotalHours || 'N/A'
        };
      });

      return createTable(transformedData, columns, {
        maxHeight: '400px',
        colorCoding: colorCoding,
        className: 'table table-striped table-sm table-hover'
      });
    }

    // Create summary statistics view
    function createSummaryStatsView(summaryData) {
      const container = document.createElement('div');
      container.className = 'row';

      // Calculate overall statistics
      const totalUsers = summaryData.length;
      const totalPresentDays = summaryData.reduce(function (sum, user) { return sum + user.Total_Present; }, 0);
      const totalAbsentDays = summaryData.reduce(function (sum, user) { return sum + user.Total_Absent; }, 0);
      const totalLateDays = summaryData.reduce(function (sum, user) { return sum + user.Total_Late; }, 0);
      const totalLowLateDays = summaryData.reduce(function (sum, user) { return sum + user.Total_Low_Late; }, 0);

      const totalDays = totalPresentDays + totalAbsentDays;
      const attendanceRate = totalDays > 0 ? ((totalPresentDays / totalDays) * 100).toFixed(1) : '0';

      // Create stat cards
      const stats = [
        { title: 'Total Users', value: totalUsers, color: 'primary' },
        { title: 'Total Present Days', value: totalPresentDays, color: 'success' },
        { title: 'Total Absent Days', value: totalAbsentDays, color: 'danger' },
        { title: 'Total Late Days', value: totalLateDays, color: 'warning' },
        { title: 'Overall Attendance Rate', value: attendanceRate + '%', color: 'info' }
      ];

      stats.forEach(function (stat) {
        const col = document.createElement('div');
        col.className = 'col-md-4 col-sm-6 mb-3';

        const card = document.createElement('div');
        card.className = 'card border-' + stat.color;
        card.innerHTML = `
                <div class="card-body text-center">
                    <h5 class="card-title text-${stat.color}">${stat.title}</h5>
                    <h2 class="card-text">${stat.value}</h2>
                </div>
            `;

        col.appendChild(card);
        container.appendChild(col);
      });

      // Add detailed table below stats
      const tableCol = document.createElement('div');
      tableCol.className = 'col-12 mt-4';

      const tableTitle = document.createElement('h5');
      tableTitle.textContent = 'User Summary Statistics';
      tableTitle.className = 'mb-3';
      tableCol.appendChild(tableTitle);

      const detailedTable = createSummaryDetailsTable(summaryData);
      tableCol.appendChild(detailedTable);

      container.appendChild(tableCol);

      return container;
    }

    function createSummaryDetailsTable(summaryData) {
      const table = document.createElement('table');
      table.className = 'table table-striped table-hover';

      // Create header
      const thead = document.createElement('thead');
      thead.className = 'table-dark';
      const headerRow = document.createElement('tr');

      const headers = [
        'User ID', 'Username', 'Present Days', 'Absent Days', 'Late Days',
        'Low Late Days', 'Early Leave Days', 'Attendance %'
      ];

      headers.forEach(function (header) {
        const th = document.createElement('th');
        th.textContent = header;
        th.style.padding = '12px';
        th.style.fontSize = '14px';
        headerRow.appendChild(th);
      });

      thead.appendChild(headerRow);
      table.appendChild(thead);

      // Create body
      const tbody = document.createElement('tbody');

      summaryData.forEach(function (row) {
        const tr = document.createElement('tr');

        const totalDays = row.Total_Present + row.Total_Absent;
        const attendancePercent = totalDays > 0 ? ((row.Total_Present / totalDays) * 100).toFixed(1) + '%' : 'N/A';

        const cells = [
          row.UserId,
          row.Username,
          row.Total_Present,
          row.Total_Absent,
          row.Total_Late,
          row.Total_Low_Late,
          row.Total_EarlyLeave,
          attendancePercent
        ];

        cells.forEach(function (cellData, cellIndex) {
          const td = document.createElement('td');
          td.textContent = cellData;
          td.style.padding = '12px';
          td.style.fontSize = '14px';

          // Color coding
          if (cellIndex === 2 && cellData > 0) { // Present
            td.style.color = '#28a745';
            td.style.fontWeight = 'bold';
          } else if (cellIndex === 3 && cellData > 0) { // Absent
            td.style.color = '#dc3545';
            td.style.fontWeight = 'bold';
          } else if (cellIndex === 4 && cellData > 0) { // Late
            td.style.color = '#dc3545';
            td.style.fontWeight = 'bold';
          } else if (cellIndex === 5 && cellData > 0) { // Low Late
            td.style.color = '#ffc107';
            td.style.fontWeight = 'bold';
          } else if (cellIndex === 7) { // Attendance %
            const percent = parseFloat(cellData);
            if (percent >= 90) {
              td.style.color = '#28a745';
            } else if (percent >= 75) {
              td.style.color = '#ffc107';
            } else if (percent < 75) {
              td.style.color = '#dc3545';
            }
            td.style.fontWeight = 'bold';
          }

          tr.appendChild(td);
        });

        tbody.appendChild(tr);
      });

      table.appendChild(tbody);

      const wrapper = document.createElement('div');
      wrapper.className = 'table-responsive';
      wrapper.appendChild(table);

      return wrapper;
    }

    // ENHANCED: Display payroll data with real Excel parsing
    function displayPayrollData(payrollData) {
      console.log('Displaying payroll data:', payrollData.length, 'records');

      const payrollContainer = document.getElementById('payrollTableContainer');
      if (payrollContainer && payrollData && payrollData.length > 0) {
        const table = createPayrollTable(payrollData);
        payrollContainer.innerHTML = '';
        payrollContainer.appendChild(table);

        // Update tab to show data is available
        updateTabIndicator('payroll', payrollData.length);
      }
    }

    // ENHANCED: Create payroll table with real data parsing
    function createPayrollTable(payrollData) {
      console.log('Creating payroll table with new buttons...', payrollData.length, 'employees');

      const container = document.createElement('div');
      container.className = 'table-responsive';

      // Add payroll summary header with data source indicator and action buttons
      const header = document.createElement('div');
      header.className = 'mb-4';
      const dataSource = state.payrollDataSource || 'Excel File';
      const currentMonth = payrollData.length > 0 ? payrollData[0].PayrollMonth : getCurrentMonth();

      // Get overall approval status from payroll data
      const approvalStatuses = payrollData.map(emp => emp.approval_status || 'draft');
      const uniqueStatuses = [...new Set(approvalStatuses)];
      const overallStatus = getOverallApprovalStatus(uniqueStatuses);
      const statusBadge = createOverallStatusBadge(overallStatus);
      
      // Define approval and submission states
      const isApproved = state.payrollApproved || false;
      const isSubmittedForReview = state.payrollSubmittedForReview || false;
      const userPermissions = window.userPermissions || {};
      const canSubmitForReview = userPermissions['payroll.submit_for_review'] && !isSubmittedForReview && !isApproved;
      const canApprove = userPermissions['payroll.approve'] && isSubmittedForReview && !isApproved;

      header.innerHTML = `
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="text-primary mb-1">PAYROLL SUMMARY - ${currentMonth} ${statusBadge}</h5>
                    <p class="text-muted mb-0">Data source: ${dataSource} | ${payrollData.length} employees</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-success btn-sm" onclick="downloadPayrollTemplate()">
                        <i class="fas fa-download"></i> Download Template
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="exportPayroll()" ${isApproved ? '' : 'disabled'}>
                        <i class="fas fa-file-export"></i> Export Payroll
                    </button>
                    ${canSubmitForReview ? `
                    <button class="btn btn-info btn-sm" onclick="submitPayrollForReview()" id="submitBtn">
                        <i class="fas fa-paper-plane"></i> Submit for Review
                    </button>
                    ` : ''}
                    ${canApprove ? `
                    <button class="btn btn-warning btn-sm" onclick="togglePayrollApproval()" id="approvalBtn">
                        <i class="fas fa-check"></i> Approve Payroll
                    </button>
                    ` : ''}
                    ${isApproved ? `
                    <button class="btn btn-secondary btn-sm" disabled id="approvalBtn">
                        <i class="fas fa-lock"></i> Approved
                    </button>
                    ` : ''}
                    ${isSubmittedForReview ? `
                    <button class="btn btn-secondary btn-sm" disabled id="submitBtn">
                        <i class="fas fa-eye"></i> Under Review
                    </button>
                    ` : ''}
                </div>
            </div>
        `;
      container.appendChild(header);

      const table = document.createElement('table');
      table.className = 'table table-striped table-hover';
      table.id = 'payrollTable';

      // Create header
      const thead = document.createElement('thead');
      thead.className = 'table-dark';
      const headerRow = document.createElement('tr');

      const headers = [
        'ID', 'Employee', 'Department', 'Position', 'Basic Salary', 'Allowances',
        'Gross Salary', 'Tax', 'Pension', 'Absent Ded.', 'Late Ded.', 'Total Ded.', 'Net Salary', 'Present Days', 'Actions'
      ];

      headers.forEach(function (header) {
        const th = document.createElement('th');
        th.textContent = header;
        th.style.padding = '10px 8px';
        th.style.fontSize = '13px';
        th.style.fontWeight = 'bold';
        th.style.whiteSpace = 'nowrap';
        headerRow.appendChild(th);
      });

      thead.appendChild(headerRow);
      table.appendChild(thead);

      // Create body
      const tbody = document.createElement('tbody');

      let totalGross = 0;
      let totalNet = 0;
      let totalDeductions = 0;

      payrollData.forEach(function (row, index) {
        const tr = document.createElement('tr');
        tr.setAttribute('data-employee-id', row.UserId);
        if (index % 2 === 0) tr.className = 'table-row-even';

        const cells = [
          row.UserId,
          row.EmployeeName,
          row.Department,
          row.Position,
          '₦' + (row.BasicSalary || 0).toLocaleString(),
          '₦' + (row.TotalAllowances || 0).toLocaleString(),
          '₦' + (row.GrossSalary || 0).toLocaleString(),
          '₦' + (row.TaxDeduction || 0).toLocaleString(),
          '₦' + (row.PensionDeduction || 0).toLocaleString(),
          '₦' + (row.AbsentDeduction || 0).toLocaleString(),
          '₦' + (row.LateDeduction || 0).toLocaleString(),
          '₦' + (row.TotalDeductions || 0).toLocaleString(),
          '₦' + (row.NetSalary || 0).toLocaleString(),
          row.PresentDays + '/' + row.WorkingDays,
          'ACTION_PLACEHOLDER' // Special placeholder for action buttons
        ];

        // Add to totals
        totalGross += row.GrossSalary || 0;
        totalNet += row.NetSalary || 0;
        totalDeductions += row.TotalDeductions || 0;

        cells.forEach(function (cellData, cellIndex) {
          const td = document.createElement('td');

          // Handle action buttons column - ENHANCED APPROVAL WORKFLOW
          if (cellData === 'ACTION_PLACEHOLDER') {
            td.style.padding = '8px';
            td.style.textAlign = 'center';
            td.style.whiteSpace = 'nowrap';

            // Get approval status and user permissions
            const approvalStatus = row.approval_status || 'draft';
            const userPermissions = window.userPermissions || {};
            // Check both individual status and global state
            const canEdit = userPermissions['payroll.edit'] && (approvalStatus === 'draft' || approvalStatus === 'rejected') && !state.payrollSubmittedForReview && !state.payrollApproved;
            const canDelete = userPermissions['payroll.delete'] && (approvalStatus === 'draft' || approvalStatus === 'rejected') && !state.payrollSubmittedForReview && !state.payrollApproved;
            // Remove individual submit buttons since we now have global submit
            const canSubmit = false; // Individual submit disabled - use global submit
            const canReview = userPermissions['payroll.review'] && approvalStatus === 'submitted_for_review';
            const canApprove = userPermissions['payroll.approve'] && approvalStatus === 'pending_approval';
            const canReject = userPermissions['payroll.reject'] && (approvalStatus === 'submitted_for_review' || approvalStatus === 'pending_approval');
            const canExport = userPermissions['payroll.export'] && approvalStatus === 'approved';

            // Create action buttons container
            const actionsContainer = document.createElement('div');
            actionsContainer.className = 'd-flex gap-1 justify-content-center align-items-center flex-wrap';

            // Edit button
            if (canEdit) {
              const editBtn = createActionButton('edit', 'fas fa-edit', 'Edit Employee', 'btn-warning', row.UserId, index);
              actionsContainer.appendChild(editBtn);
            }

            // Delete button
            if (canDelete) {
              const deleteBtn = createActionButton('delete', 'fas fa-trash', 'Delete Employee', 'btn-danger', row.UserId, index);
              actionsContainer.appendChild(deleteBtn);
            }

            // Submit for Review button
            if (canSubmit) {
              const submitBtn = createActionButton('submit', 'fas fa-paper-plane', 'Submit for Review', 'btn-info', row.UserId, index);
              actionsContainer.appendChild(submitBtn);
            }

            // Review button
            if (canReview) {
              const reviewBtn = createActionButton('review', 'fas fa-eye', 'Review', 'btn-primary', row.UserId, index);
              actionsContainer.appendChild(reviewBtn);
            }

            // Approve button
            if (canApprove) {
              const approveBtn = createActionButton('approve', 'fas fa-check', 'Approve', 'btn-success', row.UserId, index);
              actionsContainer.appendChild(approveBtn);
            }

            // Reject button
            if (canReject) {
              const rejectBtn = createActionButton('reject', 'fas fa-times', 'Reject', 'btn-danger', row.UserId, index);
              actionsContainer.appendChild(rejectBtn);
            }

            // Export button
            if (canExport) {
              const exportBtn = createActionButton('export', 'fas fa-file-export', 'Export', 'btn-secondary', row.UserId, index);
              actionsContainer.appendChild(exportBtn);
            }

            // Approval status badge
            const statusBadge = createApprovalStatusBadge(approvalStatus);
            actionsContainer.appendChild(statusBadge);

            td.appendChild(actionsContainer);

            console.log('Created approval workflow buttons for employee:', row.UserId, 'Status:', approvalStatus);

          } else {
            td.textContent = cellData;
            td.style.padding = '10px 8px';
            td.style.fontSize = '13px';
            td.style.whiteSpace = 'nowrap';
          }

          // Color coding for financial columns
          if (cellIndex >= 4 && cellIndex <= 12) { // Salary columns
            if (cellIndex === 6) { // Gross salary
              td.style.color = '#0d6efd';
              td.style.fontWeight = 'bold';
            } else if (cellIndex === 12) { // Net salary
              td.style.color = '#28a745';
              td.style.fontWeight = 'bold';
            } else if (cellIndex >= 7 && cellIndex <= 11) { // Deductions
              td.style.color = '#dc3545';
            }
          }

          // Color coding for attendance
          if (cellIndex === 13) { // Present days
            const [present, total] = cellData.split('/').map(Number);
            const attendanceRate = (present / total) * 100;
            if (attendanceRate >= 95) {
              td.style.color = '#28a745';
            } else if (attendanceRate >= 85) {
              td.style.color = '#ffc107';
            } else {
              td.style.color = '#dc3545';
            }
            td.style.fontWeight = 'bold';
          }

          tr.appendChild(td);
        });

        tbody.appendChild(tr);
      });

      table.appendChild(tbody);

      // Add totals row
      const tfooter = document.createElement('tfoot');
      tfooter.className = 'table-dark';
      const totalsRow = document.createElement('tr');

      const totalCells = [
        'TOTALS', '', '', '',
        '₦' + payrollData.reduce((sum, row) => sum + (row.BasicSalary || 0), 0).toLocaleString(),
        '₦' + payrollData.reduce((sum, row) => sum + (row.TotalAllowances || 0), 0).toLocaleString(),
        '₦' + totalGross.toLocaleString(),
        '₦' + payrollData.reduce((sum, row) => sum + (row.TaxDeduction || 0), 0).toLocaleString(),
        '₦' + payrollData.reduce((sum, row) => sum + (row.PensionDeduction || 0), 0).toLocaleString(),
        '₦' + payrollData.reduce((sum, row) => sum + (row.AbsentDeduction || 0), 0).toLocaleString(),
        '₦' + payrollData.reduce((sum, row) => sum + (row.LateDeduction || 0), 0).toLocaleString(),
        '₦' + totalDeductions.toLocaleString(),
        '₦' + totalNet.toLocaleString(),
        payrollData.length + ' employees',
        ''
      ];

      totalCells.forEach(function (cellData, cellIndex) {
        const td = document.createElement('td');
        td.textContent = cellData;
        td.style.padding = '10px 8px';
        td.style.fontSize = '13px';
        td.style.fontWeight = 'bold';
        td.style.whiteSpace = 'nowrap';

        if (cellIndex === 0) {
          td.style.color = '#ffffff';
        } else if (cellIndex === 12) { // Net total
          td.style.color = '#28a745';
        }

        totalsRow.appendChild(td);
      });

      tfooter.appendChild(totalsRow);
      table.appendChild(tfooter);

      container.appendChild(table);

      // Add summary info
      const summary = document.createElement('div');
      summary.className = 'mt-3 p-3 bg-light rounded';
      summary.innerHTML = `
            <div class="row text-center">
                <div class="col-md-3">
                    <strong>Total Employees:</strong><br>
                    <span class="h5 text-primary">${payrollData.length}</span>
                </div>
                <div class="col-md-3">
                    <strong>Gross Payroll:</strong><br>
                    <span class="h5 text-info">₦${totalGross.toLocaleString()}</span>
                </div>
                <div class="col-md-3">
                    <strong>Total Deductions:</strong><br>
                    <span class="h5 text-danger">₦${totalDeductions.toLocaleString()}</span>
                </div>
                <div class="col-md-3">
                    <strong>Net Payroll:</strong><br>
                    <span class="h5 text-success">₦${totalNet.toLocaleString()}</span>
                </div>
            </div>
        `;
      container.appendChild(summary);

      return container;
    }

    function updateTabIndicator(tabName, recordCount) {
      const tab = document.querySelector(`[data-tab="${tabName}"]`);
      if (tab) {
        // Add badge to show record count
        let badge = tab.querySelector('.badge');
        if (!badge) {
          badge = document.createElement('span');
          badge.className = 'badge bg-primary ms-2';
          tab.appendChild(badge);
        }
        badge.textContent = recordCount;
      }
    }

    // Display dashboard stats with enhanced element detection
    function displayDashboardStats(summaryData) {
      if (!summaryData || summaryData.length === 0) {
        resetDashboardStats();
        return;
      }

      // Calculate overall stats
      let totalUsers = summaryData.length;
      let totalPresent = summaryData.reduce(function (sum, user) { return sum + user.Total_Present; }, 0);
      let totalAbsent = summaryData.reduce(function (sum, user) { return sum + user.Total_Absent; }, 0);
      let totalLate = summaryData.reduce(function (sum, user) { return sum + user.Total_Late; }, 0);

      // Find most punctual user (highest attendance rate)
      let mostPunctualUser = '-';
      if (summaryData.length > 0) {
        const userWithBestAttendance = summaryData.reduce(function (best, user) {
          const userTotalDays = user.Total_Present + user.Total_Absent;
          const userAttendanceRate = userTotalDays > 0 ? (user.Total_Present / userTotalDays) : 0;
          const bestTotalDays = best.Total_Present + best.Total_Absent;
          const bestAttendanceRate = bestTotalDays > 0 ? (best.Total_Present / bestTotalDays) : 0;
          return userAttendanceRate > bestAttendanceRate ? user : best;
        });
        mostPunctualUser = userWithBestAttendance.Username || '-';
      }

      // Calculate inactive users (users with 0 present days)
      let inactiveUsers = summaryData.filter(function (user) { return user.Total_Present === 0; }).length;

      // Calculate percentages
      const totalDays = totalPresent + totalAbsent;
      const attendanceRate = totalDays > 0 ? ((totalPresent / totalDays) * 100).toFixed(1) + '%' : '0%';

      // ENHANCED: Try multiple element selectors and update methods
      updateDashboardStatMultiple(['totalEmployees', 'total-users-count', 'users-total'], totalUsers);
      updateDashboardStatMultiple(['mostPunctual', 'total-present-count', 'present-total'], mostPunctualUser);
      updateDashboardStatMultiple(['inactiveUsers', 'total-absent-count', 'absent-total'], inactiveUsers);
      updateDashboardStatMultiple(['avgAttendance', 'attendance-rate', 'overall-rate'], attendanceRate);

      console.log('Dashboard stats updated:', {
        users: totalUsers,
        present: totalPresent,
        absent: totalAbsent,
        late: totalLate,
        rate: attendanceRate
      });
    }

    function updateDashboardStatMultiple(selectors, value) {
      let updated = false;

      for (const selector of selectors) {
        const element = document.getElementById(selector);
        if (element) {
          element.textContent = value;
          updated = true;
          console.log(`Updated ${selector} to ${value}`);
          break; // Found and updated, stop trying other selectors
        }
      }

      if (!updated) {
        // Try by class names as fallback
        const classSelectors = [
          'dashboard-stat-users', 'dashboard-stat-present', 'dashboard-stat-absent',
          'dashboard-stat-late', 'dashboard-stat-rate'
        ];

        classSelectors.forEach(function (className) {
          const elements = document.getElementsByClassName(className);
          if (elements.length > 0) {
            elements[0].textContent = value;
            updated = true;
            console.log(`Updated ${className} to ${value}`);
          }
        });
      }

      if (!updated) {
        console.warn(`Could not find dashboard element for selectors:`, selectors);
      }
    }

    function updateDashboardStat(elementId, value) {
      const element = document.getElementById(elementId);
      if (element) {
        element.textContent = value;
        console.log(`Updated ${elementId} to ${value}`);
        return true;
      }
      return false;
    }

    // File validation functionality
    function validateFiles() {
      const userFileInput = getCachedElement('userFile');
      const attFilesInput = getCachedElement('attFiles');
      const userFile = userFileInput ? userFileInput.files[0] : null;
      const attFiles = attFilesInput ? Array.from(attFilesInput.files || []) : [];
      const payrollFileInput = getCachedElement('payrollFile');
      const payrollFile = payrollFileInput ? payrollFileInput.files[0] : null;

      // Validate user file
      if (!userFile) {
        return {
          valid: false,
          message: 'Please select a user mapping file (user.dat)'
        };
      }

      if (!userFile.name.toLowerCase().endsWith('.dat') && !userFile.name.toLowerCase().endsWith('.txt')) {
        return {
          valid: false,
          message: 'User mapping file must be a .dat or .txt file'
        };
      }

      if (userFile.size > 10 * 1024 * 1024) { // 10MB limit
        return {
          valid: false,
          message: 'User mapping file is too large (max 10MB)'
        };
      }

      // Validate attendance files
      if (attFiles.length === 0) {
        return {
          valid: false,
          message: '📁 Please select at least one attendance log file (.dat)'
        };
      }

      for (const file of attFiles) {
        if (!file.name.toLowerCase().endsWith('.dat') && !file.name.toLowerCase().endsWith('.txt')) {
          return {
            valid: false,
            message: `⚠️ Attendance file "${file.name}" must be a .dat or .txt file`
          };
        }

        if (file.size > 50 * 1024 * 1024) { // 50MB limit
          return {
            valid: false,
            message: `⚠️ Attendance file "${file.name}" is too large (max 50MB)`
          };
        }
      }

      // Validate payroll file (optional)
      if (payrollFile) {
        if (!payrollFile.name.toLowerCase().endsWith('.xlsx') && !payrollFile.name.toLowerCase().endsWith('.xls')) {
          return {
            valid: false,
            message: `⚠️ Payroll file "${payrollFile.name}" must be an Excel file (.xlsx or .xls)`
          };
        }

        if (payrollFile.size > 25 * 1024 * 1024) { // 25MB limit
          return {
            valid: false,
            message: `⚠️ Payroll file "${payrollFile.name}" is too large (max 25MB)`
          };
        }
      }

      return {
        valid: true,
        userFile,
        attFiles,
        payrollFile
      };
    }

    // ENHANCED: Clear all functionality - FIXED to properly clear dashboard
    function clearAll() {
      console.log('Starting comprehensive clear operation...');

      // Clear file inputs
      const fileInputs = ['userFile', 'attFiles', 'payrollFile'];
      fileInputs.forEach(function (inputId) {
        const input = document.getElementById(inputId);
        if (input) {
          input.value = '';
          console.log(`Cleared ${inputId} file input`);
        }
      });

      // Reset settings to defaults
      const settingsInputs = [
        ['startTime', '08:00'],
        ['graceMin', '15'],
        ['endTime', '17:00']
      ];

      settingsInputs.forEach(function ([id, defaultValue]) {
        const input = document.getElementById(id);
        if (input) {
          input.value = defaultValue;
          console.log(`Reset ${id} to ${defaultValue}`);
        }
      });

      // Clear application state including payroll approval
      state.users = [];
      state.attendanceData = [];
      state.summaryData = [];
      state.payrollData = [];
      state.processedResults = null;
      state.currentView = 'dashboard';
      state.payrollDataSource = null;
      state.payrollApproved = false;
      state.payrollApprovedDate = null;
      state.payrollSubmittedForReview = false;
      state.payrollSubmittedDate = null;
      console.log('Application state cleared including payroll approval status');

      // Clear all data containers
      clearDataContainers();

      // Reset dashboard stats with enhanced detection
      resetDashboardStats();

      // Clear any status messages
      const statusDisplay = document.getElementById('statusDisplay');
      if (statusDisplay) {
        statusDisplay.style.display = 'none';
      }

      // Reset tab indicators
      resetTabIndicators();

      // Switch back to dashboard view
      showTabContent('dashboard');
      const dashboardTab = document.querySelector('[data-tab="dashboard"]');
      if (dashboardTab) {
        document.querySelectorAll('.attendance-nav-tab').forEach(function (t) {
          t.classList.remove('active');
        });
        dashboardTab.classList.add('active');
      }

      // Close any open modals
      closeAttendanceDetailModal();
      closeModal();
      closeEditPayrollModal();

      // Show success notification
      if (typeof toastr !== 'undefined') {
        toastr.success('All data cleared and dashboard reset successfully!', 'Clear Completed');
      } else {
        alert('All data cleared and dashboard reset successfully!');
      }

      console.log('Comprehensive clear operation completed successfully');
    }

    function clearDataContainers() {
      const containers = [
        ['attendanceTableContainer', 'No attendance data available. Please process your files first.'],
        ['summaryTableContainer', 'No summary data available. Please process your files first.'],
        ['payrollTableContainer', 'No payroll data available. Please upload a payroll Excel file and process it.'],
        ['reportContainer', 'No report data available. Please process your files first.']
      ];

      containers.forEach(function ([containerId, message]) {
        const container = document.getElementById(containerId);
        if (container) {
          container.innerHTML = `<div class="alert alert-info">${message}</div>`;
          console.log(`Cleared ${containerId} container`);
        }
      });
    }

    // ENHANCED: Reset dashboard stats with comprehensive element detection
    function resetDashboardStats() {
      console.log('Starting comprehensive dashboard stats reset...');

      // Primary element selectors (most likely)
      const primarySelectors = [
        ['totalEmployees', '0'],
        ['mostPunctual', '-'],
        ['inactiveUsers', '0'],
        ['avgAttendance', '0%']
      ];

      // Alternative selectors (backup)
      const altSelectors = [
        ['total-users-count', 'users-count', 'dashboard-users'],
        ['total-present-count', 'present-count', 'dashboard-present'],
        ['total-absent-count', 'absent-count', 'dashboard-absent'],
        ['total-late-count', 'late-count', 'dashboard-late'],
        ['attendance-rate', 'overall-rate', 'dashboard-rate']
      ];

      // Try primary selectors first
      primarySelectors.forEach(function ([selector, value]) {
        if (updateDashboardStat(selector, value)) {
          console.log(`✓ Reset ${selector} successfully`);
        } else {
          console.warn(`✗ Could not find element: ${selector}`);
        }
      });

      // Try alternative selectors
      altSelectors.forEach(function (selectors, index) {
        const value = index === 4 ? '0%' : '0'; // Last one is percentage
        let found = false;

        for (const selector of selectors) {
          if (updateDashboardStat(selector, value)) {
            console.log(`✓ Reset alternative ${selector} successfully`);
            found = true;
            break;
          }
        }

        if (!found) {
          console.warn(`✗ Could not find any alternative elements for index ${index}`);
        }
      });

      console.log('Dashboard stats reset operation completed');
    }

    function resetTabIndicators() {
      // Remove badges from tabs
      document.querySelectorAll('.attendance-nav-tab .badge').forEach(function (badge) {
        badge.remove();
      });
      console.log('Tab indicators reset');
    }

    // Process data functionality with enhanced error handling
    function processData() {
      const validation = validateFiles();

      if (!validation.valid) {
        showUserNotification(validation.message, 'error');
        return;
      }

      // Show processing notification with progress indicator
      showUserNotification('Processing files... This may take a moment.', 'info');
      showProgressIndicator(true);

      // Process files locally for immediate display
      processFilesLocally(validation.userFile, validation.attFiles, validation.payrollFile)
        .then(function (results) {
          showProgressIndicator(false);

          if (results && results.detail && results.summary) {
            // Store results in state
            state.processedResults = results;
            state.attendanceData = results.detail;
            state.summaryData = results.summary;

            if (results.payroll) {
              state.payrollData = results.payroll;
              state.payrollDataSource = validation.payrollFile ? 'Excel File' : 'Generated from Attendance';
            }

            // Display the data immediately
            displayAttendanceData(results.detail, results.summary);
            displayDashboardStats(results.summary);

            // Display payroll data if available
            if (results.payroll && results.payroll.length > 0) {
              displayPayrollData(results.payroll);
            }

            let message = `Processing completed! Found ${results.detail.length} attendance records for ${results.summary.length} users.`;
            if (results.payroll && results.payroll.length > 0) {
              message += ` Payroll data ${state.payrollDataSource === 'Excel File' ? 'parsed from Excel' : 'generated'} for ${results.payroll.length} employees.`;
            }

            showUserNotification(message, 'success');

            console.log('Local processing completed:', {
              detailRecords: results.detail.length,
              summaryRecords: results.summary.length,
              payrollRecords: results.payroll ? results.payroll.length : 0,
              payrollSource: state.payrollDataSource
            });
          } else {
            throw new Error('Invalid processing results');
          }
        })
        .catch(function (error) {
          showProgressIndicator(false);
          console.error('Local processing error:', error);
          showUserNotification('Error processing files: ' + error.message, 'error');
        });
    }

    // Process files locally with enhanced payroll support
    async function processFilesLocally(userFile, attFiles, payrollFile) {
      try {
        console.log('Starting enhanced local file processing...');

        // Reset payroll approval and submission status when new data is uploaded
        if (state.payrollApproved) {
          state.payrollApproved = false;
          state.payrollApprovedDate = null;
          console.log('Payroll approval status reset due to new data upload');
        }
        if (state.payrollSubmittedForReview) {
          state.payrollSubmittedForReview = false;
          state.payrollSubmittedDate = null;
          console.log('Payroll submission status reset due to new data upload');
        }

        // Read user file
        const userText = await readFile(userFile);
        const users = parseUsers(userText);
        console.log('Parsed users:', users.length);

        if (users.length === 0) {
          throw new Error('No users found in user file');
        }

        // Read attendance files
        let allAttendanceText = '';
        for (const file of attFiles) {
          const text = await readFile(file);
          allAttendanceText += text + '\n';
        }

        const attendanceRecords = parseAttendance(allAttendanceText);
        console.log('Parsed attendance records:', attendanceRecords.length);

        if (attendanceRecords.length === 0) {
          throw new Error('No attendance records found in attendance files');
        }

        // Get settings from form
        const startTime = document.getElementById('startTime').value || '08:00';
        const graceMin = parseInt(document.getElementById('graceMin').value || '15', 10);
        const endTime = document.getElementById('endTime').value || '17:00';

        // Compute daily attendance
        const { attendanceByDay, minDate } = computeDailyAttendance(attendanceRecords, users);

        // Build calendar (assuming 21 working days from first attendance date)
        const calendar = buildBusinessCalendar(minDate, 21);
        console.log('Built calendar with', calendar.length, 'working days');

        // Build outputs
        const outputs = buildOutputs(users, attendanceByDay, calendar, startTime, graceMin, endTime);

        // Store summary for payroll calculations
        state.summaryData = outputs.summary;

        // Process payroll file if provided OR generate based on attendance
        let payrollData = [];
        if (payrollFile) {
          try {
            const payrollBuffer = await readFile(payrollFile);
            payrollData = parsePayrollExcel(payrollBuffer, users);
            console.log('Parsed payroll from Excel:', payrollData.length);
          } catch (payrollError) {
            console.warn('Error parsing payroll Excel, generating from attendance:', payrollError);
            payrollData = parsePayrollExcel(null, users); // Generate from attendance
            showUserNotification('Generated payroll data from attendance records (Excel parsing failed)', 'warning');
          }
        } else {
          // Generate payroll data from attendance
          payrollData = parsePayrollExcel(null, users);
          console.log('Generated payroll from attendance:', payrollData.length);
        }

        // Add payroll data to results
        outputs.payroll = payrollData;

        console.log('Enhanced processing complete:', {
          detail: outputs.detail.length,
          summary: outputs.summary.length,
          payroll: payrollData.length,
          approvalReset: true
        });

        return outputs;

      } catch (error) {
        console.error('Error in enhanced local processing:', error);
        throw error;
      }
    }

    // Save settings functionality
    function saveSettingsLaravel() {
      const form = document.getElementById('settingsForm');
      if (!form) return;

      const formData = new FormData(form);

      if (typeof toastr !== 'undefined') {
        toastr.info('Saving settings...', 'Settings');
      }

      // For now, just show success (you can implement actual saving later)
      setTimeout(function () {
        if (typeof toastr !== 'undefined') {
          toastr.success('Settings saved successfully', 'Settings Updated');
        } else {
          alert('Settings saved successfully');
        }
      }, 500);
    }

    // Enhanced user notification system
    function showUserNotification(message, type) {
      if (typeof toastr !== 'undefined') {
        switch (type) {
          case 'success':
            toastr.success(message, 'Success');
            break;
          case 'error':
            toastr.error(message, 'Error');
            break;
          case 'warning':
            toastr.warning(message, 'Warning');
            break;
          case 'info':
          default:
            toastr.info(message, 'Information');
            break;
        }
      } else {
        // Fallback to alert for older browsers
        alert(type.toUpperCase() + ': ' + message);
      }
    }

    // Progress indicator system
    function showProgressIndicator(show) {
      let progressDiv = document.getElementById('processing-progress');

      if (show) {
        if (!progressDiv) {
          progressDiv = document.createElement('div');
          progressDiv.id = 'processing-progress';
          progressDiv.style.cssText = `
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: white;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                    z-index: 10000;
                    text-align: center;
                    min-width: 300px;
                `;
          progressDiv.innerHTML = `
                    <div style="margin-bottom: 15px;">
                        <div style="width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                    </div>
                    <div style="font-weight: bold; margin-bottom: 10px;">Processing Files...</div>
                    <div style="color: #666; font-size: 14px;">Please wait while we process your attendance data</div>
                    <style>
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    </style>
                `;
          document.body.appendChild(progressDiv);
        }
        progressDiv.style.display = 'block';
      } else {
        if (progressDiv) {
          progressDiv.style.display = 'none';
        }
      }
    }

    // Enhanced Approval Workflow Helper Functions
    function getOverallApprovalStatus(statuses) {
      if (statuses.includes('approved')) return 'approved';
      if (statuses.includes('pending_approval')) return 'pending_approval';
      if (statuses.includes('submitted_for_review')) return 'submitted_for_review';
      if (statuses.includes('rejected')) return 'rejected';
      return 'draft';
    }

    function createOverallStatusBadge(status) {
      const statusConfig = {
        'draft': { class: 'bg-secondary', text: 'Draft', icon: 'fas fa-edit' },
        'submitted_for_review': { class: 'bg-warning', text: 'Under Review', icon: 'fas fa-eye' },
        'pending_approval': { class: 'bg-info', text: 'Pending Approval', icon: 'fas fa-clock' },
        'approved': { class: 'bg-success', text: 'Approved', icon: 'fas fa-check' },
        'rejected': { class: 'bg-danger', text: 'Rejected', icon: 'fas fa-times' }
      };
      
      const config = statusConfig[status] || statusConfig['draft'];
      return `<span class="badge ${config.class} ms-2"><i class="${config.icon}"></i> ${config.text}</span>`;
    }

    function createActionButton(action, icon, title, btnClass, userId, index) {
      const button = document.createElement('button');
      button.className = `btn btn-sm ${btnClass} payroll-action-btn`;
      button.style.padding = '4px 8px';
      button.style.fontSize = '11px';
      button.style.minWidth = '32px';
      button.innerHTML = `<i class="${icon}"></i>`;
      button.title = title;
      button.setAttribute('data-user-id', userId);
      button.setAttribute('data-index', index);
      button.setAttribute('data-action', action);
      
      // Add click handler
      button.onclick = function() {
        handlePayrollAction(action, userId, index);
      };
      
      return button;
    }

    function createApprovalStatusBadge(status) {
      const badge = document.createElement('span');
      badge.className = 'badge ms-1';
      
      const statusConfig = {
        'draft': { class: 'bg-secondary', text: 'Draft', icon: 'fas fa-edit' },
        'submitted_for_review': { class: 'bg-warning', text: 'Under Review', icon: 'fas fa-eye' },
        'pending_approval': { class: 'bg-info', text: 'Pending Approval', icon: 'fas fa-clock' },
        'approved': { class: 'bg-success', text: 'Approved', icon: 'fas fa-check' },
        'rejected': { class: 'bg-danger', text: 'Rejected', icon: 'fas fa-times' }
      };
      
      const config = statusConfig[status] || statusConfig['draft'];
      badge.className += ` ${config.class}`;
      badge.innerHTML = `<i class="${config.icon}"></i> ${config.text}`;
      badge.style.fontSize = '10px';
      badge.title = `Status: ${config.text}`;
      
      return badge;
    }

    function handlePayrollAction(action, userId, index) {
      const employee = state.payrollData[index];
      if (!employee) {
        showUserNotification('Employee data not found', 'error');
        return;
      }

      switch (action) {
        case 'edit':
          editPayrollEmployee(userId, index);
          break;
        case 'delete':
          deletePayrollEmployee(userId, index);
          break;
        case 'submit':
          submitPayrollForReview(userId, index);
          break;
        case 'review':
          reviewPayroll(userId, index);
          break;
        case 'approve':
          approvePayroll(userId, index);
          break;
        case 'reject':
          rejectPayroll(userId, index);
          break;
        case 'export':
          exportPayroll(userId, index);
          break;
        default:
          console.log('Unknown action:', action);
      }
    }

    // Enhanced Approval Workflow Functions
    function submitPayrollForReview(userId, index) {
      const employee = state.payrollData[index];
      if (!employee) return;

      // Show confirmation modal
      showApprovalModal('submit', employee, 'Submit for Review', 
        'Are you sure you want to submit this payroll for review?', 
        function(comments) {
          performApprovalAction('submit-for-review', userId, comments);
        });
    }

    function reviewPayroll(userId, index) {
      const employee = state.payrollData[index];
      if (!employee) return;

      showApprovalModal('review', employee, 'Review Payroll', 
        'Please review this payroll record and add any comments.', 
        function(comments) {
          performApprovalAction('review', userId, comments);
        });
    }

    function approvePayroll(userId, index) {
      const employee = state.payrollData[index];
      if (!employee) return;

      showApprovalModal('approve', employee, 'Approve Payroll', 
        'Are you sure you want to approve this payroll?', 
        function(comments) {
          performApprovalAction('approve', userId, comments);
        });
    }

    function rejectPayroll(userId, index) {
      const employee = state.payrollData[index];
      if (!employee) return;

      showRejectionModal(employee, function(reason, comments) {
        performApprovalAction('reject', userId, comments, reason);
      });
    }

    function performApprovalAction(action, userId, comments, reason = null) {
      const payload = { comments: comments };
      if (reason) payload.reason = reason;

      fetch(`/payroll/${userId}/${action}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(payload)
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showUserNotification(data.message, 'success');
          // Refresh the payroll table
          refreshPayrollTable();
        } else {
          showUserNotification(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showUserNotification('An error occurred while processing the request', 'error');
      });
    }

    function showApprovalModal(action, employee, title, message, callback) {
      const modalHtml = `
        <div class="modal fade" id="approvalModal" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">${title}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <p>${message}</p>
                <div class="mb-3">
                  <label class="form-label">Employee: <strong>${employee.EmployeeName}</strong></label>
                </div>
                <div class="mb-3">
                  <label for="approvalComments" class="form-label">Comments (Optional)</label>
                  <textarea class="form-control" id="approvalComments" rows="3" placeholder="Add any comments..."></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmApproval()">Confirm</button>
              </div>
            </div>
          </div>
        </div>
      `;

      // Remove existing modal
      const existingModal = document.getElementById('approvalModal');
      if (existingModal) existingModal.remove();

      // Add new modal
      document.body.insertAdjacentHTML('beforeend', modalHtml);
      
      // Store callback
      window.currentApprovalCallback = callback;
      
      // Show modal
      const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
      modal.show();
    }

    function showRejectionModal(employee, callback) {
      const modalHtml = `
        <div class="modal fade" id="rejectionModal" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Reject Payroll</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <p>Please provide a reason for rejecting this payroll.</p>
                <div class="mb-3">
                  <label class="form-label">Employee: <strong>${employee.EmployeeName}</strong></label>
                </div>
                <div class="mb-3">
                  <label for="rejectionReason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                  <textarea class="form-control" id="rejectionReason" rows="3" placeholder="Please provide a detailed reason for rejection..." required></textarea>
                </div>
                <div class="mb-3">
                  <label for="rejectionComments" class="form-label">Additional Comments (Optional)</label>
                  <textarea class="form-control" id="rejectionComments" rows="2" placeholder="Add any additional comments..."></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmRejection()">Reject</button>
              </div>
            </div>
          </div>
        </div>
      `;

      // Remove existing modal
      const existingModal = document.getElementById('rejectionModal');
      if (existingModal) existingModal.remove();

      // Add new modal
      document.body.insertAdjacentHTML('beforeend', modalHtml);
      
      // Store callback
      window.currentRejectionCallback = callback;
      
      // Show modal
      const modal = new bootstrap.Modal(document.getElementById('rejectionModal'));
      modal.show();
    }

    function confirmApproval() {
      const comments = document.getElementById('approvalComments').value;
      if (window.currentApprovalCallback) {
        window.currentApprovalCallback(comments);
      }
      bootstrap.Modal.getInstance(document.getElementById('approvalModal')).hide();
    }

    function confirmRejection() {
      const reason = document.getElementById('rejectionReason').value;
      const comments = document.getElementById('rejectionComments').value;
      
      if (!reason.trim()) {
        showUserNotification('Please provide a rejection reason', 'error');
        return;
      }
      
      if (window.currentRejectionCallback) {
        window.currentRejectionCallback(reason, comments);
      }
      bootstrap.Modal.getInstance(document.getElementById('rejectionModal')).hide();
    }

    function refreshPayrollTable() {
      // Reload payroll data and recreate table
      if (state.payrollData && state.payrollData.length > 0) {
        displayPayrollData(state.payrollData);
      }
    }

    // Payroll Action Functions
    function editPayrollEmployee(userId, index) {
      console.log('Edit payroll employee:', userId, index);
      
      if (state.payrollApproved) {
        showUserNotification('Cannot edit employee - Payroll has been approved', 'warning');
        return;
      }
      
      if (state.payrollSubmittedForReview) {
        showUserNotification('Cannot edit employee - Payroll has been submitted for review', 'warning');
        return;
      }
      
      if (!state.payrollData || state.payrollData.length === 0) {
        showUserNotification('No payroll data available to edit', 'error');
        return;
      }
      
      const employee = state.payrollData[index];
      if (!employee) {
        showUserNotification('Employee data not found', 'error');
        return;
      }
      
      // Create edit modal
      showEditPayrollModal(employee, index);
    }
    
    function deletePayrollEmployee(userId, index) {
      console.log('Delete payroll employee:', userId, index);
      
      if (state.payrollApproved) {
        showUserNotification('Cannot delete employee - Payroll has been approved', 'warning');
        return;
      }
      
      if (state.payrollSubmittedForReview) {
        showUserNotification('Cannot delete employee - Payroll has been submitted for review', 'warning');
        return;
      }
      
      if (!state.payrollData || state.payrollData.length === 0) {
        showUserNotification('No payroll data available to delete', 'error');
        return;
      }
      
      const employee = state.payrollData[index];
      if (!employee) {
        showUserNotification('Employee data not found', 'error');
        return;
      }
      
      // Show confirmation dialog
      if (confirm(`Are you sure you want to delete ${employee.EmployeeName} (${employee.UserId}) from the payroll?`)) {
        // Remove from payroll data
        state.payrollData.splice(index, 1);
        
        // Refresh the payroll table
        displayPayrollData(state.payrollData);
        
        showUserNotification(`${employee.EmployeeName} has been removed from payroll`, 'success');
        
        console.log('Employee deleted from payroll:', employee.EmployeeName);
      }
    }
    
    function submitPayrollForReview() {
      console.log('Submit payroll for review');
      
      if (!state.payrollData || state.payrollData.length === 0) {
        showUserNotification('No payroll data available to submit for review', 'error');
        return;
      }
      
      if (state.payrollSubmittedForReview) {
        // Already submitted - show info
        showUserNotification('Payroll is already submitted for review', 'info');
        return;
      }
      
      if (state.payrollApproved) {
        // Already approved - show info
        showUserNotification('Payroll is already approved', 'info');
        return;
      }
      
      // Show confirmation dialog
      const totalEmployees = state.payrollData.length;
      const totalNet = state.payrollData.reduce((sum, emp) => sum + (emp.NetSalary || 0), 0);
      
      if (confirm(`Submit payroll for review for ${totalEmployees} employees with total net salary of ₦${totalNet.toLocaleString()}?`)) {
        // Submit payroll for review
        state.payrollSubmittedForReview = true;
        state.payrollSubmittedDate = new Date().toLocaleDateString();
        
        // Refresh the payroll table to show updated button states
        displayPayrollData(state.payrollData);
        
        showUserNotification('Payroll submitted for review successfully! Edit and Delete buttons are now disabled.', 'success');
        
        console.log('Payroll submitted for review:', {
          employees: totalEmployees,
          totalNet: totalNet,
          date: state.payrollSubmittedDate
        });
      }
    }

    function togglePayrollApproval() {
      console.log('Toggle payroll approval');
      
      if (!state.payrollData || state.payrollData.length === 0) {
        showUserNotification('No payroll data available to approve', 'error');
        return;
      }
      
      if (state.payrollApproved) {
        // Already approved - show info
        showUserNotification('Payroll is already approved', 'info');
        return;
      }
      
      if (!state.payrollSubmittedForReview) {
        // Not submitted for review yet
        showUserNotification('Please submit payroll for review before approval', 'warning');
        return;
      }
      
      // Show confirmation dialog
      const totalEmployees = state.payrollData.length;
      const totalNet = state.payrollData.reduce((sum, emp) => sum + (emp.NetSalary || 0), 0);
      
      if (confirm(`Approve payroll for ${totalEmployees} employees with total net salary of ₦${totalNet.toLocaleString()}?`)) {
        // Approve payroll
        state.payrollApproved = true;
        state.payrollApprovedDate = new Date().toLocaleDateString();
        
        // Refresh the payroll table to show updated button states
        displayPayrollData(state.payrollData);
        
        showUserNotification('Payroll approved successfully! Edit and Delete buttons are now disabled.', 'success');
        
        console.log('Payroll approved:', {
          employees: totalEmployees,
          totalNet: totalNet,
          date: state.payrollApprovedDate
        });
      }
    }
    
    function exportPayroll() {
      console.log('Export payroll');
      
      if (!state.payrollData || state.payrollData.length === 0) {
        showUserNotification('No payroll data available to export', 'error');
        return;
      }
      
      if (!state.payrollApproved) {
        showUserNotification('Please approve the payroll before exporting', 'warning');
        return;
      }
      
      try {
        // Create export data
        const exportData = state.payrollData.map(emp => ({
          'Employee ID': emp.UserId,
          'Employee Name': emp.EmployeeName,
          'Department': emp.Department,
          'Position': emp.Position,
          'Basic Salary': emp.BasicSalary,
          'Transport Allowance': emp.TransportAllowance,
          'Meal Allowance': emp.MealAllowance,
          'Medical Allowance': emp.MedicalAllowance,
          'Total Allowances': emp.TotalAllowances,
          'Gross Salary': emp.GrossSalary,
          'Tax Deduction': emp.TaxDeduction,
          'Pension Deduction': emp.PensionDeduction,
          'Absent Deduction': emp.AbsentDeduction,
          'Late Deduction': emp.LateDeduction,
          'Total Deductions': emp.TotalDeductions,
          'Net Salary': emp.NetSalary,
          'Working Days': emp.WorkingDays,
          'Present Days': emp.PresentDays,
          'Absent Days': emp.AbsentDays,
          'Late Days': emp.LateDays,
          'Bank Account': emp.BankAccount,
          'Payroll Month': emp.PayrollMonth
        }));
        
        // Create workbook
        const workbook = XLSX.utils.book_new();
        const worksheet = XLSX.utils.json_to_sheet(exportData);
        
        // Set column widths
        const columnWidths = [
          { wch: 12 }, { wch: 20 }, { wch: 15 }, { wch: 15 }, { wch: 12 },
          { wch: 15 }, { wch: 12 }, { wch: 14 }, { wch: 14 }, { wch: 12 },
          { wch: 12 }, { wch: 15 }, { wch: 14 }, { wch: 12 }, { wch: 14 },
          { wch: 12 }, { wch: 12 }, { wch: 12 }, { wch: 11 }, { wch: 10 },
          { wch: 14 }, { wch: 15 }
        ];
        worksheet['!cols'] = columnWidths;
        
        // Style the header row
        const headerStyle = {
          font: { bold: true, color: { rgb: "FFFFFF" } },
          fill: { fgColor: { rgb: "4285F4" } },
          alignment: { horizontal: "center", vertical: "center" }
        };
        
        // Apply header styling
        const headerRange = XLSX.utils.decode_range(worksheet['!ref']);
        for (let col = headerRange.s.c; col <= headerRange.e.c; col++) {
          const cellAddress = XLSX.utils.encode_cell({ r: 0, c: col });
          if (worksheet[cellAddress]) {
            worksheet[cellAddress].s = headerStyle;
          }
        }
        
        // Add worksheet to workbook
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Payroll Export');
        
        // Generate filename
        const currentDate = new Date();
        const dateStr = currentDate.toISOString().slice(0, 10);
        const fileName = `Payroll_Export_${dateStr}.xlsx`;
        
        // Download file
        XLSX.writeFile(workbook, fileName);
        
        showUserNotification('Payroll exported successfully!', 'success');
        console.log('Payroll exported:', fileName, exportData.length, 'employees');
        
      } catch (error) {
        console.error('Error exporting payroll:', error);
        showUserNotification('Error exporting payroll: ' + error.message, 'error');
      }
    }
    
    function showEditPayrollModal(employee, index) {
      // Create modal if it doesn't exist
      let modal = document.getElementById('editPayrollModal');
      if (!modal) {
        modal = createEditPayrollModal();
        document.body.appendChild(modal);
      }
      
      // Populate modal with employee data
      populateEditModal(employee, index);
      
      // Show modal
      modal.style.display = 'block';
      modal.classList.add('show');
      document.body.classList.add('modal-open');
      
      // Add backdrop
      if (!document.querySelector('.modal-backdrop')) {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
      }
    }
    
    function createEditPayrollModal() {
      const modal = document.createElement('div');
      modal.id = 'editPayrollModal';
      modal.className = 'modal fade';
      modal.setAttribute('tabindex', '-1');
      modal.setAttribute('role', 'dialog');
      modal.style.display = 'none';
      
      modal.innerHTML = `
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title">Edit Employee Payroll</h5>
              <button type="button" class="btn-close btn-close-white" onclick="closeEditPayrollModal()" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form id="editPayrollForm">
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Employee ID</label>
                      <input type="text" class="form-control" id="editUserId" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Employee Name</label>
                      <input type="text" class="form-control" id="editEmployeeName" readonly>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Department</label>
                      <input type="text" class="form-control" id="editDepartment">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Position</label>
                      <input type="text" class="form-control" id="editPosition">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Basic Salary</label>
                      <input type="number" class="form-control" id="editBasicSalary" min="0" step="100">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Transport Allowance</label>
                      <input type="number" class="form-control" id="editTransportAllowance" min="0" step="100">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Meal Allowance</label>
                      <input type="number" class="form-control" id="editMealAllowance" min="0" step="100">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Medical Allowance</label>
                      <input type="number" class="form-control" id="editMedicalAllowance" min="0" step="100">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Bank Account</label>
                      <input type="text" class="form-control" id="editBankAccount">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Payroll Month</label>
                      <input type="text" class="form-control" id="editPayrollMonth">
                    </div>
                  </div>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" onclick="closeEditPayrollModal()">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="savePayrollChanges()">Save Changes</button>
            </div>
          </div>
        </div>
      `;
      
      // Add click outside to close
      modal.addEventListener('click', function (e) {
        if (e.target === modal) {
          closeEditPayrollModal();
        }
      });
      
      return modal;
    }
    
    function populateEditModal(employee, index) {
      // Store the index for saving
      document.getElementById('editPayrollModal').setAttribute('data-employee-index', index);
      
      // Populate form fields
      document.getElementById('editUserId').value = employee.UserId || '';
      document.getElementById('editEmployeeName').value = employee.EmployeeName || '';
      document.getElementById('editDepartment').value = employee.Department || '';
      document.getElementById('editPosition').value = employee.Position || '';
      document.getElementById('editBasicSalary').value = employee.BasicSalary || 0;
      document.getElementById('editTransportAllowance').value = employee.TransportAllowance || 0;
      document.getElementById('editMealAllowance').value = employee.MealAllowance || 0;
      document.getElementById('editMedicalAllowance').value = employee.MedicalAllowance || 0;
      document.getElementById('editBankAccount').value = employee.BankAccount || '';
      document.getElementById('editPayrollMonth').value = employee.PayrollMonth || '';
    }
    
    function savePayrollChanges() {
      const modal = document.getElementById('editPayrollModal');
      const index = parseInt(modal.getAttribute('data-employee-index'));
      
      if (index === null || index < 0 || index >= state.payrollData.length) {
        showUserNotification('Invalid employee index', 'error');
        return;
      }
      
      // Get form values
      const updatedEmployee = {
        ...state.payrollData[index], // Keep existing data
        Department: document.getElementById('editDepartment').value,
        Position: document.getElementById('editPosition').value,
        BasicSalary: parseFloat(document.getElementById('editBasicSalary').value) || 0,
        TransportAllowance: parseFloat(document.getElementById('editTransportAllowance').value) || 0,
        MealAllowance: parseFloat(document.getElementById('editMealAllowance').value) || 0,
        MedicalAllowance: parseFloat(document.getElementById('editMedicalAllowance').value) || 0,
        BankAccount: document.getElementById('editBankAccount').value,
        PayrollMonth: document.getElementById('editPayrollMonth').value
      };
      
      // Recalculate derived values
      updatedEmployee.TotalAllowances = updatedEmployee.TransportAllowance + updatedEmployee.MealAllowance + updatedEmployee.MedicalAllowance;
      updatedEmployee.GrossSalary = updatedEmployee.BasicSalary + updatedEmployee.TotalAllowances;
      updatedEmployee.TaxDeduction = Math.floor(updatedEmployee.GrossSalary * 0.075);
      updatedEmployee.PensionDeduction = Math.floor(updatedEmployee.BasicSalary * 0.08);
      updatedEmployee.TotalDeductions = updatedEmployee.TaxDeduction + updatedEmployee.PensionDeduction + updatedEmployee.AbsentDeduction + updatedEmployee.LateDeduction;
      updatedEmployee.NetSalary = updatedEmployee.GrossSalary - updatedEmployee.TotalDeductions;
      
      // Update the employee data
      state.payrollData[index] = updatedEmployee;
      
      // Refresh the payroll table
      displayPayrollData(state.payrollData);
      
      // Close modal
      closeEditPayrollModal();
      
      showUserNotification(`${updatedEmployee.EmployeeName} payroll updated successfully`, 'success');
      
      console.log('Payroll employee updated:', updatedEmployee.EmployeeName);
    }
    
    function closeEditPayrollModal() {
      const modal = document.getElementById('editPayrollModal');
      if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');
        
        // Remove backdrop
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
          backdrop.remove();
        }
      }
    }

    // Initialize dashboard on page load
    document.addEventListener('DOMContentLoaded', function () {
      console.log('Initializing enhanced attendance dashboard...');

      initTabSwitching();

      // Set initial tab content
      showTabContent('dashboard');

      // Initialize data containers with placeholder content
      clearDataContainers();

      // Add event listeners for buttons
      const processBtn = document.getElementById('processBtn');
      if (processBtn) {
        processBtn.addEventListener('click', function (e) {
          e.preventDefault();
          processData();
        });
        console.log('Process button event listener added');
      }

      const clearBtn = document.getElementById('clearBtn');
      if (clearBtn) {
        clearBtn.addEventListener('click', function (e) {
          e.preventDefault();
          clearAll();
        });
        console.log('Clear button event listener added');
      }

      // Add event listener for template download button
      const downloadTemplateBtn = document.getElementById('downloadTemplateBtn');
      if (downloadTemplateBtn) {
        downloadTemplateBtn.addEventListener('click', function (e) {
          e.preventDefault();
          downloadPayrollTemplate();
        });
        console.log('Download template button event listener added');
      }

      // Add event listener for template download link
      const downloadTemplateLink = document.getElementById('downloadTemplate');
      if (downloadTemplateLink) {
        downloadTemplateLink.addEventListener('click', function (e) {
          e.preventDefault();
          downloadPayrollTemplate();
        });
        console.log('Download template link event listener added');
      }

      // Initialize toastr if available
      if (typeof toastr !== 'undefined') {
        toastr.options = {
          "closeButton": true,
          "debug": false,
          "newestOnTop": true,
          "progressBar": true,
          "positionClass": "toast-top-right",
          "preventDuplicates": true,
          "onclick": null,
          "showDuration": "300",
          "hideDuration": "1000",
          "timeOut": "5000",
          "extendedTimeOut": "1000",
          "showEasing": "swing",
          "hideEasing": "linear",
          "showMethod": "fadeIn",
          "hideMethod": "fadeOut"
        };
        console.log('Toastr notifications initialized');
      }

      console.log('Enhanced attendance dashboard initialization completed successfully!');
    });
  </script>

@endsection