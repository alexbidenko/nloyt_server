<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

/**
 * App\ServicesList
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesList whereServiceId($value)
 * @property int $id
 * @property int $service_id
 * @property int $services_id
 * @property-read \App\ServicesTemplate $servicesTemplate
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesList query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesList whereServicesId($value)
 * @mixin \Eloquent
 */

class ServicesList extends Model
{

    protected $fillable = ['service_id'];

    protected $guarded = [];

    protected $table = 'services_lists';
    public $timestamps = false;
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $casts = [
        'makes' => 'array',
        'models' => 'array',
        'gens' => 'array',
        'modifications' => 'array',
        'packages' => 'array',
        'tools' => 'array',
        'duration' => 'float',
        'price' => 'float',
    ];

    function servicesTemplate() {
        return $this->hasOne('App\ServicesTemplate', 'services_id');
    }

    static function getServicesListByService(int $serviceId) {
//        return ServicesGroup::with(['servicesTemplates' => function(Builder $query) use($serviceId) {
//                return $query->whereIn('id', ServicesList::whereServiceId($serviceId)->pluck('services_id'))->get();
//            }])->get();
        return ServicesGroup::all()->map(function(ServicesGroup $group) use ($serviceId) {
            $group = $group->toArray();
            $group['services'] = ServicesTemplate::query()->join('services_lists', function(JoinClause $join) use ($serviceId) {
                $join->on('services_templates.id', '=', 'services_lists.services_id')->where('services_lists.service_id', $serviceId);
            })->get();
            return (object) $group;
        })->filter(function($group) use ($serviceId) {
            if(count($group->services) > 0) {
                return true;
            } else return false;
        })->all();
    }
}
