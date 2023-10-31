<?php

namespace App\Models\Forms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'vis_document_url',
        'passport_document_url',
        'documents_verified_url',
        'noc_form_id',
        'documents_verified_by',
    ];

    protected $searchableFields = ['*'];
    protected $casts = [
        'allow_postupload'         => 'boolean',
    ];
}
