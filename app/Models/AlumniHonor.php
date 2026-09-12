<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlumniHonor extends Model
{
    use HasFactory;

    protected $table = 'alumni_honor';

    public $timestamps = false;

    protected $fillable = [
        'member_id',
        'graduation_year',
        'honor_quote',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
