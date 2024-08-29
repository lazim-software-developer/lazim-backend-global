<?php

namespace App\Models\Accounting;

use App\Models\Master\Service;
use App\Models\Accounting\Budget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Budgetitem extends Model
{
    use HasFactory;
    protected $table = 'budget_items';
    protected $fillable = [
        
        'budget_id',
        'service_id',
        'budget_excl_vat',
        'vat_rate',
        'vat_amount',
        'total',
        'rate',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
