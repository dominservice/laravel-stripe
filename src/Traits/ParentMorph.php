<?php

namespace Dominservice\LaraStripe\Traits;

trait ParentMorph
{
    public function scopeWhereParent($query, $parent)
    {
        return $query->where(\DB::getTablePrefix() . $this->getTable() . '.parent_type', $parent->getMorphClass())
            ->where(\DB::getTablePrefix() . $this->getTable() . '.parent_id', $parent->{$parent->getKeyName()});
    }
    public function scopeWhereUlidParent($query, $parent)
    {
        return $query->where(\DB::getTablePrefix() . $this->getTable() . '.ulid_parent_type', $parent->getMorphClass())
            ->where(\DB::getTablePrefix() . $this->getTable() . '.ulid_parent_id', $parent->{$parent->getKeyName()});
    }
    public function scopeWhereUuidParent($query, $parent)
    {
        return $query->where(\DB::getTablePrefix() . $this->getTable() . '.uuid_parent_type', $parent->getMorphClass())
            ->where(\DB::getTablePrefix() . $this->getTable() . '.uuid_parent_id', $parent->{$parent->getKeyName()});
    }
}
