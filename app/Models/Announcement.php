<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'sent_by', 'title', 'body', 'type',
        'target_scope', 'district_id', 'school_id',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function targetLabel(): string
    {
        return match ($this->target_scope) {
            'district' => $this->district?->name ?? 'District',
            'school'   => $this->school?->name ?? 'School',
            default    => 'All Schools',
        };
    }
}
