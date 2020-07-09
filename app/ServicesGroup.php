<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * App\ServicesGroup
 *
 * @property int $id
 * @property string $title
 * @property Collection $services
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServicesGroup whereTitle($value)
 * @mixin \Eloquent
 */
class ServicesGroup extends Model
{

    protected $fillable = ['title'];

    protected $guarded = [];

    protected $table = 'services_groups';
    public $timestamps = false;
    protected $primaryKey = 'id';
    public $incrementing = true;

    public function servicesTemplates() {
        return $this->hasMany('App\ServicesTemplate', 'services_group_id', 'id');
    }
}
