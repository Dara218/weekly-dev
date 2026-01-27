<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeType extends Model
{
    /**
     * Get the invoice items associated with this fee type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
