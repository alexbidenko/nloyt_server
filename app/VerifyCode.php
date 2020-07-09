<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\VerifyCode
 *
 * @property int $id
 * @property int $userId
 * @property string $secret
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VerifyCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VerifyCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VerifyCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VerifyCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VerifyCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VerifyCode whereSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VerifyCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VerifyCode whereUserId($value)
 * @mixin \Eloquent
 */
class VerifyCode extends Model
{
    //
}
