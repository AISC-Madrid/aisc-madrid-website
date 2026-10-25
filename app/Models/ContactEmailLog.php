<?php

namespace App\Models;

use Database\Factories\ContactEmailLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactEmailLog extends Model
{
    /** @use HasFactory<ContactEmailLogFactory> */
    use HasFactory;

    protected $table = 'contact_email_logs';

    public $timestamps = false;

    protected $fillable = [
        'contact_id',
        'template_name',
        'sent_at',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
