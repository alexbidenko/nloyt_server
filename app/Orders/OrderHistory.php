<?php

namespace App\Orders;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Orders\OrderHistory
 *
 * @property int $id
 * @property int $orderId
 * @property int $status
 * @property int $timestamp
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\OrderHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\OrderHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\OrderHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereTimestamp($value)
 * @mixin \Eloquent
 */
class OrderHistory extends Model
{
    protected $fillable = [
        'orderId', 'status', 'timestamp'
    ];

    protected $table = 'order_history';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
}
