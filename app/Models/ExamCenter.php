<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamCenter extends Model
{
    protected $fillable = [
        'district_id', 'name', 'code', 'address', 'capacity',
        'invigilator_name', 'contact_phone', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity'  => 'integer',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function examForms(): HasMany
    {
        return $this->hasMany(ExamForm::class);
    }

    public function examTimetables(): HasMany
    {
        return $this->hasMany(ExamTimetable::class);
    }
}
