<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmeData extends Model
{
    use HasFactory;

    protected $table = 'sme_datas';

    protected $fillable = [
        'data_id',
        'network',
        'plan_type',
        'amount',
        'size',
        'validity',
        'status',
    ];

    /**
     * Calculate final price for a specific user role.
     */
    public function calculatePriceForRole($role)
    {
        $service = Service::where('name', 'SME Data')->first();
        if (!$service) return (float)$this->amount;

        $network = strtoupper($this->network);
        $fieldCode = null;

        if (str_contains($network, 'MTN')) $fieldCode = 'SME01';
        elseif (str_contains($network, 'AIRTEL')) $fieldCode = 'SME02';
        elseif (str_contains($network, 'GLO')) $fieldCode = 'SME03';
        elseif (str_contains($network, '9MOBILE') || str_contains($network, 'ETISALAT')) $fieldCode = 'SME04';
        
        $field = $service->fields()->where('field_code', $fieldCode)->first();
        
        $roleFee = $field ? (float)$field->getPriceForUserType($role) : 0;

        return (float)$this->amount + $roleFee;
    }
}
