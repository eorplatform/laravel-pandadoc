<?php

use Illuminate\Support\Facades\Route;

Route::webhooks(config('pandadoc.webhooks.url'), 'pandadoc');
