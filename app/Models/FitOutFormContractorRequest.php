<?php

namespace App\Models;

use App\Models\Building\Document;
use App\Models\Forms\FitOutForm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FitOutFormContractorRequest extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    
    protected $fillable = ['work_type', 'work_name', 'fit_out_form_id','status'];

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function fitOut()
    {
        return $this->belongsTo(FitOutForm::class);
    }
}
