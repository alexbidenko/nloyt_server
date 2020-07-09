<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ServiceDevice
 *
 * @property int $id
 * @property int $service_id
 * @property string $pin
 * @property boolean $is_busy
 * @property string|null $connected_to
 * @property string|null $active_bridge
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceDevice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceDevice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceDevice query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceDevice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceDevice whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceDevice wherePin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceDevice whereIsBusy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceDevice whereConnectedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceDevice whereActiveBridge($value)
 * @mixin \Eloquent
 */
class ServiceDevice extends Model
{
    protected $fillable = [
        'service_id', 'pin', 'is_busy', 'connected_to', 'active_bridge'
    ];

    protected $table = 'service_devices';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
}
