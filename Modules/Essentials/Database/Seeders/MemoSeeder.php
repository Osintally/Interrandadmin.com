<?php

namespace Modules\Essentials\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Essentials\Entities\Memo;
use Modules\Essentials\Entities\MemoRecipient;
use App\User;
use App\Business;

class MemoSeeder extends Seeder
{
    public function run()
    {
        $businesses = Business::all();
        
        foreach ($businesses as $business) {
            $users = User::where('business_id', $business->id)->limit(5)->get();
            
            if ($users->count() >= 2) {
                $sender = $users->first();
                $recipients = $users->skip(1)->take(3);
                
                $memo = Memo::create([
                    'business_id' => $business->id,
                    'sender_id' => $sender->id,
                    'subject' => 'Welcome to Corporate Memos System',
                    'body' => '<p>Dear Team,</p><p>We are excited to introduce our new Corporate Memos system. This system allows you to:</p><ul><li>Send memos to multiple recipients</li><li>Attach files and documents</li><li>Track read receipts</li><li>Save drafts for later</li></ul><p>Please explore the features and let us know your feedback.</p><p>Best regards,<br>Management</p>',
                    'status' => 'sent'
                ]);
                
                foreach ($recipients as $index => $recipient) {
                    MemoRecipient::create([
                        'memo_id' => $memo->id,
                        'user_id' => $recipient->id,
                        'recipient_type' => $index == 0 ? 'to' : ($index == 1 ? 'cc' : 'bcc'),
                        'is_read' => false
                    ]);
                }
                
                $draft_memo = Memo::create([
                    'business_id' => $business->id,
                    'sender_id' => $sender->id,
                    'subject' => 'Draft: Quarterly Review Meeting',
                    'body' => '<p>This is a draft memo about the upcoming quarterly review meeting...</p>',
                    'status' => 'draft'
                ]);
                
                MemoRecipient::create([
                    'memo_id' => $draft_memo->id,
                    'user_id' => $recipients->first()->id,
                    'recipient_type' => 'to',
                    'is_read' => false
                ]);
            }
        }
    }
}
