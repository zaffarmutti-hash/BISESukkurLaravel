<?php

namespace App\Contracts;

/**
 * Marker for models owned by the board (super admin).
 *
 * Board-level records are NOT scoped to a school. When the super admin
 * creates or updates them, every school sees the change on the next request.
 */
interface BoardLevelModel
{
}
