<?php

namespace App\Models\Accounting;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OAMInvoice extends Model
{
    use HasFactory;

    protected $table = 'oam_invoices';

    protected $fillable = [
        'building_id',
        'flat_id',
        'invoice_number',
        'invoice_date',
        'invoice_status',
        'due_amount',
        'general_fund_amount',
        'reserve_fund_amount',
        'additional_charges',
        'previous_balance',
        'adjust_amount',
        'invoice_due_date',
        'invoice_pdf_link',
        'invoice_detail_link',
        'updated_by',
    ];

    // Save data to invoice audit table before updating the entry
    protected static function booted()
    {
        static::updating(function ($oamInvoice) {
            DB::transaction(function () use ($oamInvoice) {
                // Capture current data
                $originalData = $oamInvoice->getOriginal();

                // Save to audit table
                DB::table('oam_invoice_audits')->insert([
                    'oam_invoice_id' => $oamInvoice->id,
                    'data' => json_encode($originalData),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        });
    }
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }
}
