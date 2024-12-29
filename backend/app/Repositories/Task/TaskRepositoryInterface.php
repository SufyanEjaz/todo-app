<?php

namespace App\Repositories\Task;

interface TaskRepositoryInterface
{
    public function create(array $data);
    public function find(int $id);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function toggleComplete(int $id);
    public function toggleArchiveRestore(int $id);
    public function getAll(array $filters);
}
