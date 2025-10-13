<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\AttendanceLog;
use App\Models\AttendanceSetting;
use App\Models\AttendanceFileLog;
use App\Business;
use App\User;
use Carbon\Carbon;
use Exception;

class ProcessAttendanceFiles extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'attendance:process 
                            {--business_id= : Specific business ID to process}
                            {--directory=attendance-uploads : Directory to scan for files}
                            {--notify : Send email notifications}
                            {--dry-run : Preview what would be processed without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Automatically process attendance files from specified directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting automated attendance file processing...');
        
        try {
            $businessId = $this->option('business_id');
            $directory = $this->option('directory');
            $notify = $this->option('notify');
            $dryRun = $this->option('dry-run');

            if ($dryRun) {
                $this->warn('📋 DRY RUN MODE: No changes will be made to the database');
            }

            // Get businesses to process
            $businesses = $businessId ? 
                Business::where('id', $businessId)->get() : 
                Business::whereHas('attendanceSettings', function($query) {
                    $query->where('auto_process_files', true);
                })->get();

            if ($businesses->isEmpty()) {
                $this->error('❌ No businesses found for processing');
                return 1;
            }

            $this->info("📊 Processing {$businesses->count()} business(es)...");

            foreach ($businesses as $business) {
                $this->processBusinessFiles($business, $directory, $notify, $dryRun);
            }

            $this->info('✅ Automated processing completed successfully');
            return 0;

        } catch (Exception $e) {
            $this->error("❌ Error during processing: {$e->getMessage()}");
            Log::error('Attendance processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Process files for a specific business.
     */
    private function processBusinessFiles(Business $business, string $directory, bool $notify, bool $dryRun)
    {
        $this->info("🏢 Processing business: {$business->name} (ID: {$business->id})");

        $businessDirectory = "{$directory}/business_{$business->id}";
        
        // Check if directory exists
        if (!Storage::exists($businessDirectory)) {
            $this->warn("📁 Directory not found: {$businessDirectory}");
            return;
        }

        // Get all files in the directory
        $files = Storage::allFiles($businessDirectory);
        
        if (empty($files)) {
            $this->info('📄 No files found to process');
            return;
        }

        $this->info("📋 Found {count($files)} files to process");

        $processedCount = 0;
        $errorCount = 0;
        $results = [];

        foreach ($files as $file) {
            try {
                $result = $this->processSingleFile($business, $file, $dryRun);
                $results[] = $result;
                
                if ($result['success']) {
                    $processedCount++;
                    $this->line("✅ {$result['filename']}: {$result['message']}");
                } else {
                    $errorCount++;
                    $this->error("❌ {$result['filename']}: {$result['message']}");
                }
                
            } catch (Exception $e) {
                $errorCount++;
                $this->error("❌ Error processing {$file}: {$e->getMessage()}");
                Log::error("File processing error", [
                    'business_id' => $business->id,
                    'file' => $file,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Summary
        $this->info("📊 Summary for {$business->name}:");
        $this->info("   ✅ Processed: {$processedCount}");
        $this->info("   ❌ Errors: {$errorCount}");

        // Send notifications if enabled
        if ($notify && !$dryRun) {
            $this->sendProcessingNotification($business, $results);
        }

        // Archive processed files
        if (!$dryRun && $processedCount > 0) {
            $this->archiveProcessedFiles($business, $results);
        }
    }

    /**
     * Process a single file.
     */
    private function processSingleFile(Business $business, string $filePath, bool $dryRun): array
    {
        $filename = basename($filePath);
        $fileContent = Storage::get($filePath);
        $fileSize = Storage::size($filePath);
        $fileHash = hash('sha256', $fileContent);

        // Check if file was already processed
        $existingLog = AttendanceFileLog::where('business_id', $business->id)
            ->where('file_hash', $fileHash)
            ->first();

        if ($existingLog) {
            return [
                'success' => false,
                'filename' => $filename,
                'message' => 'File already processed (duplicate hash)',
                'data' => null
            ];
        }

        // Determine file type
        $fileType = $this->determineFileType($filename);
        
        if (!$fileType) {
            return [
                'success' => false,
                'filename' => $filename,
                'message' => 'Unknown file type',
                'data' => null
            ];
        }

        // Process based on file type
        try {
            $processedData = $this->parseFileContent($fileContent, $fileType);
            
            if (!$dryRun) {
                // Save file log
                $fileLog = AttendanceFileLog::create([
                    'business_id' => $business->id,
                    'uploaded_by' => 1, // System user
                    'file_type' => $fileType,
                    'original_filename' => $filename,
                    'stored_filename' => $filename,
                    'file_path' => $filePath,
                    'file_size' => $fileSize,
                    'file_hash' => $fileHash,
                    'status' => 'processing',
                    'records_processed' => 0,
                    'records_failed' => 0
                ]);

                // Process the data
                $result = $this->saveProcessedData($business, $processedData, $fileType);
                
                // Update file log
                $fileLog->update([
                    'status' => $result['success'] ? 'completed' : 'failed',
                    'records_processed' => $result['processed'] ?? 0,
                    'records_failed' => $result['failed'] ?? 0,
                    'processing_log' => $result['message'] ?? '',
                    'processed_at' => now()
                ]);

                return [
                    'success' => $result['success'],
                    'filename' => $filename,
                    'message' => $result['message'],
                    'data' => $result
                ];
            }

            return [
                'success' => true,
                'filename' => $filename,
                'message' => "Would process {$processedData['count']} records",
                'data' => $processedData
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'filename' => $filename,
                'message' => "Processing error: {$e->getMessage()}",
                'data' => null
            ];
        }
    }

    /**
     * Determine file type based on filename.
     */
    private function determineFileType(string $filename): ?string
    {
        if (stripos($filename, 'user') !== false && (str_ends_with($filename, '.dat') || str_ends_with($filename, '.txt'))) {
            return 'user_mapping';
        }
        
        if ((str_ends_with($filename, '.dat') || str_ends_with($filename, '.txt')) && stripos($filename, 'user') === false) {
            return 'attendance_data';
        }
        
        if (str_ends_with($filename, '.xlsx') || str_ends_with($filename, '.xls')) {
            return 'payroll_data';
        }

        return null;
    }

    /**
     * Parse file content based on type.
     */
    private function parseFileContent(string $content, string $fileType): array
    {
        switch ($fileType) {
            case 'user_mapping':
                return $this->parseUserMappingFile($content);
                
            case 'attendance_data':
                return $this->parseAttendanceFile($content);
                
            case 'payroll_data':
                // For Excel files, would need additional processing
                return ['count' => 0, 'data' => []];
                
            default:
                throw new Exception("Unknown file type: {$fileType}");
        }
    }

    /**
     * Parse user mapping file (.dat format).
     */
    private function parseUserMappingFile(string $content): array
    {
        // Use the same logic as the frontend parser
        $cleaned = preg_replace('/[\x00-\x1F]/', ' ', $content);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        $tokens = explode(' ', trim($cleaned));

        $names = [];
        $ids = [];
        
        foreach ($tokens as $token) {
            if (preg_match('/^[A-Za-z]+$/', $token)) {
                $names[] = $token;
            } elseif (preg_match('/^\d+$/', $token)) {
                $ids[] = $token;
            }
        }

        $users = [];
        $count = min(count($names), count($ids));
        
        for ($i = 0; $i < $count; $i++) {
            $users[] = [
                'user_id' => $ids[$i],
                'username' => $names[$i]
            ];
        }

        // Remove duplicates
        $uniqueUsers = [];
        $seen = [];
        foreach ($users as $user) {
            if (!in_array($user['user_id'], $seen)) {
                $uniqueUsers[] = $user;
                $seen[] = $user['user_id'];
            }
        }

        return [
            'count' => count($uniqueUsers),
            'data' => $uniqueUsers
        ];
    }

    /**
     * Parse attendance data file (.dat format).
     */
    private function parseAttendanceFile(string $content): array
    {
        $lines = explode("\n", $content);
        $records = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Handle concatenated timestamps
            $entries = $this->splitConcatenatedEntries($line);
            
            foreach ($entries as $entry) {
                $record = $this->parseAttendanceEntry($entry);
                if ($record) {
                    $records[] = $record;
                }
            }
        }

        return [
            'count' => count($records),
            'data' => $records
        ];
    }

    /**
     * Split concatenated timestamp entries.
     */
    private function splitConcatenatedEntries(string $line): array
    {
        if (preg_match_all('/(\d+)\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})/', $line, $matches, PREG_SET_ORDER)) {
            return array_map(function($match) {
                return $match[1] . ' ' . $match[2];
            }, $matches);
        }

        return [$line];
    }

    /**
     * Parse individual attendance entry.
     */
    private function parseAttendanceEntry(string $entry): ?array
    {
        $parts = explode(' ', $entry, 3);
        
        if (count($parts) < 2) {
            return null;
        }

        $userId = $parts[0];
        if (!preg_match('/^\d+$/', $userId)) {
            return null;
        }

        // Extract timestamp
        if (preg_match('/(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2}:\d{2})/', $entry, $matches)) {
            return [
                'user_id' => $userId,
                'timestamp' => $matches[1] . ' ' . $matches[2]
            ];
        }

        return null;
    }

    /**
     * Save processed data to database.
     */
    private function saveProcessedData(Business $business, array $data, string $fileType): array
    {
        $processed = 0;
        $failed = 0;

        DB::beginTransaction();
        
        try {
            if ($fileType === 'attendance_data') {
                // Group by user and date
                $grouped = [];
                foreach ($data['data'] as $record) {
                    $date = Carbon::parse($record['timestamp'])->format('Y-m-d');
                    $key = $record['user_id'] . '|' . $date;
                    
                    if (!isset($grouped[$key])) {
                        $grouped[$key] = [
                            'user_id' => $record['user_id'],
                            'date' => $date,
                            'clock_in' => $record['timestamp'],
                            'clock_out' => $record['timestamp']
                        ];
                    } else {
                        if ($record['timestamp'] < $grouped[$key]['clock_in']) {
                            $grouped[$key]['clock_in'] = $record['timestamp'];
                        }
                        if ($record['timestamp'] > $grouped[$key]['clock_out']) {
                            $grouped[$key]['clock_out'] = $record['timestamp'];
                        }
                    }
                }

                // Save attendance records
                $settings = AttendanceSetting::getOrCreateForBusiness($business->id);
                
                foreach ($grouped as $record) {
                    try {
                        $attendanceLog = AttendanceLog::create([
                            'business_id' => $business->id,
                            'user_id' => $record['user_id'],
                            'attendance_date' => $record['date'],
                            'clock_in' => $record['clock_in'],
                            'clock_out' => $record['clock_out'],
                            'status' => 'present'
                        ]);

                        // Calculate derived fields
                        $lateInfo = $attendanceLog->determineLateStatus(
                            $settings->start_time->format('H:i'),
                            $settings->grace_minutes
                        );
                        
                        $attendanceLog->update([
                            'late_status' => $lateInfo['late_status'],
                            'late_category' => $lateInfo['late_category'],
                            'late_minutes' => $lateInfo['late_minutes'],
                            'total_hours' => $attendanceLog->calculateTotalHours(),
                            'early_leave' => $attendanceLog->checkEarlyLeave($settings->end_time->format('H:i'))
                        ]);

                        $processed++;
                        
                    } catch (Exception $e) {
                        $failed++;
                        Log::warning('Failed to save attendance record', [
                            'business_id' => $business->id,
                            'record' => $record,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            DB::commit();
            
            return [
                'success' => true,
                'message' => "Processed {$processed} records, {$failed} failed",
                'processed' => $processed,
                'failed' => $failed
            ];

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Send processing notification email.
     */
    private function sendProcessingNotification(Business $business, array $results): void
    {
        try {
            // Get business admin email
            $adminUser = User::where('business_id', $business->id)
                ->whereHas('roles', function($query) {
                    $query->where('name', 'Admin');
                })
                ->first();

            if (!$adminUser || !$adminUser->email) {
                return;
            }

            $successCount = count(array_filter($results, fn($r) => $r['success']));
            $failedCount = count($results) - $successCount;

            $subject = "Attendance Processing Report - {$business->name}";
            $message = "Automated attendance processing completed:\n\n";
            $message .= "✅ Successfully processed: {$successCount} files\n";
            $message .= "❌ Failed: {$failedCount} files\n\n";
            
            if ($failedCount > 0) {
                $message .= "Failed files:\n";
                foreach ($results as $result) {
                    if (!$result['success']) {
                        $message .= "- {$result['filename']}: {$result['message']}\n";
                    }
                }
            }

            // Send simple text email
            Mail::raw($message, function ($mail) use ($adminUser, $subject) {
                $mail->to($adminUser->email)
                     ->subject($subject);
            });

            $this->info("📧 Notification sent to {$adminUser->email}");

        } catch (Exception $e) {
            $this->warn("📧 Failed to send notification: {$e->getMessage()}");
        }
    }

    /**
     * Archive processed files.
     */
    private function archiveProcessedFiles(Business $business, array $results): void
    {
        $archiveDir = "attendance-archives/business_{$business->id}/" . date('Y-m-d');
        
        foreach ($results as $result) {
            if ($result['success'] && isset($result['data'])) {
                $originalPath = "attendance-uploads/business_{$business->id}/" . $result['filename'];
                $archivePath = "{$archiveDir}/" . $result['filename'];
                
                try {
                    Storage::move($originalPath, $archivePath);
                    $this->line("📁 Archived: {$result['filename']}");
                } catch (Exception $e) {
                    $this->warn("📁 Failed to archive {$result['filename']}: {$e->getMessage()}");
                }
            }
        }
    }
}