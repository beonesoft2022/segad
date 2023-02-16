<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class system_events extends Model
{
    use SoftDeletes;
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */


    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Combines Category and sub-category
     *
     * @param int $business_id
     * @return array
     */
    public static function all_system_events()
    {
        $all_system_events = system_events::get()
                                ->toArray();

        if (empty($all_system_events)) {
            return [];
        }
        $system_events = [];


        foreach ($all_system_events as $system_event) {
            $system_events[] = $system_event;
        }



        return $system_events;
    }

    /**
     * Category Dropdown
     *
     * @param int $business_id
     * @param string $type category type
     * @return array
     */
    public static function forDropdown($business_id, $type)
    {
        $categories = Category::where('business_id', $business_id)
                            ->where('parent_id', 0)
                            ->where('category_type', $type)
                            ->select(DB::raw('IF(short_code IS NOT NULL, CONCAT(name, "-", short_code), name) as name'), 'id')
                            ->orderBy('name', 'asc')
                            ->get();

        $dropdown =  $categories->pluck('name', 'id');

        return $dropdown;
    }

}
