<?php

namespace App\Models\Forms;

use App\Models\Forms\NocForms;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Forms\SaleNOC;

class NocContacts extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'first_name',
        'last_name',
        'email',
        'mobile',
        'emirates_id',
        'passport_number',
        'visa_number',
        'emirates_document_url',
        'visa_document_url',
        'passport_document_url',
        'noc_form_id',
        'documents_verified_by',
        'agent_email',
        'agent_phone',
        'title_deed',
        'poa_document'
    ];

    protected $searchableFields = ['*'];

    protected $casts = [
        'allow_postupload' => 'boolean',
    ];

    public function noc()
    {
        return $this->belongsTo(SaleNOC::class);
    }
    public function nocforms()
    {
        return $this->belongsTo(NocForms::class);
    }
}
