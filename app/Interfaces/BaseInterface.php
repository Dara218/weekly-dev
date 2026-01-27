<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface BaseInterface
{
    /**
     * Retrieve all records.
     *
     * @return \Illuminate\Database\Eloquent\Collection A collection of model instances.
     */
    public function all(): Collection;
    /**
     * Find a record by ID. Fails if none found.
     *
     * @param int $id The ID of the record.
     *
     * @return mixed
     */
    public function find(int $id): mixed;

    /**
     * Create a new record.
     *
     * @param array<mixed> $data The data for the new record.
     *
     * @return \Illuminate\Database\Eloquent\Model The created model instance.
     */
    public function create(array $data): Model;

    /**
     * Update an existing record.
     *
     * @param int $id The ID of the record to update.
     * @param array<mixed> $data The updated data.
     *
     * @return \Illuminate\Database\Eloquent\Model The updated model instance.
     */
    public function update(array $data, int $id): Model;

    /**
     * Delete a record by ID.
     *
     * @param int|array<mixed> $ids The ID or an array of IDs to delete.
     *
     * @return bool True if at least one record was deleted, false otherwise.
     */
    public function delete(int|array $ids): bool;
}
