<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Service
 *
 * @property int $id
 * @property int $adminId
 * @property string $legalEntityName
 * @property string $legalEntityNumber
 * @property bool $isOfficialDealer
 * @property bool $isInHolding
 * @property string $holdingName
 * @property string $holdingSite
 * @property string $serviceName
 * @property string $serviceAddress
 * @property array $address
 * @property string $servicePhone
 * @property array $schedules
 * @property string $serviceTime
 * @property string $serviceSite
 * @property array $autoMarks
 * @property array $equipmentsAndSoftware
 * @property array $servicePhotos
 * @property string $description
 * @property int $receiversCount
 * @property float|null $latitude
 * @property float|null $longitude
 * @property boolean|null $isBusy
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereAutoMarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereEquipmentsAndSoftware($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereHoldingName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereHoldingSite($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereIsInHolding($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereIsOfficialDealer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereLegalEntityName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereLegalEntityNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereReceiversCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereServiceAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereServiceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereServicePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereServicePhotos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereServiceSite($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereServiceTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereSchedules($value)
 * @mixin \Eloquent
 * @property string|null $servicePhone2
 * @property int|null $createTime
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereCreateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Service whereServicePhone2($value)
 */
class Service extends Model
{

    protected $table = 'services';

    protected $primaryKey = 'id';

    protected $fillable = ['legalEntityName', 'legalEntityNumber', 'isOfficialDealer', 'isInHolding', 'holdingName',
        'holdingSite', 'serviceName', 'serviceAddress', 'servicePhone', 'serviceTime', 'serviceSite', 'autoMarks',
        'equipmentsAndSoftware', 'servicePhotos', 'description', 'receiversCount', 'latitude', 'longitude'];

    protected $guarded = [];

    protected $casts = [
        'autoMarks' => 'array',
        'equipmentsAndSoftware' => 'array',
        'servicePhotos' => 'array',
        'address' => 'array',
        'schedules' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public $timestamps = false;
}
