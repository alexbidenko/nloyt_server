<?php

namespace App;

use App\Http\Controllers\Controller;
use App\Orders\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

/**
 * App\Employee
 *
 * @property int $id
 * @property string $phone
 * @property string $email
 * @property string $password
 * @property string $token
 * @property string|null $fullName
 * @property string|null $firstName
 * @property string|null $lastName
 * @property string|null $photo
 * @property string|null $country
 * @property string|null $position
 * @property int|null $registrationTime
 * @property int $serviceId
 * @property mixed|null $roles
 * @property bool $isAdmin
 * @property Service $service
 * @method static Builder|Employee newModelQuery()
 * @method static Builder|Employee newQuery()
 * @method static Builder|Employee query()
 * @method static Builder|Employee whereCountry($value)
 * @method static Builder|Employee whereEmail($value)
 * @method static Builder|Employee whereFirstName($value)
 * @method static Builder|Employee whereFullName($value)
 * @method static Builder|Employee whereId($value)
 * @method static Builder|Employee whereIsAdmin($value)
 * @method static Builder|Employee whereLastName($value)
 * @method static Builder|Employee wherePassword($value)
 * @method static Builder|Employee wherePhone($value)
 * @method static Builder|Employee wherePhoto($value)
 * @method static Builder|Employee wherePosition($value)
 * @method static Builder|Employee whereRegistrationTime($value)
 * @method static Builder|Employee whereRoles($value)
 * @method static Builder|Employee whereServiceId($value)
 * @method static Builder|Employee whereToken($value)
 * @mixin \Eloquent
 */
class Employee extends Model
{

    protected $table = 'employees';

    protected $primaryKey = 'id';

    protected $fillable = ['phone', 'email', 'token', 'fullName', 'firstName', 'lastName', 'photo',
        'country', 'position', 'registrationTime', 'serviceId', 'roles', 'isAdmin', 'service'];

    protected $hidden = ['password'];

    protected $guarded = [];

    public $timestamps = false;

    public function getServiceAttribute() {
        if(Service::whereId($this->serviceId)->exists()) {
            return (object) Service::whereId($this->serviceId)->first()->toArray();
        }
        return null;
    }

    /**
     * @param string $email
     * @return Builder|Model|object|null|Employee
     */
    static function getUser(string $email) {
        $user = Employee::whereEmail($email)->firstOr(function() {
            Controller::throwError(Response::HTTP_NOT_FOUND, 'User not found');
        })->append('service');
        if($user->service != null) {
            $user->service->isBusy =
                Order::query()->where('serviceId', $user->id)->where('status', 2)->exists();
        }
        return $user;
    }

    /**
     * @param string $token
     * @return Employee
     */
    static function getEmployee(string $token) {
        return Employee::whereToken($token)->firstOr(function() {
            Controller::throwError(Response::HTTP_NOT_FOUND, 'User not found');
        });
    }
}
