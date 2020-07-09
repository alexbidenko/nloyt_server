<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\UserPurchase
 *
 * @property int $id
 * @property int $userId
 * @property string $stripeId
 * @property float $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $data
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPurchase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPurchase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPurchase query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPurchase whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPurchase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPurchase whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPurchase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPurchase whereStripeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPurchase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPurchase whereUserId($value)
 * @mixin \Eloquent
 */
class UserPurchase extends Model
{
    //
}
