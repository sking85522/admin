<?php
// admin/core/jsondb.php

class JsonDB {
    private $file;

    public function __construct($file) {
        $this->file = $file;
        if (!file_exists($this->file)) {
            file_put_contents($this->file, json_encode([]));
        }
    }

    public function getAll() {
        $data = file_get_contents($this->file);
        return json_decode($data, true) ?: [];
    }

    public function getById($id) {
        $data = $this->getAll();
        foreach ($data as $item) {
            if (isset($item['id']) && $item['id'] == $id) {
                return $item;
            }
        }
        return null;
    }

    public function insert($record) {
        $data = $this->getAll();
        if (!isset($record['id'])) {
            $record['id'] = uniqid(); // Generate unique ID if none provided
        }
        $data[] = $record;
        $this->save($data);
        return $record['id'];
    }

    public function update($id, $updates) {
        $data = $this->getAll();
        $updated = false;
        foreach ($data as &$item) {
            if (isset($item['id']) && $item['id'] == $id) {
                $item = array_merge($item, $updates);
                $updated = true;
                break;
            }
        }
        if ($updated) {
            $this->save($data);
        }
        return $updated;
    }

    public function delete($id) {
        $data = $this->getAll();
        $initialCount = count($data);
        $data = array_filter($data, function($item) use ($id) {
            return isset($item['id']) && $item['id'] != $id;
        });

        if (count($data) !== $initialCount) {
             // Reindex array
             $this->save(array_values($data));
             return true;
        }
        return false;
    }

    private function save($data) {
        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT));
    }
}
