<?php

namespace TribuApi\Core;

use TribuApi\Rest\UpdatesController;
use TribuApi\Updates\UpdatesProvider;

if (!defined('ABSPATH')) {
    exit;
}

final class Bootstrap
{
    public static function init(): void
    {
        add_action('rest_api_init', static function (): void {
            (new UpdatesController(new UpdatesProvider()))->registerRoutes();
        });
    }
}
