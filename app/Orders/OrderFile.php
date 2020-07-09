<?php

namespace App\Orders;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Orders\OrderFile
 *
 * @property int $id
 * @property int $serviceId
 * @property int $orderId
 * @property int $timestamp
 * @property string $filename
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereFilename($value)
 * @mixin \Eloquent
 */
class OrderFile extends Model
{
    protected $fillable = [
        'serviceId', 'orderId', 'timestamp', 'filename'
    ];

    protected $table = 'order_files';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
}
