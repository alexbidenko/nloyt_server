<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

/**
 * App\ServicesTemplate
 *
 * @property int $id
 * @property array|null $makes
 * @property array|null $models
 * @property array|null $gens
 * @property array|null $modifications
 * @property array|null $packages
 * @property array|null $tools
 * @property string|null $photo
 * @property float $duration
 * @property float $price
 * @property int $services_group_id
 * @property string $title
 * @property string|null $description
 * @property-read \App\ServicesGroup $servicesGroup
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesTemplate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesTemplate whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesTemplate whereGens($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesTemplate whereMakes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesTemplate whereModels($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesTemplate whereModifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesTemplate wherePackages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesTemplate wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesTemplate wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesTemplate whereServicesGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesTemplate whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesTemplate whereTools($value)
 * @mixin \Eloquent
 */
class ServicesTemplate extends Model
{

    protected $fillable = ['makes', 'models', 'gens', 'modifications', 'packages', 'tools', 'photo',
        'duration', 'price', 'services_group_id', 'title', 'description'];

    protected $guarded = [];

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

    protected $table = 'services_templates';
    public $timestamps = false;
    protected $primaryKey = 'id';
    public $incrementing = true;

    public function servicesGroup() {
        return $this->belongsTo('App\ServicesGroup', 'services_group_id', 'id');
    }

    public function servicesList() {
        return $this->belongsTo('App\ServicesList', 'services_id', 'id');
    }

    public function service() {
        return $this->hasOne('App\Service', 'id', 'service_id');
    }

    public function group() {
        return $this->hasOne('App\ServicesGroup', 'id', 'services_group_id');
    }

    public static function catalogList($page = null, $perPage = null) {
        $prepare = ServicesTemplate::query()->with('group');
        if($page == null || $perPage == null)
            return $prepare->get();
        else
            return $prepare->forPage($page, $perPage)->get();
    }

    public static function workshopsByIdFromCatalog($idFromCatalog, $page = null, $perPage = null) {
        $prepare = ServicesTemplate::query()->join('services_lists', function(JoinClause $join) use ($idFromCatalog) {
            $join->on('services_templates.id', '=', 'services_lists.services_id')->where('services_id', $idFromCatalog);
        })->with('service')->with('group');
        if($page == null || $perPage == null)
            return $prepare->get();
        else
            return $prepare->forPage($page, $perPage)->get();
    }
}
