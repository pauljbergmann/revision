<?php

namespace Stevebauman\Revision\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

trait HasRevisionsTrait
{
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
    abstract public function revisions();

    /**
     * The current users ID for storage in revisions.
     *
     * @return int|string
     */
    abstract public function revisionUserId();

    /**
     * The trait boot method.
     *
     * @return void
     */
    public static function bootHasRevisionsTrait()
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
            if($this->isDirty($column)) {
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
        if(property_exists($this, 'revisionColumns')) {
            $this->revisionColumns = $columns;
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
        if(property_exists($this, 'revisionColumnsToAvoid')) {
            // We'll check if the property exists so we don't assign
            // a non-existent column on the revision model.
            $this->revisionColumnsToAvoid = $columns;
        }

        return $this;
    }

    /**
     * Returns the revision columns.
     *
     * @return array
     */
    protected function getRevisionColumns()
    {
        $columns = is_array($this->revisionColumns) ? $this->revisionColumns : [];

        if(isset($columns[0]) && $columns[0] === '*') {
            // If we're given a wildcard, we'll retrieve
            // all columns to create revisions on.
            $columns = Schema::getColumnListing($this->getTable());
        }

        // Filter the returned columns by the columns to avoid.
        return array_filter($columns, function($column) {
            $columnsToAvoid = is_array($this->revisionColumnsToAvoid) ?
                $this->revisionColumnsToAvoid : [];

            return ! in_array($column, $columnsToAvoid);
        });
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
            'user_id'           => $this->revisionUserId(),
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
