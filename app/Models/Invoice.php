<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'invoice_no',
        'student_id',
        'total_amount',
        'due_date',
        'status',
        'stripe_invoice_id',
        'stripe_hosted_invoice_url',
        'billing_period',
        'reminded_at',
        'consolidated_into_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reminded_at' => 'datetime',
    ];

    /**
     * Get the student associated with this invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the items for this invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the payments for this invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * The combined invoice that replaced this overdue invoice, if any.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function consolidatedInto()
    {
        return $this->belongsTo(self::class, 'consolidated_into_id');
    }

    /**
     * Overdue invoices that were replaced by this combined invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function consolidatedFrom()
    {
        return $this->hasMany(self::class, 'consolidated_into_id');
    }
}
