<!DOCTYPE html>
<html lang="{{$language}}">
<head>
    <title>Act receipt</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

    <style type="text/css">
        .header-container {
            height: 35px;
            position: relative;
            font-family: Roboto, sans-serif;
            font-style: normal;
            font-weight: normal;
            font-size: 12px;
            line-height: 14px;
            text-align: center;
            letter-spacing: 0.01em;
            color: #AAAAAA;
        }
        .request-code {
            position: absolute;
            top: 6px;
            left: 0;
        }
        .request-date {
            position: absolute;
            top: 6px;
            right: 0;
        }
        .collapse-section {
            border-top: 1px dashed #aaa;
        }
        .section-header-container {
            height: 39px;
            padding-top: 9px;
            position: relative;
        }
        .section-header {
            font-family: Roboto, sans-serif;
            font-style: normal;
            font-weight: bold;
            font-size: 16px;
            line-height: 19px;
            color: #000000;
            display: block;
            padding-bottom: 4px;
            padding-top: 6px;
        }
        .section-header:hover {
            color: black;
            text-decoration: none;
        }
        .section-body-container {
            padding-bottom: 11px;
        }
        .section-body {
            font-family: Roboto, sans-serif;
            font-style: normal;
            font-weight: normal;
            font-size: 12px;
            line-height: 16px;
            color: #000000;
            padding-bottom: 4px;
            display: block;
        }
        .expand-more {
            width: 18px;
            height: 7px;
            position: absolute;
            top: 18px;
            right: 0;
            transition: all .3s;
        }
        .collapsed ~ .expand-more {
            transform: rotate(180deg);
            cursor: pointer;
        }
        .item-container {
            position: relative;
            padding-top: 6px;
            margin-bottom: 2px;
        }
        .item-point {
            font-family: Roboto, sans-serif;
            font-style: normal;
            font-weight: normal;
            font-size: 14px;
            line-height: 18px;
            color: #000000;
            position: absolute;
            display: block;
            width: fit-content;
            top: 6px;
            left: 0;
        }
        .item-text {
            font-family: Roboto, sans-serif;
            font-style: normal;
            font-weight: normal;
            font-size: 14px;
            line-height: 18px;
            padding-left: 22px;
            padding-right: 59px;
            color: #000000;
            display: block;
            padding-bottom: 9px;
            margin: 0;
        }
        .item-price {
            font-family: Roboto, sans-serif;
            font-style: normal;
            font-weight: 900;
            font-size: 14px;
            line-height: 15px;
            color: #000000;
            position: absolute;
            top: 6px;
            right: 0;
            width: fit-content;
        }
        .item-divider {
            margin-left: 22px;
            width: calc(100% - 22px);
            background-color: #aaa;
            height: 1px;
        }
        .total-container {
            padding-top: 5px;
            position: relative;
        }
        .total-price {
            position: absolute;
            font-family: Roboto, sans-serif;
            font-style: normal;
            font-weight: 900;
            font-size: 16px;
            line-height: 18px;
            text-align: right;
            top: 11px;
            right: 0;
            color: #000000;
        }
        .total-time-container {
            position: relative;
        }
        .total-time-title {
            font-family: Roboto, sans-serif;
            font-style: normal;
            font-weight: normal;
            font-size: 12px;
            line-height: 14px;
            color: #AAAAAA;
        }
        .total-time-value {
            font-family: Roboto, sans-serif;
            font-style: normal;
            font-weight: normal;
            font-size: 12px;
            line-height: 14px;
            text-align: right;
            color: #AAAAAA;
            position: absolute;
            top: 0;
            right: 0;
        }
        .risk-title {
            font-family: Roboto, sans-serif;
            font-style: normal;
            font-weight: normal;
            font-size: 12px;
            line-height: 13px;
            text-align: right;
            color: #AAAAAA;
            top: 15px;
            right: 0;
            position: absolute;
            width: fit-content;
        }
        .item-risk {
            font-family: Roboto, sans-serif;
            font-style: normal;
            font-weight: 900;
            font-size: 14px;
            line-height: 15px;
            width: fit-content;
            position: absolute;
            top: 6px;
            right: 0;
        }
        .item-file {
            color: #0054B9;
        }
        .item-file:hover {
            color: #0054B9;
        }
        .item-file-size {
            font-family: Roboto, sans-serif;
            font-size: 14px;
            line-height: 15px;
            width: fit-content;
            position: absolute;
            top: 6px;
            right: 0;
            font-style: normal;
            font-weight: normal;
            color: #AAAAAA;
        }
    </style>
    <script type="text/javascript">
        const downloadFile = (filename) => {
            fetch(`${location.origin}{{$userType == 'user' ? '/api/user/order/download/'.$order->id.'/${filename}' : '/api/service/order/file/${filename}'}}`, {
                method: 'GET',
                headers: new Headers({
                    Authorization: '{{$token}}'
                })
            })
                .then(response => response.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                });
        };
    </script>
</head>
<body>

<div class="container" style="padding: 16px;">
    <div>
        <div class="header-container">
            <span class="request-code">№{{$order->requestCode}}</span>
            <span class="request-date">
                @if (count($history) > 0)
                    {{date('H:i d/m/y', $history[count($history) - 1]['timestamp'])}}
                @else
                    {{date('H:i d/m/y')}}
                @endif
            </span>
        </div>
        <div class="collapse-section">
            <div class="section-header-container">
                <a class="section-header" role="button" data-toggle="collapse" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    {{$service->serviceName}}
                </a>
                <svg width="20" height="9" viewBox="0 0 20 9" fill="none" xmlns="http://www.w3.org/2000/svg" class="expand-more">
                    <path d="M1 8L10 1L19 8" stroke="black"/>
                </svg>
            </div>

            <div id="collapseOne" class="collapse show section-body-container" aria-labelledby="headingOne">
                <div class="section-body">{{$service->serviceAddress}}</div>
                <div class="section-header">{{$employee->fullName ?? $employee->firstName.' '.$employee->lastName}}</div>
                <div class="section-body">{{$employee->position ?? 'Администратор'}}</div>
            </div>
        </div>
        <div class="collapse-section">
            <div class="section-header-container">
                <a class="section-header" role="button" data-toggle="collapse" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    {{$device->make}} {{$device->model}} [{{$device->type}}] {{$device->year}}
                </a>
                <svg width="20" height="9" viewBox="0 0 20 9" fill="none" xmlns="http://www.w3.org/2000/svg" class="expand-more">
                    <path d="M1 8L10 1L19 8" stroke="black"/>
                </svg>
            </div>
            <div id="collapseTwo" class="collapse show section-body-container" aria-labelledby="headingTwo">
                <div class="section-body">GLC300 4MATIC 2.0 245 hp, Sport Plus</div>
                <div class="section-body">VIN: {{$device->vin}}</div>
                <div class="section-body">№: CALIFORNIA 5EDM945</div>
                <div class="section-header">{{$user->firstName}} {{$user->lastName}}</div>
                <div class="section-body">{{$user->phone}}</div>
            </div>
        </div>
        <div class="collapse-section">
            <div class="section-header-container">
                <a class="section-header" role="button" data-toggle="collapse" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    @lang('messages.services')
                </a>
                <svg width="20" height="9" viewBox="0 0 20 9" fill="none" xmlns="http://www.w3.org/2000/svg" class="expand-more">
                    <path d="M1 8L10 1L19 8" stroke="black"/>
                </svg>
            </div>
            <div id="collapseThree" class="collapse show section-body-container" aria-labelledby="headingThree">
                <div class="section-body">
                    <div class="item-container">
                        <span class="item-point">1</span>
                        <p class="item-text">Полноценная компьютерная диагностика</p>
                        <span class="item-price">$30</span>
                        <div class="item-divider"></div>
                    </div>
                    <div class="total-container">
                        <div class="section-header">@lang('messages.total_amount')</div>
                        <div class="total-price">$30</div>
                        <div class="total-time-container">
                            <div class="total-time-title">@lang('messages.time_consumed')</div>
                            <div class="total-time-value">{{(int) ($order->duration / 60)}} min</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(count($order->conclusions) > 0)
            <div class="collapse-section">
                <div class="section-header-container">
                    <a class="section-header" role="button" data-toggle="collapse" href="#collapseFour" aria-expanded="true" aria-controls="collapseFour">
                        @lang('messages.conclusion')
                    </a>
                    <div class="risk-title">@lang('messages.risk_level')</div>
                </div>

                <div id="collapseFour" class="collapse show section-body-container">
                    @foreach($order->conclusions as $key => $conclusion)
                        <div class="item-container">
                            <span class="item-point">{{$key + 1}}</span>
                            <p class="item-text">{{$conclusion->text}}</p>
                            <span class="item-risk">
                                @switch($conclusion->risk)
                                    @case(1)
                                        <span style="color: #6BA30F;">@lang('messages.low')</span>
                                        @break
                                    @case(2)
                                        <span style="color: yellow;">@lang('messages.middle')</span>
                                        @break
                                    @case(3)
                                        <span style="color: #FF0000;">@lang('messages.high')</span>
                                        @break
                                @endswitch
                            </span>
                        </div>
                        @if($key < count($order->conclusions) - 1)
                            <div class="item-divider"></div>
                        @endif
                    @endforeach
                    <div style="height: 8px;"></div>
                </div>
            </div>
        @endif
        @if(count($order->files) > 0)
            <div class="collapse-section">
                <div class="section-header-container">
                    <a class="section-header" role="button" data-toggle="collapse" href="#collapseFour" aria-expanded="true" aria-controls="collapseFour">
                        @lang('messages.attachments')
                    </a>
                </div>

                <div id="collapseFour" class="collapse show section-body-container">
                    @foreach($order->files as $key => $file)
                        <div class="item-container">
                            <span class="item-point">{{$key + 1}}</span>
                            <a class="item-text item-file" id="downloadFile-{{$file->id}}" href="#downloadFile-{{$file->id}}" onclick="downloadFile('{{$file->filename}}')">{{$file->filename}}</a>
                            <span class="item-file-size">
                                <?php
                                $size = Storage::disk('private_service')
                                    ->size('s'.$service->id.'/order_files/'.$file->filename);
                                if($size > 1024 * 1024 * 1024) {
                                    echo round($size / 1024 / 1024 / 1024, 1).' Gb';
                                } elseif($size > 1024 * 1024) {
                                    echo round($size / 1024 / 1024, 1).' Mb';
                                } elseif($size > 1024) {
                                    echo round($size / 1024, 1).' Kb';
                                } else {
                                    echo $size.' Bytes';
                                }
                                ?>
                            </span>
                        </div>
                        @if($key < count($order->files) - 1)
                            <div class="item-divider"></div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
        @if(count($logs) > 0)
            <div class="collapse-section">
                <div class="section-header-container">
                    <a class="section-header" role="button" data-toggle="collapse" href="#collapseFour" aria-expanded="true" aria-controls="collapseFour">
                        @lang('messages.logs')
                    </a>
                </div>

                <div id="collapseFour" class="collapse show section-body-container">
                    @foreach($logs as $key => $log)
                        <div class="item-container">
                            <span class="item-point">{{$key + 1}}</span>
                            <p class="item-text">{{$log->message}}</p>
                        </div>
                        @if($key < count($logs) - 1)
                            <div class="item-divider"></div>
                        @endif
                    @endforeach
                    <div style="height: 8px;"></div>
                </div>
            </div>
        @endif
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

</body>
</html>
