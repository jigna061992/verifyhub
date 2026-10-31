<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Verification extends Model
{
    protected $fillable = [
        'retailer_id', 'service_id', 'reference', 'status', 'input_masked',
        'result', 'provider_ref', 'failure_reason', 'price', 'provider_cost',
    ];

    protected function casts(): array
    {
        return [
            'result'        => 'encrypted:array',
            'price'         => 'decimal:2',
            'provider_cost' => 'decimal:2',
        ];
    }

    public function retailer(): BelongsTo
    {
        return $this->belongsTo(Retailer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(VerificationService::class, 'service_id');
    }
}
