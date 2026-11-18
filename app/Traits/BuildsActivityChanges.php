<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait BuildsActivityChanges
{
    /**
     * Capture the auditable attributes of a model before an operation.
     *
     * @param  array<int, string>  $except
     * @return array<string, mixed>
     */
    protected function activitySnapshot(Model $model, array $except = []): array
    {
        return Arr::except($model->getAttributes(), array_merge([
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
            'password',
            'supervisor_pin',
            'remember_token',
            'email_verified_at',
        ], $except));
    }

    /**
     * Build the changes payload for a newly created record.
     *
     * @param  array<int, string>  $except
     * @return array{attributes: array<string, mixed>, old: array<string, mixed>}
     */
    protected function activityCreatedChanges(Model $model, array $except = []): array
    {
        return [
            'attributes' => $this->activitySnapshot($model, $except),
            'old' => [],
        ];
    }

    /**
     * Build the changes payload for an updated record.
     *
     * @param  array<string, mixed>  $snapshot
     * @param  array<int, string>  $except
     * @return array{attributes: array<string, mixed>, old: array<string, mixed>}
     */
    protected function activityUpdatedChanges(Model $model, array $snapshot, array $except = []): array
    {
        $current = $this->activitySnapshot($model, $except);
        $old = [];
        $attributes = [];

        foreach ($current as $key => $value) {
            $previous = $snapshot[$key] ?? null;

            if ($previous === $value) {
                continue;
            }

            $old[$key] = $previous;
            $attributes[$key] = $value;
        }

        return [
            'attributes' => $attributes,
            'old' => $old,
        ];
    }

    /**
     * Build the changes payload for a deleted record.
     *
     * @param  array<string, mixed>  $snapshot
     * @return array{attributes: array<string, mixed>, old: array<string, mixed>}
     */
    protected function activityDeletedChanges(array $snapshot): array
    {
        return [
            'attributes' => [],
            'old' => $snapshot,
        ];
    }
}
