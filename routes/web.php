<?php

use Illuminate\Support\Facades\Route;
use Mpietrucha\Cdn\Http\Controllers\CdnController;
use Mpietrucha\Cdn\Url;

Route::domain(Url::getHost())->get('/{image}', CdnController::class)->where('image', '.*');
