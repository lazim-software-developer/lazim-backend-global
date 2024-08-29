<?php

namespace App\Models\Forms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Forms\SaleNOC;

class NocFormSignedDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'noc_form_id',
        'document',
        'uploaded_by'
    ];

    public function nocForm()
    {
        return $this->belongsTo(SaleNOC::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
