<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalNotice extends Model
{
    use HasFactory;

    protected $table = 'legal_notice';

    protected $fillable = ['legalNoticeId', 'building_id', 'flat_id', 'owner_association_id', 'mollakPropertyId', 'registrationDate', 'registrationNumber',
                            'invoiceNumber','invoicePeriod', 'previousBalance', 'invoiceAmount', 'approvedLegalAmount', 'legalNoticePDF', 'isRDCCaseStart', 'isRDCCaseEnd'];

    protected $cast = ['isRDCCaseStart', 'isRDCCaseEnd'];
}
