<?php

namespace App\Orders;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Orders\OrderReceipt
 *
 * @property int $id
 * @property int $order_id
 * @property int $employee_id
 * @property int $create_time
 * @property boolean $is_approved
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereCreateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Orders\Order whereIsApproved($value)
 * @mixin \Eloquent
 */
class OrderReceipt extends Model
{
    protected $fillable = [
        'order_id', 'employee_id', 'create_time', 'is_approved'
    ];

    protected $table = 'order_receipts';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
}
