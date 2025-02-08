<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkEmailManagement extends Model
{
    use HasFactory;
    protected $table = "bulk_email_managements";

    protected $fillable = ['title', 'email_template_id', 'file_path', 'owner_association_id', 'status'];

    public function emailLog()
    {
        return $this->hasMany(EmailLog::class);
    }

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
}
