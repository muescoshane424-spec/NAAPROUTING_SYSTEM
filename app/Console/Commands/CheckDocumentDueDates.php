<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Document;
use App\Models\User;
use App\Notifications\DocumentDueNotification;
use Carbon\Carbon;

class CheckDocumentDueDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:check-due-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for documents approaching due date and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking document due dates...');

        // Get documents due in the next 3 days
        $upcomingDue = Document::where('due_date', '>=', Carbon::now())
                               ->where('due_date', '<=', Carbon::now()->addDays(3))
                               ->get();

        foreach ($upcomingDue as $document) {
            // Notify the uploader or admin
            $user = User::find($document->uploaded_by);
            if ($user) {
                $user->notify(new DocumentDueNotification($document));
                $this->info("Notification sent for document: {$document->title}");
            }
        }

        // Mark overdue documents
        $overdue = Document::where('due_date', '<', Carbon::now())
                           ->where('status', '!=', 'Overdue')
                           ->update(['status' => 'Overdue']);

        $this->info("Marked {$overdue} documents as overdue.");
    }
}