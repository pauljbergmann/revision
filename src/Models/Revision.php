<?php

namespace Stevebauman\Revision\Models;

use Illuminate\Database\Eloquent\Model;
use Stevebauman\Revision\Traits\Revision as RevisionTrait;
use App\Models\User;

/**
 * Class Revision
 *
 * @package Stevebauman\Revision
 * @since 1.0.0
 * @version 1.6.0
 * @author Pauljbergmann
 */
class Revision extends Model
{
    use RevisionTrait;

    /**
     * The revisions table.
     *
     * @var string
     */
    protected $table = 'revisions';

    /**
     * The belongs to user relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
