<?php

namespace App\Models;

use Database\Factories\NewsletterLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Newsletter emails sent to subscribers. Rows are keyed by email, not by form_submissions.id.
 *
 * @property int $id
 * @property string $email
 * @property string $template_name
 * @property Carbon|null $sent_at
 */
class NewsletterLog extends Model
{
    /** @use HasFactory<NewsletterLogFactory> */
    use HasFactory;

    protected $table = 'newsletter_logs';

    public $timestamps = false;

    protected $fillable = [
        'email',
        'template_name',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
