// Wait for XLSX to be loaded
document.addEventListener('DOMContentLoaded', function() {
  // Check if XLSX is loaded
  if (typeof XLSX === 'undefined') {
    console.error('XLSX library not loaded');
    showNotification('Error: Excel processing library not loaded', 'error');
    return;
  }
  
  // Initialize the application
  initializeApp();
});

// Application State
const state = {
  currentView: 'dashboard',
  users: [],
  attendanceData: [],
  summaryData: [],
  payrollData: [],
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

// DOM Elements
const elements = {
  navLinks: document.querySelectorAll('.nav-link'),
  views: document.querySelectorAll('.view'),
  pageTitle: document.getElementById('pageTitle'),
  pageSubtitle: document.getElementById('pageSubtitle'),
  
  // Dashboard
  totalEmployees: document.getElementById('totalEmployees'),
  mostPunctual: document.getElementById('mostPunctual'),
  inactiveUsers: document.getElementById('inactiveUsers'),
  avgAttendance: document.getElementById('avgAttendance'),
  
  // File inputs
  userFile: document.getElementById('userFile'),
  attFiles: document.getElementById('attFiles'),
  payrollFile: document.getElementById('payrollFile'),
  
  // Form inputs
  startTime: document.getElementById('startTime'),
  graceMin: document.getElementById('graceMin'),
  endTime: document.getElementById('endTime'),
  workDays: document.getElementById('workDays'),
  
  // Buttons
  uploadFilesBtn: document.getElementById('uploadFilesBtn'),
  processBtn: document.getElementById('processBtn'),
  clearBtn: document.getElementById('clearBtn'),
  generatePayrollBtn: document.getElementById('generatePayrollBtn'),
  
  // Tables
  attendanceTableBody: document.getElementById('attendanceTableBody'),
  payrollTableBody: document.getElementById('payrollTableBody'),
  
  // Search
  attendanceSearch: document.getElementById('attendanceSearch'),
  
  // Modal
  attendanceModal: document.getElementById('attendanceModal'),
  modalTitle: document.getElementById('modalTitle'),
  modalBody: document.getElementById('modalBody'),
  modalClose: document.getElementById('modalClose'),
  
  // Status
  statusDisplay: document.getElementById('statusDisplay'),
  statusMessage: document.getElementById('statusMessage'),
  
  // Notification
  notification: document.getElementById('notification'),
  notificationText: document.getElementById('notificationText')
};

// Initialize Application
function initializeApp() {
  console.log('Initializing Attendance Management System...');
  initNavigation();
  initEventListeners();
  loadSettings();
  renderAttendanceTable();
  renderPayrollTable();
  showNotification('System initialized successfully!', 'success');
}

// Navigation
function initNavigation() {
  elements.navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const view = link.getAttribute('data-view');
      switchView(view);
    });
  });
}

function switchView(viewName) {
  // Update navigation
  elements.navLinks.forEach(link => link.classList.remove('active'));
  document.querySelector(`[data-view="${viewName}"]`).classList.add('active');
  
  // Update views
  elements.views.forEach(view => view.classList.remove('active'));
  document.getElementById(viewName).classList.add('active');
  
  // Update header
  const titles = {
    dashboard: { title: 'Dashboard', subtitle: 'Overview of your attendance management system' },
    attendance: { title: 'Attendance', subtitle: 'View and manage employee attendance records' },
    payroll: { title: 'Payroll', subtitle: 'Generate and manage employee payroll' },
    settings: { title: 'Settings', subtitle: 'Configure system settings and preferences' }
  };
  
  elements.pageTitle.textContent = titles[viewName].title;
  elements.pageSubtitle.textContent = titles[viewName].subtitle;
  
  state.currentView = viewName;
}

// File Processing - ENHANCED VERSION
function readFile(file) {
  return new Promise((resolve) => {
    if (!file) return resolve('');
    const fr = new FileReader();
    fr.onload = () => resolve(String(fr.result || ''));
    fr.readAsText(file);
  });
}

// Parse user.dat (robust to control chars / variable spacing)
function parseUsers(text) {
  // Replace control chars with spaces, then split
  const cleaned = text.replace(/[\x00-\x1F]/g, ' ').replace(/\s+/g, ' ').trim();
  const tokens = cleaned.split(' ');
  const nameIdPairs = [];
  // Strategy: find sequences where a name token (letters only) is followed later by a numeric token; pair in order.
  // Since original file appears as Name … ID … Name … ID, we zip by alternating name and id appearances.
  const names = [], ids = [];
  for (const t of tokens) {
    if (/^[A-Za-z]+$/.test(t)) names.push(t);
    else if (/^\d+$/.test(t)) ids.push(t);
  }
  const n = Math.min(names.length, ids.length);
  for (let i = 0; i < n; i++) {
    nameIdPairs.push({ UserId: ids[i], Username: names[i] });
  }
  // Deduplicate by UserId, keep first username encountered
  const seen = new Map();
  for (const r of nameIdPairs) { 
    if (!seen.has(r.UserId)) seen.set(r.UserId, r.Username); 
  }
  return Array.from(seen, ([UserId, Username]) => ({ UserId, Username }));
}

// ENHANCED: Parse attendance logs with robust timestamp handling
function parseAttendance(text) {
  const rows = [];
  const lines = text.split(/\r?\n/);
  
  for (const line of lines) {
    const trimmed = line.trim();
    if (!trimmed) continue;
    
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
        if (parts[i] && parts[i-1]) {
          entries.push(parts[i-1].trim() + ' ' + parts[i]);
        }
      }
      for (const entry of entries) {
        parseTimestampEntry(entry, rows);
      }
    } else {
      parseTimestampEntry(trimmed, rows);
    }
  }
  return rows;
}

// ENHANCED: Helper function to parse individual timestamp entries with timezone fix
function parseTimestampEntry(entry, rows) {
  // Extract timestamp patterns more robustly
  const parts = entry.split(/\s+/);
  if (parts.length < 2) return;
  
  const uid = parts[0];
  if (!/^\d+$/.test(uid)) return;
  
  // Look for timestamp pattern: YYYY-MM-DD HH:MM:SS or similar
  const timestampMatch = entry.match(/(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2}:\d{2})/);
  if (!timestampMatch) {
    // Try alternative formats
    const altMatch = entry.match(/(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2}:\d{2})/);
    if (altMatch) {
      // FIXED: Force local time interpretation
      const dateTimeStr = altMatch[1] + ' ' + altMatch[2];
      const d = createLocalDate(dateTimeStr);
      if (!isNaN(d.getTime())) {
        rows.push({UserId: String(uid), Timestamp: d});
      }
    }
    return;
  }
  
  const dateStr = timestampMatch[1];
  const timeStr = timestampMatch[2];
  // FIXED: Force local time interpretation to prevent timezone shifts
  const dateTimeStr = dateStr + ' ' + timeStr;
  const d = createLocalDate(dateTimeStr);
  
  if (!isNaN(d.getTime())) {
    rows.push({UserId: String(uid), Timestamp: d});
  }
}

// FIXED: Create date in local timezone to prevent UTC conversion issues
function createLocalDate(dateTimeString) {
  // Parse manually to avoid timezone conversion
  const match = dateTimeString.match(/(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2}):(\d{2})/);
  if (!match) return new Date(dateTimeString); // fallback
  
  const [, year, month, day, hour, minute, second] = match;
  // Use local timezone by passing individual components
  return new Date(
    parseInt(year), 
    parseInt(month) - 1, // Month is 0-indexed in JS
    parseInt(day), 
    parseInt(hour), 
    parseInt(minute), 
    parseInt(second)
  );
}

// Compute min/max per user per date
function computeDailyAttendance(att, users) {
  // Union of userIds from both sources
  const userMap = new Map(users.map(u => [u.UserId, u.Username]));
  for (const r of att) { 
    if (!userMap.has(r.UserId)) userMap.set(r.UserId, ''); 
  }
  const minDate = att.reduce((m, r) => r.Timestamp < m ? r.Timestamp : m, att[0]?.Timestamp || new Date());

  // Group by userId+date
  const key = (uid, d) => uid + '|' + d.toISOString().slice(0, 10);
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

// Build N business days from start date
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

// ENHANCED: Build attendance outputs with accurate time comparison
function buildOutputs(users, attMap, calendar, startTime, graceMin, endTime) {
  // Build thresholds
  const [sh, sm] = startTime.split(':').map(x => parseInt(x || '0', 10));
  const [eh, em] = endTime.split(':').map(x => parseInt(x || '0', 10));
  
  // Calculate time thresholds in minutes since midnight (SIMPLIFIED AND ACCURATE)
  const startTimeMinutes = sh * 60 + sm;                    // e.g., 8:00 = 480 min
  const graceTimeMinutes = startTimeMinutes + graceMin;     // e.g., 8:15 = 495 min
  const lateTimeMinutes = graceTimeMinutes + 5;             // e.g., 8:20 = 500 min
  const endTimeMinutes = eh * 60 + em;                      // e.g., 16:00 = 960 min

  // Utility to get minutes since midnight for time comparison
  const getMinutesSinceMidnight = (date) => {
    return date.getHours() * 60 + date.getMinutes() + (date.getSeconds() / 60);
  };

  // DEBUG: Log the thresholds
  console.log(`Time thresholds: Start=${startTimeMinutes}min (${startTime}), Grace=${graceTimeMinutes}min, Late=${lateTimeMinutes}min, End=${endTimeMinutes}min`);

  const usersSorted = [...users].sort((a, b) => a.UserId.localeCompare(b.UserId, undefined, { numeric: true }));
  const detail = [];

  for (const u of usersSorted) {
    for (const day of calendar) {
      const key = u.UserId + '|' + day.toISOString().slice(0, 10);
      const rec = attMap.get(key);
      if (rec) {
        const clockin = rec.Clockin;
        const clockout = rec.Clockout;
        const clockinMinutes = getMinutesSinceMidnight(clockin);
        const clockoutMinutes = getMinutesSinceMidnight(clockout);
        
        // DEBUG: Log individual clockin times for first few records
        if (detail.length < 5) {
          const timeStr = String(clockin.getHours()).padStart(2, '0') + ':' + String(clockin.getMinutes()).padStart(2, '0');
          console.log(`User ${u.UserId} clockin: ${timeStr} (${clockinMinutes.toFixed(1)} min)`);
        }
        
        // Determine lateness status - FIXED LOGIC
        let lateStatus = 'No';
        let lateCategory = '';
        
        if (clockinMinutes <= graceTimeMinutes) {
          lateStatus = 'No'; // On time (within grace period)
          lateCategory = '';
        } else if (clockinMinutes <= lateTimeMinutes) {
          lateStatus = 'Low_Late'; // 5 minutes after grace period
          lateCategory = 'Minor';
        } else {
          lateStatus = 'Late'; // More than 5 minutes after grace period  
          lateCategory = 'Major';
        }
        
        const earlyLeave = clockoutMinutes < endTimeMinutes;
        const hours = (clockout - clockin) / 36e5;
        
        detail.push({
          UserId: u.UserId,
          Username: u.Username || '',
          Date: day.toISOString().slice(0, 10),
          WorkingDay: 'Yes',
          Clockin: clockin.toISOString().replace('T', ' ').slice(0, 19),
          Clockout: clockout.toISOString().replace('T', ' ').slice(0, 19),
          Late: lateStatus,
          LateCategory: lateCategory,
          EarlyLeave: earlyLeave ? 'Yes' : 'No',
          TotalHours: hours.toFixed(2),
          Status: 'Present'
        });
      } else {
        detail.push({
          UserId: u.UserId,
          Username: u.Username || '',
          Date: day.toISOString().slice(0, 10),
          WorkingDay: 'Yes',
          Clockin: '',
          Clockout: '',
          Late: '',
          LateCategory: '',
          EarlyLeave: '',
          TotalHours: '',
          Status: 'Absent'
        });
      }
    }
  }

  // ENHANCED: Build summary with enhanced late tracking
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

  const summary = Array.from(byUser.values()).sort((a, b) => a.UserId.localeCompare(b.UserId, undefined, { numeric: true }));
  return { detail, summary };
}

// Process Attendance Data
async function processAttendanceData() {
  try {
    showStatus('Reading files...', 'info');
    
    const usersTxt = await readFile(elements.userFile.files?.[0]);
    const attTxts = await Promise.all(Array.from(elements.attFiles.files || []).map(readFile));
    
    const users = parseUsers(usersTxt || '');
    const att = parseAttendance(attTxts.join('\n'));
    
    if (users.length === 0) throw new Error('No users parsed from user.dat');
    if (att.length === 0) throw new Error('No attendance rows parsed');

    const startTime = elements.startTime.value || '08:00';
    const graceMin = parseInt(elements.graceMin.value || '15', 10);
    const endTime = elements.endTime.value || '16:00';
    const days = parseInt(elements.workDays.value || '21', 10);

    const { attendanceByDay, minDate } = computeDailyAttendance(att, users);
    const calendar = buildBusinessCalendar(minDate, days);
    const { detail, summary } = buildOutputs(users, attendanceByDay, calendar, startTime, graceMin, endTime);

    // Update state
    state.users = users;
    state.attendanceData = detail;
    state.summaryData = summary;

    // Update UI
    updateDashboardStats();
    renderAttendanceTable();
    
    showNotification('Data processed successfully!', 'success');
    showStatus(`Processed ${detail.length} daily rows for ${summary.length} users.`, 'success');
    
    console.log('Sample processed data:', detail.slice(0, 3)); // Debug log
    
  } catch (err) {
    console.error(err);
    showNotification('Error: ' + err.message, 'error');
    showStatus('Error: ' + err.message, 'error');
  }
}

// Update Dashboard Statistics
function updateDashboardStats() {
  elements.totalEmployees.textContent = state.users.length;
  
  if (state.summaryData.length > 0) {
    // Most punctual (highest percentage of on-time attendance)
    const mostPunctualUser = state.summaryData.reduce((best, user) => {
      const totalDays = user.Total_Present + user.Total_Absent;
      const onTimeRate = totalDays > 0 ? ((user.Total_Present - user.Total_Late - user.Total_Low_Late) / totalDays) * 100 : 0;
      const bestRate = best ? ((best.Total_Present - best.Total_Late - best.Total_Low_Late) / (best.Total_Present + best.Total_Absent)) * 100 : 0;
      return onTimeRate > bestRate ? user : best;
    }, null);
    
    elements.mostPunctual.textContent = mostPunctualUser ? mostPunctualUser.Username : '-';
    
    // Inactive users (absent for all days)
    const inactiveCount = state.summaryData.filter(user => user.Total_Present === 0).length;
    elements.inactiveUsers.textContent = inactiveCount;
    
    // Average attendance rate
    const totalDays = state.settings.workDaysCount;
    const avgAttendance = state.summaryData.reduce((sum, user) => sum + user.Total_Present, 0) / (state.summaryData.length * totalDays) * 100;
    elements.avgAttendance.textContent = avgAttendance.toFixed(1) + '%';
  }
}

// ENHANCED: Render Attendance Table with new columns
function renderAttendanceTable() {
  const tbody = elements.attendanceTableBody;
  tbody.innerHTML = '';
  
  if (state.summaryData.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="8" style="text-align: center; color: var(--text-muted);">
          No attendance data available. Please upload and process files.
        </td>
      </tr>
    `;
    return;
  }
  
  // Update table header to include new Low Late column
  const headerRow = elements.attendanceTableBody.parentElement.querySelector('thead tr');
  if (headerRow && !headerRow.innerHTML.includes('Low Late')) {
    headerRow.innerHTML = `
      <th>User ID</th>
      <th>Username</th>
      <th>Present</th>
      <th>Absent</th>
      <th>Late</th>
      <th>Low Late</th>
      <th>Early Leave</th>
      <th>Actions</th>
    `;
  }
  
  state.summaryData.forEach(user => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${user.UserId}</td>
      <td><strong>${user.Username}</strong></td>
      <td><span class="status-pill status-present">${user.Total_Present}</span></td>
      <td><span class="status-pill status-absent">${user.Total_Absent}</span></td>
      <td><span class="status-pill status-late">${user.Total_Late}</span></td>
      <td><span class="status-pill status-warning">${user.Total_Low_Late || 0}</span></td>
      <td><span class="status-pill">${user.Total_EarlyLeave}</span></td>
      <td>
        <button class="btn btn-sm btn-primary view-details-btn" data-userid="${user.UserId}" data-username="${user.Username}">
          <i class="fas fa-eye"></i>
          View Details
        </button>
      </td>
    `;
    tbody.appendChild(row);
  });
  
  // Add event listeners for view details buttons
  document.querySelectorAll('.view-details-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const userId = e.target.closest('button').getAttribute('data-userid');
      const username = e.target.closest('button').getAttribute('data-username');
      showUserDetailedAttendance(userId, username);
    });
  });
}

// ENHANCED: Show User Detailed Attendance with new status display
function showUserDetailedAttendance(userId, username) {
  const userAttendance = state.attendanceData.filter(record => record.UserId === userId);
  
  elements.modalTitle.textContent = `Detailed Attendance - ${username} (${userId})`;
  
  let modalContent = `
    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Clock In</th>
            <th>Clock Out</th>
            <th>Hours</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
  `;
  
  userAttendance.forEach(record => {
    const statusClass = record.Status === 'Present' ? 'status-present' : 'status-absent';
    let lateIndicator = '';
    
    if (record.Late === 'Late') {
      lateIndicator = '<span class="status-pill status-late">Late</span>';
    } else if (record.Late === 'Low_Late') {
      lateIndicator = '<span class="status-pill status-warning">Low Late</span>';
    } else if (record.Late === 'No' && record.Status === 'Present') {
      lateIndicator = '<span class="status-pill status-success">On Time</span>';
    }
    
    modalContent += `
      <tr>
        <td>${record.Date}</td>
        <td>${record.Clockin || '-'}</td>
        <td>${record.Clockout || '-'}</td>
        <td>${record.TotalHours || '-'}</td>
        <td>
          <span class="status-pill ${statusClass}">${record.Status}</span>
          ${lateIndicator}
        </td>
      </tr>
    `;
  });
  
  modalContent += `
        </tbody>
      </table>
    </div>
  `;
  
  elements.modalBody.innerHTML = modalContent;
  elements.attendanceModal.classList.add('show');
}

// Load Payroll Data
async function loadPayrollData() {
  const file = elements.payrollFile.files?.[0];
  if (!file) {
    showNotification('Please select a payroll file', 'error');
    return;
  }

  try {
    showStatus('Loading payroll data...', 'info');
    
    // Read Excel file using SheetJS
    const arrayBuffer = await file.arrayBuffer();
    const workbook = XLSX.read(arrayBuffer, { type: 'array' });
    const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
    const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });

    // Find header row and extract employee data
    let headerRowIndex = -1;
    for (let i = 0; i < jsonData.length; i++) {
      const row = jsonData[i];
      if (row && row.some(cell => cell && typeof cell === 'string' && cell.includes('Name'))) {
        headerRowIndex = i;
        break;
      }
    }

    const employeeData = [];
    for (let i = headerRowIndex + 1; i < jsonData.length; i++) {
      const row = jsonData[i];
      if (row && row[0] && typeof row[0] === 'number') {
        employeeData.push({
          sn: row[0],
          name: row[1],
          position: row[2],
          grossPay: row[3] || 0,
          deduction: row[4] || 0,
          addResponsibility: row[5] || 0,
          staffUniform: row[6] || 0,
          netPay: row[7] || 0,
          remark: row[8] || ''
        });
      }
    }

    state.payrollData = employeeData;
    renderPayrollTable();
    showNotification('Payroll data loaded successfully!', 'success');
    showStatus(`Loaded ${employeeData.length} employee payroll records.`, 'success');
    
  } catch (error) {
    console.error('Error loading payroll:', error);
    showNotification('Error loading payroll file', 'error');
    showStatus('Error loading payroll file: ' + error.message, 'error');
  }
}

// Render Payroll Table
function renderPayrollTable() {
  const tbody = elements.payrollTableBody;
  tbody.innerHTML = '';
  
  if (state.payrollData.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" style="text-align: center; color: var(--text-muted);">
          No payroll data available. Please upload payroll file.
        </td>
      </tr>
    `;
    return;
  }
  
  state.payrollData.forEach(employee => {
    const row = document.createElement('tr');
    const currency = state.settings.currency === 'NGN' ? '₦' : state.settings.currency === 'USD' ? '$' : '€';
    
    row.innerHTML = `
      <td>${employee.sn}</td>
      <td><strong>${employee.name}</strong><br><small style="color: var(--text-muted);">${employee.position}</small></td>
      <td>${employee.position}</td>
      <td>${currency}${employee.grossPay.toLocaleString()}</td>
      <td>${currency}${employee.deduction.toLocaleString()}</td>
      <td>${currency}${employee.netPay.toLocaleString()}</td>
      <td><small>${employee.remark}</small></td>
    `;
    tbody.appendChild(row);
  });
}

// Utility Functions
function showStatus(message, type) {
  elements.statusMessage.textContent = message;
  elements.statusDisplay.style.display = 'block';
  elements.statusDisplay.className = `card ${type}`;
}

function showNotification(message, type) {
  elements.notificationText.textContent = message;
  elements.notification.className = `notification ${type} show`;
  
  setTimeout(() => {
    elements.notification.classList.remove('show');
  }, 3000);
}

function clearAllData() {
  // Clear file inputs
  elements.userFile.value = '';
  elements.attFiles.value = '';
  elements.payrollFile.value = '';
  
  // Clear state
  state.users = [];
  state.attendanceData = [];
  state.summaryData = [];
  state.payrollData = [];
  
  // Clear UI
  elements.totalEmployees.textContent = '0';
  elements.mostPunctual.textContent = '-';
  elements.inactiveUsers.textContent = '0';
  elements.avgAttendance.textContent = '0%';
  
  elements.statusDisplay.style.display = 'none';
  
  // Reset tables
  renderAttendanceTable();
  renderPayrollTable();
  
  showNotification('All data cleared', 'success');
}

// ENHANCED: Download CSV with new columns
function downloadCSV(filename, data, columns) {
  const esc = (v) => {
    if (v == null) return '';
    const s = String(v);
    if (/[",\n]/.test(s)) return '"' + s.replace(/"/g, '""') + '"';
    return s;
  };
  
  const header = columns.join(',');
  const lines = data.map(r => columns.map(c => esc(r[c])).join(','));
  const csv = [header, ...lines].join('\n');
  
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
}

// Event Listeners
function initEventListeners() {
  // Upload Files Button (Main functionality)
  elements.uploadFilesBtn.addEventListener('click', () => {
    switchView('dashboard');
    // Scroll to upload section
    document.querySelector('.card').scrollIntoView({ behavior: 'smooth' });
    showNotification('Select your files to upload and process', 'info');
  });
  
  // File processing
  elements.processBtn.addEventListener('click', processAttendanceData);
  elements.clearBtn.addEventListener('click', clearAllData);
  
  // Payroll
  elements.payrollFile.addEventListener('change', loadPayrollData);
  elements.generatePayrollBtn.addEventListener('click', () => {
    if (state.payrollData.length > 0) {
      downloadCSV('Generated_Payroll.csv', state.payrollData, 
        ['sn', 'name', 'position', 'grossPay', 'deduction', 'netPay', 'remark']);
      showNotification('Payroll exported successfully!', 'success');
    } else {
      showNotification('No payroll data to export', 'error');
    }
  });
  
  // Search functionality
  elements.attendanceSearch.addEventListener('input', (e) => {
    const searchTerm = e.target.value.toLowerCase();
    const rows = elements.attendanceTableBody.querySelectorAll('tr');
    
    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
  });
  
  // Modal functionality
  elements.modalClose.addEventListener('click', () => {
    elements.attendanceModal.classList.remove('show');
  });
  
  elements.attendanceModal.addEventListener('click', (e) => {
    if (e.target === elements.attendanceModal) {
      elements.attendanceModal.classList.remove('show');
    }
  });
  
  // Settings
  document.getElementById('saveSettingsBtn').addEventListener('click', () => {
    // Update settings from form
    state.settings.startTime = elements.startTime.value;
    state.settings.endTime = elements.endTime.value;
    state.settings.graceMinutes = parseInt(elements.graceMin.value);
    state.settings.workDaysCount = parseInt(elements.workDays.value);
    state.settings.absentDeduction = parseFloat(document.getElementById('absentDeduction').value);
    state.settings.lateDeduction = parseFloat(document.getElementById('lateDeduction').value);
    state.settings.currency = document.getElementById('currency').value;
    
    // Update working days
    state.settings.workingDays = [];
    const dayCheckboxes = ['workSun', 'workMon', 'workTue', 'workWed', 'workThu', 'workFri', 'workSat'];
    dayCheckboxes.forEach((id, index) => {
      if (document.getElementById(id).checked) {
        state.settings.workingDays.push(index);
      }
    });
    
    // Save to localStorage
    localStorage.setItem('attendanceSettings', JSON.stringify(state.settings));
    showNotification('Settings saved successfully!', 'success');
  });
  
  // ENHANCED: Export data with new columns
  document.getElementById('exportDataBtn').addEventListener('click', () => {
    if (state.attendanceData.length > 0) {
      downloadCSV('Attendance_Detail.csv', state.attendanceData, 
        ['UserId', 'Username', 'Date', 'Clockin', 'Clockout', 'Late', 'LateCategory', 'EarlyLeave', 'TotalHours', 'Status']);
    }
    if (state.summaryData.length > 0) {
      downloadCSV('Attendance_Summary.csv', state.summaryData, 
        ['UserId', 'Username', 'Total_Present', 'Total_Absent', 'Total_Late', 'Total_Low_Late', 'Total_EarlyLeave']);
    }
    showNotification('Data exported successfully!', 'success');
  });
  
  // Reset data
  document.getElementById('resetDataBtn').addEventListener('click', () => {
    if (confirm('Are you sure you want to reset all data? This action cannot be undone.')) {
      clearAllData();
      localStorage.removeItem('attendanceSettings');
      showNotification('All data reset successfully!', 'success');
    }
  });
  
  // Keyboard shortcuts
  document.addEventListener('keydown', (e) => {
    if (e.ctrlKey || e.metaKey) {
      switch (e.key) {
        case '1':
          e.preventDefault();
          switchView('dashboard');
          break;
        case '2':
          e.preventDefault();
          switchView('attendance');
          break;
        case '3':
          e.preventDefault();
          switchView('payroll');
          break;
        case '4':
          e.preventDefault();
          switchView('settings');
          break;
      }
    }
    // Escape key to close modal
    if (e.key === 'Escape') {
      elements.attendanceModal.classList.remove('show');
    }
  });
}

// Load saved settings
function loadSettings() {
  const saved = localStorage.getItem('attendanceSettings');
  if (saved) {
    try {
      state.settings = { ...state.settings, ...JSON.parse(saved) };
      
      // Update form fields
      elements.startTime.value = state.settings.startTime;
      elements.endTime.value = state.settings.endTime;
      elements.graceMin.value = state.settings.graceMinutes;
      elements.workDays.value = state.settings.workDaysCount;
      
      document.getElementById('absentDeduction').value = state.settings.absentDeduction;
      document.getElementById('lateDeduction').value = state.settings.lateDeduction;
      document.getElementById('currency').value = state.settings.currency;
      
      // Update working days checkboxes
      const dayCheckboxes = ['workSun', 'workMon', 'workTue', 'workWed', 'workThu', 'workFri', 'workSat'];
      dayCheckboxes.forEach((id, index) => {
        document.getElementById(id).checked = state.settings.workingDays.includes(index);
      });
      
      console.log('Settings loaded successfully');
    } catch (error) {
      console.error('Error loading settings:', error);
      showNotification('Error loading saved settings', 'error');
    }
  }
}