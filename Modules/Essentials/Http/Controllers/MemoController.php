<?php

namespace Modules\Essentials\Http\Controllers;

use App\User;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Essentials\Entities\Memo;
use Modules\Essentials\Entities\MemoRecipient;
use Modules\Essentials\Entities\MemoAttachment;
use Yajra\DataTables\Facades\DataTables;

class MemoController extends Controller
{
    protected $moduleUtil;
    protected $notificationUtil;
    protected $commonUtil;

    public function __construct(ModuleUtil $moduleUtil, NotificationUtil $notificationUtil, Util $commonUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->notificationUtil = $notificationUtil;
        $this->commonUtil = $commonUtil;
    }

    public function index(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        
        if (!$this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $memos = Memo::with(['sender', 'recipients.user', 'attachments'])
                ->where('business_id', $business_id)
                ->where(function($query) use ($user_id) {
                    $query->where('sender_id', $user_id)
                          ->orWhereHas('recipients', function($q) use ($user_id) {
                              $q->where('user_id', $user_id);
                          });
                })
                ->select('essentials_memos.*');

            return DataTables::of($memos)
                ->addColumn('action', function($memo) use ($user_id) {
                    $actions = '<div class="btn-group">';
                    $actions .= '<button class="btn btn-xs btn-primary view-memo" data-id="'.$memo->id.'"><i class="fa fa-eye"></i></button>';
                    
                    if ($memo->sender_id == $user_id) {
                        if ($memo->status == 'draft') {
                            $actions .= '<button class="btn btn-xs btn-info edit-memo" data-id="'.$memo->id.'"><i class="fa fa-edit"></i></button>';
                        }
                        $actions .= '<button class="btn btn-xs btn-danger delete-memo" data-id="'.$memo->id.'"><i class="fa fa-trash"></i></button>';
                    }
                    $actions .= '</div>';
                    return $actions;
                })
                ->editColumn('subject', function($memo) {
                    $subject = $memo->subject;
                    if ($memo->has_attachments) {
                        $subject .= ' <i class="fa fa-paperclip text-muted"></i>';
                    }
                    return $subject;
                })
                ->editColumn('sender', function($memo) {
                    return $memo->sender->getUserFullNameAttribute();
                })
                ->addColumn('recipients_count', function($memo) {
                    return $memo->recipients->count();
                })
                ->editColumn('status', function($memo) {
                    $class = $memo->status == 'sent' ? 'success' : ($memo->status == 'draft' ? 'warning' : 'default');
                    return '<span class="label label-'.$class.'">'.ucfirst($memo->status).'</span>';
                })
                ->editColumn('created_at', function($memo) {
                    return $memo->created_at->format('M d, Y H:i');
                })
                ->rawColumns(['action', 'subject', 'status'])
                ->make(true);
        }

        return view('essentials::memos.index');
    }

    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        
        if (!$this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) {
            abort(403, 'Unauthorized action.');
        }
        
        $users = User::forDropdown($business_id, false);
        
        return view('essentials::memos.create', compact('users'));
    }

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        
        if (!$this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'to_recipients' => 'required|array|min:1',
            'to_recipients.*' => 'exists:users,id',
            'cc_recipients.*' => 'exists:users,id',
            'bcc_recipients.*' => 'exists:users,id',
        ]);

        try {
            $memo = Memo::create([
                'business_id' => $business_id,
                'sender_id' => $user_id,
                'subject' => $request->subject,
                'body' => $request->body,
                'status' => $request->has('send') ? 'sent' : 'draft'
            ]);

            $this->saveRecipients($memo, $request);
            $this->saveAttachments($memo, $request);

            if ($request->has('send')) {
                $this->sendNotifications($memo);
            }

            $message = $request->has('send') ? 'Memo sent successfully' : 'Memo saved as draft';
            
            return response()->json(['success' => true, 'msg' => $message, 'memo_id' => $memo->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        
        $memo = Memo::with(['sender', 'recipients.user', 'attachments'])
                   ->where('business_id', $business_id)
                   ->findOrFail($id);
        
        if (!$this->checkMemoAccess($memo, $user_id)) {
            abort(403, 'Unauthorized access.');
        }

        $recipient = $memo->recipients()->where('user_id', $user_id)->first();
        if ($recipient && !$recipient->is_read) {
            $recipient->is_read = true;
            $recipient->read_at = now();
            $recipient->save();
        }

        activity()
            ->performedOn($memo)
            ->log('memo_viewed');

        if (request()->ajax()) {
            return view('essentials::memos.show_modal', compact('memo'));
        }

        return view('essentials::memos.show', compact('memo'));
    }

    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        
        $memo = Memo::with(['recipients.user', 'attachments'])
                   ->where('business_id', $business_id)
                   ->where('sender_id', $user_id)
                   ->where('status', 'draft')
                   ->findOrFail($id);
        
        $users = User::forDropdown($business_id, false);
        
        return response()->json([
            'success' => true,
            'memo' => $memo,
            'users' => $users
        ]);
    }

    public function update(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        
        $memo = Memo::where('business_id', $business_id)
                   ->where('sender_id', $user_id)
                   ->where('status', 'draft')
                   ->findOrFail($id);

        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'to_recipients' => 'required|array|min:1',
        ]);

        try {
            $memo->update([
                'subject' => $request->subject,
                'body' => $request->body,
                'status' => $request->has('send') ? 'sent' : 'draft'
            ]);

            $memo->recipients()->delete();
            $this->saveRecipients($memo, $request);
            $this->saveAttachments($memo, $request);

            if ($request->has('send')) {
                $this->sendNotifications($memo);
            }

            $message = $request->has('send') ? 'Memo sent successfully' : 'Memo updated successfully';
            
            return response()->json(['success' => true, 'msg' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        
        $memo = Memo::where('business_id', $business_id)
                   ->where('sender_id', $user_id)
                   ->findOrFail($id);

        try {
            foreach ($memo->attachments as $attachment) {
                Storage::delete($attachment->storage_path);
            }
            
            $memo->delete();
            
            return response()->json(['success' => true, 'msg' => 'Memo deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function downloadAttachment($memo_id, $attachment_id)
    {
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        
        $memo = Memo::where('business_id', $business_id)->findOrFail($memo_id);
        
        if (!$this->checkMemoAccess($memo, $user_id)) {
            abort(403, 'Unauthorized access.');
        }
        
        $attachment = MemoAttachment::where('memo_id', $memo_id)->findOrFail($attachment_id);
        
        activity()
            ->performedOn($memo)
            ->withProperties(['attachment_id' => $attachment_id, 'filename' => $attachment->filename])
            ->log('attachment_downloaded');
        
        return Storage::download($attachment->storage_path, $attachment->filename);
    }

    public function searchUsers(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $term = $request->get('q');
        
        $users = User::where('business_id', $business_id)
                    ->where(function($query) use ($term) {
                        $query->where('first_name', 'like', '%'.$term.'%')
                              ->orWhere('last_name', 'like', '%'.$term.'%')
                              ->orWhere('username', 'like', '%'.$term.'%');
                    })
                    ->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as text"))
                    ->limit(20)
                    ->get();

        return response()->json(['results' => $users]);
    }

    private function checkMemoAccess($memo, $user_id)
    {
        if ($memo->sender_id == $user_id) {
            return true;
        }
        
        if ($memo->recipients()->where('user_id', $user_id)->exists()) {
            return true;
        }
        
        return false;
    }

    private function saveRecipients($memo, $request)
    {
        $recipients_data = [];
        
        if ($request->has('to_recipients')) {
            foreach ($request->to_recipients as $user_id) {
                $recipients_data[] = [
                    'memo_id' => $memo->id,
                    'user_id' => $user_id,
                    'recipient_type' => 'to',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        
        if ($request->has('cc_recipients')) {
            foreach ($request->cc_recipients as $user_id) {
                $recipients_data[] = [
                    'memo_id' => $memo->id,
                    'user_id' => $user_id,
                    'recipient_type' => 'cc',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        
        if ($request->has('bcc_recipients')) {
            foreach ($request->bcc_recipients as $user_id) {
                $recipients_data[] = [
                    'memo_id' => $memo->id,
                    'user_id' => $user_id,
                    'recipient_type' => 'bcc',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        
        if (!empty($recipients_data)) {
            MemoRecipient::insert($recipients_data);
        }
    }

    private function saveAttachments($memo, $request)
    {
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    $allowed_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'txt'];
                    $extension = strtolower($file->getClientOriginalExtension());
                    
                    if (!in_array($extension, $allowed_types)) {
                        continue;
                    }
                    
                    if ($file->getSize() > 50 * 1024 * 1024) {
                        continue;
                    }
                    
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('memo_attachments', $filename);
                    
                    MemoAttachment::create([
                        'memo_id' => $memo->id,
                        'filename' => $file->getClientOriginalName(),
                        'storage_path' => $path,
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize()
                    ]);
                }
            }
        }
    }

    private function sendNotifications($memo)
    {
        $recipients = $memo->recipients()->with('user')->get();
        
        foreach ($recipients as $recipient) {
            if ($recipient->user->email) {
                try {
                    $notification_data = [
                        'subject' => 'New Memo: ' . $memo->subject,
                        'email_body' => 'You have received a new memo from ' . $memo->sender->getUserFullNameAttribute() . '.<br><br>Subject: ' . $memo->subject,
                        'sms_body' => 'New memo: ' . $memo->subject,
                        'auto_send' => 1,
                        'auto_send_sms' => 0
                    ];
                    
                    $this->notificationUtil->autoSendNotification(
                        $memo->business_id,
                        'new_memo',
                        $memo,
                        $recipient->user
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send memo notification: ' . $e->getMessage());
                }
            }
        }
    }
}
