<?php

namespace App\Database;

interface EntityModelInterface {
    public function count();

    public function find(string $id = null, array $projection = []);

    public function insert(object $record);

    public function update(string $id, object $record);

    public function delete(string $id);
}