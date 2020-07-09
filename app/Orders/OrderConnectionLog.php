<?php

namespace App\Orders;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Orders\OrderConclusion
 *
 * @property int $id
 * @property int $order_id
 * @property string $user_device_pin
 * @property string $service_device_pin
 * @property string $bridge
 * @property int $timestamp_start
 * @property int $timestamp_end
 * @property string $message
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereUserDevicePin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereServiceDevicePin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereBridge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereTimestampStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereTimestampEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereMessage($value)
 * @mixin \Eloquent
 */
class OrderConnectionLog extends Model
{
    protected $fillable = [
        'order_id', 'user_device_pin', 'service_device_pin', 'bridge', 'timestamp_start', 'timestamp_end', 'message'
    ];

    protected $table = 'order_connection_logs';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
}
