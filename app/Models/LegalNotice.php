<?php

namespace App\Models;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalNotice extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $table = 'legal_notice';

    protected $fillable = ['legalNoticeId', 'building_id', 'flat_id', 'owner_association_id', 'mollakPropertyId', 'registrationDate', 'registrationNumber',
                            'invoiceNumber','due_date','case_status','case_number','case_type',
                            'invoicePeriod', 'previousBalance', 'invoiceAmount', 'approvedLegalAmount', 'legalNoticePDF', 'isRDCCaseStart', 'isRDCCaseEnd'];

    protected $cast = ['isRDCCaseStart', 'isRDCCaseEnd'];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }

    public function building() {
        return $this->belongsTo(Building::class);
    }

    public function flat(){
        return $this->belongsTo(Flat::class);
    }
}
