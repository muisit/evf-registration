<?php
 
namespace App\Console\Commands;
 
use App\Notifications\ReportNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
 
class SendGeneralNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evf:report';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send out the general notification';
 
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $notification = new ReportNotification();
        Notification::route('mail', 'webmaster@veteransfencing.eu')->notify($notification);
    }
}
