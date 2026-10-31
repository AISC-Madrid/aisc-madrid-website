<?php

namespace App\Models;

use App\Casts\YesNoBoolean;
use Database\Factories\FormSubmissionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * People who signed up through the website forms or the newsletter (the dashboard "users").
 *
 * @property int $id
 * @property string $full_name
 * @property string $email
 * @property bool $newsletter
 * @property string $unsubscribe_token
 * @property Carbon|null $submitted_at
 */
class FormSubmission extends Model
{
    /** @use HasFactory<FormSubmissionFactory> */
    use HasFactory;

    protected $table = 'form_submissions';

    const CREATED_AT = 'submitted_at';

    const UPDATED_AT = null;

    protected $fillable = [
        'full_name',
        'email',
        'newsletter',
        'unsubscribe_token',
    ];

    protected $hidden = [
        'unsubscribe_token',
    ];

    protected $casts = [
        'newsletter' => YesNoBoolean::class,
        'submitted_at' => 'datetime',
    ];

    /**
     * @param  Builder<FormSubmission>  $query
     * @return Builder<FormSubmission>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($term): void {
            $query->where('full_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    /**
     * Random token for the unsubscribe links (32 hex characters, the legacy site's format).
     */
    public static function newUnsubscribeToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Stop sending the newsletter. Returns false if they were already unsubscribed.
     */
    public function unsubscribe(): bool
    {
        if (! $this->newsletter) {
            return false;
        }

        return $this->update(['newsletter' => false]);
    }
}
