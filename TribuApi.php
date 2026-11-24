<?php

/**
 * Plugin Name: Tribu Api
 * Description: Permet d'exposer différents endpoints. Accessible depuis mon-url.com/wp-json/tribu-api/v1/updates (possible d'ajouter un paramètre info=counts, info=core, info=plugins ou info=themes pour filtrer les résultats, exemple : mon-url.com/wp-json/tribu-api/v1/updates?info=core)
 * Version: 1.1.0
 * Author: Tribu and Co
 * Author URI: https://tribu-and-co.fr
 */

namespace TribuApi;

use TribuApi\Core\Autoloader;
use TribuApi\Core\Bootstrap;

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/src/Core/Autoloader.php';

Autoloader::register();
Bootstrap::init();
