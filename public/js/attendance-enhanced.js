// Enhanced Attendance Management System with Laravel Backend Integration
// This file preserves the original .dat parsing logic and modern UI from Attendance & Payroll folder
// while integrating seamlessly with Laravel backend

// Import the core parsing functions from the original script (these are copied to preserve functionality)

// ORIGINAL PARSING FUNCTIONS (preserved exactly as they were)
function readFile(file) {
  return new Promise((resolve) => {
    if (!file) return resolve('');
    const fr = new FileReader();
    fr.onload = () => resolve(String(fr.result || ''));
    fr.readAsText(file);
  });
}

// Parse user.dat (robust to control chars / variable spacing) - ORIGINAL LOGIC PRESERVED
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

// ENHANCED: Parse attendance logs with robust timestamp handling - ORIGINAL LOGIC PRESERVED
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

// ENHANCED: Helper function to parse individual timestamp entries with timezone fix - ORIGINAL LOGIC PRESERVED
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

// FIXED: Create date in local timezone to prevent UTC conversion issues - ORIGINAL LOGIC PRESERVED
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

// Compute min/max per user per date - ORIGINAL LOGIC PRESERVED
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

// Build N business days from start date - ENHANCED WITH LARAVEL INTEGRATION
function buildBusinessCalendar(startDate, n) {
  const days = [];
  const d = new Date(startDate); 
  d.setHours(0, 0, 0, 0);
  
  // Use Laravel settings if available, otherwise default to Mon-Fri
  let workingDays = [1, 2, 3, 4, 5]; // Mon-Fri by default
  if (window.LaravelConfig && window.LaravelConfig.initialSettings && window.LaravelConfig.initialSettings.working_days) {
    workingDays = window.LaravelConfig.initialSettings.working_days;
  }
  
  while (days.length < n) {
    const wd = d.getDay(); // 0 Sun .. 6 Sat
    if (workingDays.includes(wd)) {
      days.push(new Date(d));
    }
    d.setDate(d.getDate() + 1);
  }
  return days;
}

// ENHANCED: Build attendance outputs with accurate time comparison - ORIGINAL LOGIC ENHANCED
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

  // ENHANCED: Build summary with enhanced late tracking - ORIGINAL LOGIC PRESERVED
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

// ENHANCED: Download CSV with new columns - ORIGINAL LOGIC PRESERVED
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

// ENHANCED LARAVEL INTEGRATION - Process Attendance Data with Backend
async function processAttendanceData() {
  try {
    showStatus('Reading files...', 'info');
    
    const userFileInput = document.getElementById('userFile');
    const attFilesInput = document.getElementById('attFiles');
    
    if (!userFileInput.files[0]) {
      throw new Error('Please select a user mapping file');
    }
    
    if (!attFilesInput.files.length) {
      throw new Error('Please select at least one attendance file');
    }

    const usersTxt = await readFile(userFileInput.files[0]);
    const attTxts = await Promise.all(Array.from(attFilesInput.files).map(readFile));
    
    const users = parseUsers(usersTxt || '');
    const att = parseAttendance(attTxts.join('\n'));
    
    if (users.length === 0) throw new Error('No users parsed from user.dat');
    if (att.length === 0) throw new Error('No attendance rows parsed');

    const startTime = document.getElementById('startTime').value || '08:00';
    const graceMin = parseInt(document.getElementById('graceMin').value || '15', 10);
    const endTime = document.getElementById('endTime').value || '16:00';
    const days = parseInt(document.getElementById('workDays').value || '21', 10);

    const { attendanceByDay, minDate } = computeDailyAttendance(att, users);
    const calendar = buildBusinessCalendar(minDate, days);
    const { detail, summary } = buildOutputs(users, attendanceByDay, calendar, startTime, graceMin, endTime);

    // Update global state (compatible with original script)
    window.state = window.state || {};
    window.state.users = users;
    window.state.attendanceData = detail;
    window.state.summaryData = summary;

    // Update UI
    updateDashboardStats();
    renderAttendanceTable();
    
    showNotification('Data processed successfully!', 'success');
    showStatus(`Processed ${detail.length} daily rows for ${summary.length} users.`, 'success');
    
    console.log('Sample processed data:', detail.slice(0, 3)); // Debug log
    
    // Send to Laravel backend if available
    if (window.LaravelConfig && window.LaravelConfig.routes.processFiles) {
      try {
        const formData = new FormData();
        formData.append('processed_data', JSON.stringify({ detail, summary, users }));
        formData.append('_token', window.LaravelConfig.csrfToken);
        
        const response = await fetch(window.LaravelConfig.routes.processFiles, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: formData
        });
        
        if (response.ok) {
          console.log('Data successfully sent to Laravel backend');
        }
      } catch (backendError) {
        console.warn('Backend sync failed:', backendError);
        // Continue with frontend-only operation
      }
    }
    
  } catch (err) {
    console.error(err);
    showNotification('Error: ' + err.message, 'error');
    showStatus('Error: ' + err.message, 'error');
  }
}

// Export functions for global use
window.processAttendanceData = processAttendanceData;
window.parseUsers = parseUsers;
window.parseAttendance = parseAttendance;
window.buildOutputs = buildOutputs;
window.downloadCSV = downloadCSV;