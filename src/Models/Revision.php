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

    /**
     * The revisionable morphTo relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function revisionable()
    {
        return $this->morphTo();
    }

    /**
     * Returns the user responsible for the revision.
     *
     * @return mixed
     */
    public function getUserResponsible()
    {
        return $this->user;
    }

    /**
     * An alias for getUserResponsible().
     *
     * @since 1.3.0
     *
     * @uses getUserResponsible()
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->getUserResponsible();
    }
}
