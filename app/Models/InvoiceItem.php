<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    /**
     * Get the invoice that this item belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the fee type for this invoice item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function feeType()
    {
        return $this->belongsTo(FeeType::class);
    }
}
