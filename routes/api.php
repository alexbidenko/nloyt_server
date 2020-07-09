<?php

Route::middleware('cors')->group(function () {

    Route::prefix('user')->group(function () {

        /**
         * Добавление пользователя или авторизация пользователя по логину и паролю
         */
        Route::post('/', [
            'uses' => 'UserController@addOrCheckUser',
            'nocsrf' => true,
        ]);

        Route::get('/', [
            'uses' => 'UserController@getUser',
            'nocsrf' => true,
        ]);

        /**
         * Обновление данных пользователя
         */
        Route::post('/update', [
            'uses' => 'UserController@updateUser',
            'nocsrf' => true,
        ]);

        Route::post('/logout', [
            'uses' => 'UserController@allLogout',
            'nocsrf' => true,
        ]);

        /**
         * Повторная отправка СМС кода
         */
        Route::get('/phone/{id}', [
            'uses' => 'UserController@sendSecret',
            'nocsrf' => true,
        ]);

        /**
         * Подтверждение номера телефона пользователя по отправленному коду
         */
        Route::get('/confirm/{id}/{secret}', [
            'uses' => 'UserController@confirmPhone',
            'nocsrf' => true,
        ]);

        /**
         * Добавление нового устройства пользователя
         */
        Route::post('/device/{pin}', [
            'uses' => 'UserController@addDevice',
            'nocsrf' => true,
        ]);

        /**
         * Добавление нового устройства пользователя
         */
        Route::post('/device/auto/{pin}', [
            'uses' => 'UserController@addDeviceAuto',
            'nocsrf' => true,
        ]);

        Route::post('/device/update/{pinOrId}', [
            'uses' => 'UserController@updateDevice',
            'nocsrf' => true,
        ]);

        /**
         * Получение списка устройств пользователя
         */
        Route::get('/device', [
            'uses' => 'UserController@getDevice',
            'nocsrf' => true,
        ]);

        Route::post('/auto', [
            'uses' => 'UserController@addAuto',
            'nocsrf' => true,
        ]);

        Route::post('/auto/{pin}/bind/{deviceId}', [
            'uses' => 'UserController@bindAutoToDevice',
            'nocsrf' => true,
        ]);

        Route::post('/device/{pin}/activate', [
            'uses' => 'UserController@bindAutoToDevice',
            'nocsrf' => true,
        ]);

        /**
         * Получение списка устройств пользователя с пагинацией
         */
        Route::get('/device/{page}/{perPage}', [
            'uses' => 'UserController@getDevicePaginate',
            'nocsrf' => true,
        ]);

        /**
         * Получение телеметрии устройства по выбранному диапазону дат
         */
        Route::get('/device/{pin}', [
            'uses' => 'UserController@getDeviceData',
            'nocsrf' => true,
        ]);

        /**
         * Тестовое api для проверки работоспособности nexmo
         */
        Route::get('/nexmo/{phone}/{id}', [
            'uses' => 'UserController@sendSMS',
            'nocsrf' => true,
        ]);

        Route::get('/order', [
            'uses' => 'OrderController@getUserOrders',
            'nocsrf' => true
        ]);

        Route::get('/order/download/{orderId}/{filename}', 'UserOrderController@getOrderFile');

        Route::post('/order/{pin}', [
            'uses' => 'UserOrderController@addOrder',
            'nocsrf' => true
        ]);

        Route::post('/order/update/{orderId}', [
            'uses' => 'OrderController@userUpdateStatus',
            'nocsrf' => true
        ]);

        Route::get('/service/services/all', 'UserController@getFullServicesList');
        Route::get('/service/services/all/{page}/{perPage}', 'UserController@getFullServicesListPaginate');
        Route::get('/service/services/{serviceId}', 'UserController@getServicesList');

        Route::get('/catalog/workshops/{idFromCatalog}/{page}/{perPage}', 'UserController@getWorkshopsByIdFromCatalogPaginate');
        Route::get('/catalog/workshops/{idFromCatalog}', 'UserController@getWorkshopsByIdFromCatalog');
        Route::get('/catalog/list/{page}/{perPage}', 'UserController@getCatalogListPaginate');
        Route::get('/catalog/list', 'UserController@getCatalogList');

        Route::get('/service/freeTime/{serviceId}', [
            'uses' => 'UserController@getServiceFreeTime',
            'nocsrf' => true
        ]);

        Route::get('/service', [
            'uses' => 'UserController@getServices',
            'nocsrf' => true
        ]);

        Route::get('/service/{page}/{perPage}', [
            'uses' => 'UserController@getServicesPaginate',
            'nocsrf' => true
        ]);

        Route::post('/avatar', [
            'uses' => 'UserController@addAvatar',
            'nocsrf' => true
        ]);

        Route::post('/purchase', [
            'uses' => 'StripeController@addPurchase',
            'nocsrf' => true,
        ]);
    });

    Route::prefix('service')->group(function () {

        /**
         * Добавление нового сервисного центра и привязка его к администратору
         */
        Route::post('/', [
            'uses' => 'EmployeeController@addService',
            'nocsrf' => true,
        ]);

        /**
         * Список сервисных центров (тест)
         */
        Route::get('/', [
            'uses' => 'ServiceController@getServices',
            'nocsrf' => true,
        ]);

        /**
         * Добавление нового девайса сервисным центром
         */
        Route::get('/device/{pin}', [
            'uses' => 'ServiceController@addDevice',
            'nocsrf' => true,
        ]);

        /**
         * Получение последней телеметрии с устройств, обслуживаемых сервисным центром
         */
        Route::get('/device/{page}/{perPage}', [
            'uses' => 'ServiceController@getDevicesStatusPaginate',
            'nocsrf' => true,
        ]);

        /**
         * Получение последней телеметрии с устройств, обслуживаемых сервисным центром
         */
        Route::get('/device', [
            'uses' => 'ServiceController@getDevicesStatus',
            'nocsrf' => true,
        ]);

        /**
         * Добавление новых сотрудников
         */
        Route::post('/employees/{serviceId}', [
            'uses' => 'ServiceController@addEmployees',
            'nocsrf' => true,
        ]);

        /**
         * Получение файла конфигурации для панели управления сервисным центром
         */
        Route::get('/config', [
            'uses' => 'ServiceController@getServiceConfig',
            'nocsrf' => true,
        ]);

        Route::get('/order/{orderId}/error', [
            'uses' => 'OrderController@getOrderErrors',
            'nocsrf' => true
        ]);

        Route::get('/order', [
            'uses' => 'OrderController@getServiceOrders',
            'nocsrf' => true
        ]);

        Route::get('/order/page/{page}/{perPage}', [
            'uses' => 'OrderController@getServiceOrdersPaginate',
            'nocsrf' => true
        ]);

        Route::get('/order/{orderId}', [
            'uses' => 'OrderController@getOrderById',
            'nocsrf' => true
        ]);

        Route::put('/order/{orderId}', [
            'uses' => 'OrderController@serviceUpdateStatus',
            'nocsrf' => true
        ]);

        Route::get('/counts', [
            'uses' => 'ServiceController@getAllDataCounts',
            'nocsrf' => true
        ]);

        Route::post('/order/file/{orderId}', [
            'uses' => 'OrderController@addFilesToOrder',
            'nocsrf' => true,
        ]);

        Route::get('/order/file/{filename}', [
            'uses' => 'OrderController@getOrderFile',
            'nocsrf' => true,
        ]);

        Route::delete('/order/file/{filename}', [
            'uses' => 'OrderController@deleteOrderFile',
            'nocsrf' => true,
        ]);

        Route::post('/order/conclusion/{orderId}', [
            'uses' => 'OrderController@addConclusionToOrder',
            'nocsrf' => true,
        ]);

        Route::put('/order/conclusion/{conclusionId}', [
            'uses' => 'OrderController@updateConclusions',
            'nocsrf' => true,
        ]);

        Route::delete('/order/conclusion/{conclusionId}', [
            'uses' => 'OrderController@deleteConclusions',
            'nocsrf' => true,
        ]);
    });

    Route::prefix('employee')->group(function () {

        /**
         * Регистрация администратора сервисного центра
         */
        Route::post('/', [
            'uses' => 'EmployeeController@addAdmin',
            'nocsrf' => true,
        ]);

        /**
         * Вход
         */
        Route::post('/login', [
            'uses' => 'EmployeeController@loginAdmin',
            'nocsrf' => true,
        ]);

        Route::post('/phoneCode', 'EmployeeController@sendPhoneCode');

        Route::post('/password', 'EmployeeController@setPassword');

        /**
         * Добавление заказа на покупку устройств
         */
        Route::post('/purchase', [
            'uses' => 'ServiceController@addPurchase',
            'nocsrf' => true,
        ]);
    });

    Route::prefix('employee/old')->group(function () {

        /**
         * Изменение пароля сотрудником сервисного центра
         */
        Route::post('/password', [
            'uses' => 'ServiceController@employeeChangePassword',
            'nocsrf' => true,
        ]);
    });

    /**
     * Получение списка стран
     */
    Route::get('/countries', function () {
        return response()->json([
            'data' => [
                [
                    'title' => 'Россия'
                ],
                [
                    'title' => 'USA'
                ],
                [
                    'title' => 'France'
                ]
            ],
            'success' => true,
            'error' => []
        ]);
    });

    Route::prefix('order')->group(function () {

        Route::get('/types', [
            'uses' => 'OrderController@getTypes',
            'nocsrf' => true
        ]);

        Route::get('/statuses', [
            'uses' => 'OrderController@getStatuses',
            'nocsrf' => true
        ]);

        Route::get('/receipt/{orderId}', 'OrderController@getReceipt');
    });

    Route::prefix('check')->group(function () {

        Route::get('/{table}', [
            'uses' => 'CheckController@getTable',
            'nocsrf' => true,
        ]);

        Route::delete('/{table}', [
            'uses' => 'CheckController@deleteTable',
            'nocsrf' => true,
        ]);

        Route::post('/{table}', [
            'uses' => 'CheckController@insertTable',
            'nocsrf' => true,
        ]);

        Route::delete('/table/{table}', [
            'uses' => 'CheckController@deleteFullTable',
            'nocsrf' => true,
        ]);
    });

    Route::prefix('data')->group(function() {

        Route::get('/auto', [
            'uses' => 'DataController@getAutos',
            'nocsrf' => true,
        ]);

        Route::get('/auto/{text}', [
            'uses' => 'DataController@getAutosByText',
            'nocsrf' => true,
        ]);

        Route::get('/mark', [
            'uses' => 'DataController@getMarks',
            'nocsrf' => true,
        ]);

        Route::get('/model', [
            'uses' => 'DataController@getModels',
            'nocsrf' => true,
        ]);

        Route::get('/model/{id}', [
            'uses' => 'DataController@getModelsById',
            'nocsrf' => true,
        ]);

        Route::get('/modification', [
            'uses' => 'DataController@getModifications',
            'nocsrf' => true,
        ]);

        Route::get('/modification/{id}', [
            'uses' => 'DataController@getModificationsById',
            'nocsrf' => true,
        ]);

        Route::get('/address/{address}', [
            'uses' => 'DataController@getLocationByAddress',
            'nocsrf' => true,
        ]);

        Route::get('/terms', function() {
            return view('terms');
        });

        Route::get('/policy', function() {
            return view('policy');
        });
    });

    Route::prefix('stripe')->group(function() {

        Route::post('/endpoint', [
            'uses' => 'StripeController@stripeEndpoint',
            'nocsrf' => true,
        ]);
    });

    Route::prefix('study')->group(function() {

        Route::post('/authorization', [
            'uses' => 'StudyController@authorization',
            'nocsrf' => true,
        ]);

        Route::get('/message', [
            'uses' => 'StudyController@getLastMessage',
            'nocsrf' => true,
        ]);

        Route::post('/message', [
            'uses' => 'StudyController@sendMessage',
            'nocsrf' => true,
        ]);

        Route::get('/entities', [
            'uses' => 'StudyController@getEntities',
            'nocsrf' => true,
        ]);
    });

    Route::get('/admin', function() { return view('admin'); });
    Route::get('/device/admin', function() { return view('device-admin'); });

    Route::post('/device/send_message', 'DeviceTesting@sendMessage');
});
