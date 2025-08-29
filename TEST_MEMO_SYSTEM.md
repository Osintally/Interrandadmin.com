# Corporate Memos System Testing Guide

## Quick Test Script

This guide provides step-by-step instructions to test the new corporate memos system after deployment.

### Prerequisites
- Application running and accessible
- Database migrations completed
- At least 2 test users in the system
- Admin access to create test data

### Test Scenario 1: Basic Memo Composition and Sending

1. **Navigate to Memos**
   - Login as User A
   - Go to `/essentials/memos`
   - Verify page loads without errors
   - Check that "Compose Memo" button is visible

2. **Compose New Memo**
   - Click "Compose Memo" button
   - Verify modal opens with all fields:
     - To: (multi-select dropdown)
     - CC: (multi-select dropdown) 
     - BCC: (multi-select dropdown)
     - Subject: (text input)
     - Message: (rich text editor)
     - Attachments: (file upload area)

3. **Fill Memo Details**
   - Select User B in "To:" field
   - Add subject: "Test Corporate Memo"
   - Add rich text content with formatting (bold, italic, lists)
   - Upload a test PDF file (< 50MB)
   - Verify file appears in attachment preview

4. **Send Memo**
   - Click "Send" button
   - Verify success message appears
   - Check that memo appears in sent items

### Test Scenario 2: Recipient Experience

1. **Login as User B**
   - Navigate to `/essentials/memos`
   - Verify new memo appears in inbox
   - Check that attachment indicator is visible

2. **View Memo**
   - Click to open the memo
   - Verify all content displays correctly:
     - Sender information
     - Subject and body with formatting
     - Attachment with download link
   - Test attachment download
   - Verify file downloads correctly

### Test Scenario 3: Access Control

1. **Test Unauthorized Access**
   - Login as User C (not a recipient)
   - Try to access memo URL directly
   - Verify 403 Forbidden error

2. **Test File Access Control**
   - As User C, try to access attachment download URL
   - Verify 403 Forbidden error

### Test Scenario 4: Draft Functionality

1. **Create Draft**
   - Login as User A
   - Start composing memo
   - Fill partial information
   - Click "Save Draft"
   - Verify draft is saved

2. **Resume Draft**
   - Navigate away and return
   - Find draft in drafts section
   - Edit and complete memo
   - Send successfully

### Test Scenario 5: File Upload Security

1. **Test File Type Validation**
   - Try uploading .exe file
   - Verify rejection with error message
   - Try uploading allowed file types (PDF, DOC, JPG)
   - Verify acceptance

2. **Test File Size Limits**
   - Try uploading file > 50MB
   - Verify rejection with size error
   - Upload file < 50MB
   - Verify acceptance

### Test Scenario 6: Rich Text Editor

1. **Test Formatting**
   - Bold, italic, underline text
   - Create bulleted and numbered lists
   - Add links
   - Verify formatting preserved after sending

2. **Test XSS Protection**
   - Try entering `<script>alert('xss')</script>`
   - Verify script is sanitized/escaped
   - Content displays safely

### Test Scenario 7: Notifications

1. **Send Memo with Notifications**
   - Compose memo to multiple recipients
   - Send memo
   - Check that recipients receive notifications
   - Verify email notifications (if configured)

### Test Scenario 8: Mobile Responsiveness

1. **Test on Mobile Device**
   - Access memos on mobile browser
   - Verify responsive layout
   - Test compose functionality
   - Verify file upload works on mobile

### Expected Results Checklist

- [ ] Memo composition interface loads correctly
- [ ] To/CC/BCC recipient selection works
- [ ] Rich text editor functions properly
- [ ] File upload accepts valid files and rejects invalid ones
- [ ] File size limits enforced
- [ ] Sent memos appear in recipient inboxes
- [ ] Attachment download works for authorized users
- [ ] Unauthorized users get 403 errors
- [ ] Draft save/resume functionality works
- [ ] Notifications sent to recipients
- [ ] Mobile interface is responsive
- [ ] XSS protection prevents script execution
- [ ] Audit logs record all actions

### Troubleshooting Common Issues

**Issue**: TinyMCE editor not loading
- **Solution**: Check that TinyMCE assets are properly loaded in layout
- **Check**: Browser console for JavaScript errors

**Issue**: Select2 dropdowns not working
- **Solution**: Verify Select2 library is loaded
- **Check**: jQuery and Select2 initialization scripts

**Issue**: File uploads failing
- **Solution**: Check storage permissions and configuration
- **Check**: Laravel logs for upload errors

**Issue**: 500 errors on memo operations
- **Solution**: Check database migrations completed
- **Check**: Laravel logs for specific error details

**Issue**: Recipients not receiving notifications
- **Solution**: Check mail configuration in .env
- **Check**: Queue workers running if using queued notifications

### Performance Testing

1. **Large File Upload**
   - Upload 45MB file
   - Verify progress indicator works
   - Check upload completes successfully

2. **Multiple Recipients**
   - Send memo to 20+ recipients
   - Verify performance remains acceptable
   - Check notification delivery

3. **Attachment Preview**
   - Test PDF preview functionality
   - Test image preview
   - Verify preview loads quickly

### Security Verification

1. **SQL Injection Test**
   - Try entering SQL in subject: `'; DROP TABLE users; --`
   - Verify no database impact

2. **File Upload Security**
   - Try uploading PHP file renamed as .pdf
   - Verify proper MIME type detection

3. **Session Security**
   - Test session timeout behavior
   - Verify CSRF protection on forms

### Cleanup After Testing

1. **Remove Test Data**
   - Delete test memos
   - Remove test attachments
   - Clean up test user accounts

2. **Reset Configuration**
   - Restore production settings
   - Clear test notifications

---

## Automated Test Commands

If you have PHPUnit tests set up:

```bash
# Run all memo-related tests
php artisan test --filter=Memo

# Run specific test classes
php artisan test tests/Feature/MemoControllerTest.php
php artisan test tests/Unit/MemoModelTest.php

# Run with coverage
php artisan test --coverage
```

## Load Testing

For production readiness:

```bash
# Install Apache Bench (if not available)
sudo apt-get install apache2-utils

# Test memo list endpoint
ab -n 100 -c 10 http://your-domain/essentials/memos

# Test memo creation (requires authentication)
# Use tools like JMeter or Locust for authenticated requests
```

---

**Note**: This testing guide should be executed in a staging environment before production deployment. All tests should pass before considering the system ready for production use.
