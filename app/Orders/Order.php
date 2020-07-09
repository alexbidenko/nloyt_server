<?php

namespace App\Orders;

use App\ServicesList;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Orders\Order
 *
 * @property int $id
 * @property int $devicePin
 * @property int $serviceId
 * @property int $status
 * @property string $type
 * @property int $timeStart
 * @property int $duration
 * @property bool $isStarted
 * @property string $requestCode
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array|null $files
 * @property array|null $conclusions
 * @property array $services_ids
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereDevicePin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereIsStarted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereTimeStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereRequestCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereServicesIds($value)
 * @mixin \Eloquent
 */
class Order extends Model
{
    protected $fillable = [
        'devicePin', 'serviceId', 'status', 'type', 'timeStart', 'duration', 'isStarted', 'requestCode', 'services_ids'
    ];

    protected $casts = [
        'services_ids' => 'array'
    ];

    protected $table = 'orders';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    public function files()
    {
        return $this->hasMany('App\Orders\OrderFile', 'orderId', 'id');
    }

    public function conclusions()
    {
        return $this->hasMany('App\Orders\OrderConclusion', 'orderId', 'id');
    }

    /**
     * @param $id
     * @return Order|\Illuminate\Database\Eloquent\Builder|Model|object|null
     */
    static function getFullOrder($id) {
        return Order::whereId($id)->with('files')->with('conclusions')->first();
    }

    public function device()
    {
        return $this->hasOne('App\UserDevice', 'pin', 'devicePin');
    }

    public function getServicesAttribute() {
        return ServicesList::query()
            ->whereIn('services_lists.id', $this->services_ids)
            ->join('services_templates', 'services_lists.services_id', '=', 'services_templates.id')
            ->get();
    }

    static public function getPin($pin) {
        if (substr($pin, 0, 4) == '1111' && strlen($pin) > 6)
            return substr($pin, strlen($pin) - 6, 6);
        return $pin;
    }
}
