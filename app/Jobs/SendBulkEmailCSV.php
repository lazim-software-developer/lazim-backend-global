<?php
namespace App\Jobs;

use App\Models\BulkEmailManagement;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Traits\UtilsTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Mime\Part\HtmlPart;

class SendBulkEmailCSV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UtilsTrait;

    public $bulkEmailData;

    public function __construct($id)
    {
        $this->bulkEmailData = BulkEmailManagement::find($id);
        $this->configureMail($this->bulkEmailData->owner_association_id);
    }

    public function handle()
    {
        try {
            $csvFilePath = Storage::disk('s3')->url($this->bulkEmailData->file_path);
            $templateId = $this->bulkEmailData->email_template_id;

            // Read the CSV file
            $file = fopen($csvFilePath, 'r');
            $headers = fgetcsv($file); // Get the first row as headers
            $headers = array_map('trim', $headers);

            // Validate the CSV format to ensure the email column exists
            if (!in_array('email', $headers)) {
                throw new Exception('CSV does not contain an email column');
            }

            // Fetch the email template
            $template = EmailTemplate::find($templateId);
            if (!$template) {
                throw new Exception('Invalid email template.');
            }

            $this->bulkEmailData->status = "processing";
            $this->bulkEmailData->save();
            $errorCount = 0;
            // Process each row in the CSV
            while (($row = fgetcsv($file)) !== false) {
                $data = array_combine($headers, $row);
                $email = $data['email'];

                $emailLogSuccess = EmailLog::where([
                    'owner_association_id' => $template->owner_association_id,
                    'bulk_email_management_id' => $this->bulkEmailData->id,
                    'status' => 'sent',
                    'recipient_email' => $email,
                    'email_template_id' => $templateId,
                ])->exists();

                if($emailLogSuccess){
                    continue;
                }

                $content = $template->body;

                // Replace placeholders in the email template with the CSV data
                foreach ($data as $key => $value) {
                    $content = str_replace("{{" . $key . "}}", $value, $content);
                }

                // Create an email log entry
                $log = EmailLog::create([
                    'recipient_email' => $email,
                    'email_template_id' => $templateId,
                    'email_content' => $content,
                    'status' => 'queued',
                    'owner_association_id' => $template->owner_association_id,
                    'bulk_email_management_id' => $this->bulkEmailData->id
                ]);

                // Send email
                try {

                    Mail::html($content, function ($message) use ($email, $template) {
                        $message->to($email)
                        ->subject($template->subject);
                    });

                    // Update log on success
                    $log->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'error_message' => null,
                    ]);
                } catch (Exception $e) {
                    $errorCount++;
                    // Update log with error message
                    $log->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                    ]);
                }
            }

            fclose($file);

            if($errorCount === 0){
                $this->bulkEmailData->status = "success";
                $this->bulkEmailData->save();
            }

        } catch (Exception $e) {
            $this->bulkEmailData->status = "failed";
            $this->bulkEmailData->save();
            // Log any errors that occur during the job processing
            \Log::error('Error processing bulk email job: ' . $e->getMessage());
        }
    }
}
