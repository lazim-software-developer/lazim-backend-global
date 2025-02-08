<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_email', 'bulk_email_management_id', 'email_template_id', 'status', 'error_message', 'sent_at', 'email_content', 'owner_association_id'
    ];

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
}
