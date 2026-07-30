<?php

/*
|--------------------------------------------------------------------------
| Web Routes Loader
|--------------------------------------------------------------------------
|
| One file per domain — add files to routes/web/, don't edit this loader.
| Every routes/web/*.php file is loaded automatically in sorted order:
|
|   routes/web/app.php       Authenticated, team-scoped app routes
|   routes/web/public.php    Public (marketing/content) routes
|   routes/web/settings.php  Account & team settings routes
|
*/

$routeFiles = glob(__DIR__.'/web/*.php') ?: [];

sort($routeFiles);

foreach ($routeFiles as $routeFile) {
    require $routeFile;
}
