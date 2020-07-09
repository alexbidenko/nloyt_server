<?php

namespace App\Orders;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Orders\OrderConclusion
 *
 * @property int $id
 * @property int $serviceId
 * @property int $orderId
 * @property int $timestamp
 * @property string $text
 * @property int $risk
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereRisk($value)
 * @mixin \Eloquent
 */
class OrderConclusion extends Model
{
    protected $fillable = [
        'serviceId', 'orderId', 'timestamp', 'text', 'risk'
    ];

    protected $table = 'order_conclusions';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
}
