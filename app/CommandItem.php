<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Exception;

class CommandItem extends Model
{
    public $incrementing = false;
    protected $primaryKey = array('commId', 'itemId');
    
    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }
    
    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        foreach ($this->getKeyName() as $key) {
            // UPDATE: Added isset() per devflow's comment.
            if (isset($this->$key))
                $query->where($key, '=', $this->$key);
                else
                    throw new Exception(__METHOD__ . 'Missing part of the primary key: ' . $key);
        }
        
        return $query;
    }
    
    protected static function find($id, $columns = ['*'])
    {
        $me = new self;
        $query = $me->newQuery();
        $i=0;
        foreach ($me->getKeyName() as $key) {
            $query->where($key, '=', $id[$i]);
            $i++;
        }
        return $query->first();
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'commId', 'itemId', 'amount',
    ];
}
