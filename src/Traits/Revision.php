<?php

namespace Stevebauman\Revision\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait Revision
 *
 * @package Stevebauman\Revision
 * @version 1.3.0
 * @author Stevebauman
 * @author Pauljbergmann
 */
trait Revision
{
    /**
     * The belongsTo user relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    abstract public function user();

    /**
     * The revisionable morphTo relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    abstract public function revisionable();

    /**
     * Returns the user responsible for the revision.
     *
     * @return mixed
     */
    abstract public function getUserResponsible();

    /**
     * Returns the revisions column name.
     *
     * @return string
     */
    public function getColumnName()
    {
        $model = $this->revisionable;

        $column = $this->key;

        $formattedColumns = $model->getRevisionColumnsFormatted();

        if (is_array($formattedColumns) && array_key_exists($column, $formattedColumns)) {
            return $formattedColumns[$column];
        }

        return $column;
    }

    /**
     * Returns the old value of the model.
     *
     * @return mixed
     */
    public function getOldValue()
    {
        return $this->getRevisedValue('old_value');
    }

    /**
     * Returns the new value of the model.
     *
     * @return mixed
     */
    public function getNewValue()
    {
        return $this->getRevisedValue('new_value');
    }

    /**
     * Returns the revised value for the specified key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getRevisedValue($key)
    {
        $model = $this->revisionable;

        $value = $this->{$key};

        $means = $this->getColumnMeans($this->key, $model);

        // Check if the column key is inside the column means property array.
        if ($means) {
            return $this->getColumnMeansProperty($means, $model, $value);
        }

        return $value;
    }

    /**
     * Returns the keys accessor on the specified model.
     *
     * If the key does not have an accessor, it returns false.
     *
     * @param int|string $key
     * @param Model      $model
     *
     * @return string|bool
     */
    protected function getColumnMeans($key, Model $model)
    {
        $columnsMean = $model->getRevisionColumnsMean();

        if (is_array($columnsMean) && array_key_exists($key, $columnsMean)) {
            return $columnsMean[$key];
        }

        return false;
    }

    /**
     * Retrieves a relationships nested property from a column.
     *
     * @param string $key
     * @param Model  $model
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function getColumnMeansProperty($key, $model, $value)
    {
        // Explode the dot notated key.
        $attributes = explode('.', $key);

        foreach ($attributes as $attribute) {
            // If we're at the end of the attributes array,
            // we'll see if the temporary object is
            // an instance of an Eloquent Model.
            if ($attribute === end($attributes)) {
                // If the relationship model has a get mutator
                // for the current attribute, we'll run it
                // through the mutator and pass on the
                // revised value.
                if($model->hasGetMutator($attribute)) {
                    $model = $model->mutateAttribute($attribute, $value);
                } else {
                    // Looks like the relationship model doesn't
                    // have a mutator for the attribute, we'll
                    // return the models attribute.
                    $model = $model->{$attribute};
                }
            } else {
                $model = $model->{$attribute};
            }
        }

        return $model;
    }
}
