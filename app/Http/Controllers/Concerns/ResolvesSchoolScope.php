<?php

namespace App\Http\Controllers\Concerns;

trait ResolvesSchoolScope
{
    protected function schoolScopeId(): int
    {
        $schoolId = request()->attributes->get('school_scope_id');

        if (! $schoolId) {
            abort(403, 'School scope is not available for this request.');
        }

        return (int) $schoolId;
    }

    protected function scopedSchool(): \App\Models\School
    {
        $school = request()->attributes->get('school_scope');

        if (! $school) {
            abort(403, 'School scope is not available for this request.');
        }

        return $school;
    }
}
