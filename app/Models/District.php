<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class District extends Model
{
    protected $fillable = ['name', 'code'];

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }

    public function examCenters(): HasMany
    {
        return $this->hasMany(ExamCenter::class);
    }
}
