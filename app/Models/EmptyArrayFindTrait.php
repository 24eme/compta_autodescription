<?php

namespace Models;

trait EmptyArrayFindTrait
{
    public function find($filter = NULL, ?array $options = NULL, $ttl = 0)
    {
        $exists = false;
        foreach (class_parents($this) as $parent) {
            if (method_exists($parent, 'find')) {
                $exists = true;
            }
        }

        if ($exists === false) {
            throw new Exception('No find function in parent class');
        }

        $results = parent::find($filter, $options, $ttl);
        return $results ?: [];
    }
}
