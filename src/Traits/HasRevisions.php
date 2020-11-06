<?php

namespace Stevebauman\Revision\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Stevebauman\Revision\Models\Revision;
use Stevebauman\Revision\Traits\AuthenticatedUser;

/**
 * Trait HasRevisionsTrait
 *
 * @package Stevebauman\Revision
 * @version 1.3.0
 * @author Stevebauman
 * @author Pauljbergmann
 */
trait HasRevisions
{
    use AuthenticatedUser;

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    abstract public function getTable();

    /**
     * The morphMany revisions relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function revisions()
    {
        return $this->morphMany(Revision::class, 'revisionable');
    }

    /**
     * The trait boot method.
     *
     * @return void
     */
    public static function bootHasRevisions()
    {
        static::updated(function(Model $model) {
            $model->afterUpdate();
        });
    }

    /**
     * Creates a revision record on the models save event.
     *
     * @return void
     */
    public function afterUpdate()
    {
        array_map(function ($column) {
            if ($this->isDirty($column)) {
                $this->processCreateRevisionRecord(
                    $column,
                    $this->getOriginal($column),
                    $this->getAttribute($column)
                );
            }
        }, $this->getRevisionColumns());
    }

    /**
     * Returns the revision columns formatted array.
     *
     * @return null|array
     */
    public function getRevisionColumnsFormatted()
    {
        return $this->revisionColumnsFormatted;
    }

    /**
     * Returns the revision columns mean array.
     *
     * @return null|array
     */
    public function getRevisionColumnsMean()
    {
        return $this->revisionColumnsMean;
    }

    /**
     * Sets the revision columns.
     *
     * @param array $columns
     *
     * @return $this
     */
    public function setRevisionColumns(array $columns = ['*'])
    {
        if (property_exists($this, 'revisable')) {
            $this->revisable = $columns;
        }

        return $this;
    }

    /**
     * Sets the revision columns to avoid.
     *
     * @param array $columns
     *
     * @return $this
     */
    public function setRevisionColumnsToAvoid(array $columns = [])
    {
        if (property_exists($this, 'notRevisable')) {
            // We'll check if the property exists so we don't assign
            // a non-existent column on the revision model.
            $this->notRevisable = $columns;
        }

        return $this;
    }
    
    /**
     * Sets the revision columns to avoid.
     *
     * @param array $columns
     *
     * @return $this
     */
    public function setRevisionColumnsToAvoid(array $columns = [])
    {
        if (property_exists($this, 'notRevisable')) {
            // We'll check if the property exists so we don't assign
            // a non-existent column on the revision model.
            $this->notRevisable = $columns;
        }

        return $this;
    }

    protected function getRevisionColumns()
    {
        $notRevisable = is_array($this->notRevisable) ? $this->notRevisable : [];

        $revisable = is_array($this->revisable) ? $this->revisable : [];

        $columns = Schema::getColumnListing($this->getTable());

        if (count($notRevisable)) {
            return $this->filterColumns($columns, $notRevisable);
        } elseif (count($revisable)) {
            return $this->filterColumns($columns, []);
        }

        return $this->filterColumns($columns, []);
    }

    public function filterColumns(array $columns, array $attributes)
    {
        return array_filter($columns, function($column) use ($attributes) {
            // Do not revise 'updated_at' column.
            if (! $this->reviseTimestamps) {
                $attributes[] = 'updated_at';
            }

            return (! in_array($column, $attributes));
        });
    }

    /**
     * Returns the revision columns.
     *
     * @todo Clean this spaghetti code!
     *
     * @version 1.3.0
     * @return array
     */
    protected function getRevisionColumns_()
    {
        $notRevisable = is_array($this->notRevisable) ? $this->notRevisable : [];
        $revisable = is_array($this->revisable) ? $this->revisable : [];

        if (count($notRevisable)) {
            $columns = Schema::getColumnListing($this->getTable());

            return array_filter($columns, function($column) use ($notRevisable) {

                // Do not revise 'updated_at' column.
                if (! $this->reviseTimestamps) {
                    $notRevisable[] = 'updated_at';
                }

                return (! in_array($column, $notRevisable));
            });
        }

        if (count($revisable)) {
            if ($this->reviseTimestamps) {
                $revisable[] = 'updated_at';
            }

            return array_filter($revisable, function($column) {
                $notRevisable = [];
                // Do not revise 'updated_at' column.
                if (! $this->reviseTimestamps) {
                    $notRevisable[] = 'updated_at';
                }

                return (! in_array($column, $notRevisable));
            });
        }

        $columns = Schema::getColumnListing($this->getTable());

        if (! $this->reviseTimestamps) {
            $columns = array_filter($columns, function($column) {
                // Do not revise 'updated_at' column.
                $notRevisable = ['updated_at'];

                return (! in_array($column, $notRevisable));
            });
        }

        return $columns;
    }

    /**
     * Creates a new revision record.
     *
     * @param string|int $key
     * @param mixed      $old
     * @param mixed      $new
     *
     * @return Model
     */
    protected function processCreateRevisionRecord($key, $old, $new)
    {
        $attributes = [
            'revisionable_type' => self::class,
            'revisionable_id'   => $this->getKey(),
            'created_by'        => $this->getAuthenticatedUserId(),
            'key'               => $key,
            'old_value'         => $old,
            'new_value'         => $new,
        ];

        $model = $this->revisions()
            ->getRelated()
            ->newInstance()
            ->forceFill($attributes);

        $model->save();

        return $model;
    }
}
