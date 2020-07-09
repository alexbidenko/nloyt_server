<?php

namespace App;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

/**
 * App\UserDevice
 *
 * @property int $id
 * @property int $ownerId
 * @property string $pin
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $make
 * @property string|null $model
 * @property string|null $modification
 * @property string|null $type
 * @property int|null $date
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserDevice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserDevice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserDevice query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserDevice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserDevice whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserDevice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserDevice whereMake($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserDevice whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserDevice whereModification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserDevice whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserDevice wherePin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserDevice whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserDevice whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $vin
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserDevice whereVin($value)
 */
class UserDevice extends Model
{
    protected $fillable = [
        'ownerId', 'pin', 'make', 'model', 'modification', 'type', 'date'
    ];

    protected $table = 'user_devices';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    static function checkNewDevice($pin, $ownerId) {
//        if (!(is_numeric($pin) && (int) $pin >= (int) env('MIN_DEVICES_PIN') && (int) $pin < (int) env('MAX_DEVICES_PIN')))
//            Controller::throwError(Response::HTTP_CONFLICT, 'Device is not exists');
        if (UserDevice::wherePin($pin)->where('ownerId', '<>', $ownerId)->exists()) {
            Controller::throwError(Response::HTTP_FORBIDDEN, 'It\'s not your device');
        }
    }
}
