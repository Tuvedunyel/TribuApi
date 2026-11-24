<?php

namespace TribuApi\Rest;

use TribuApi\Updates\UpdatesProvider;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Endpoint GET /wp-json/tribu-api/v1/updates
 */
final readonly class UpdatesController
{
    private const string NAMESPACE = 'tribu-api/v1';
    private const string ROUTE = '/updates';

    public function __construct(private UpdatesProvider $updates) {}

    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, self::ROUTE, [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => $this->getUpdates(...),
            'permission_callback' => function () {
                return current_user_can('update_core');
            },
            'args' => [
                'info' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
    }

    /**
     * Retourne les mises à jour disponibles, filtrées par le paramètre `info`
     * (counts, core, plugins, themes) s'il est renseigné.
     *
     * @param \WP_REST_Request<array<string, mixed>> $request
     */
    public function getUpdates(\WP_REST_Request $request): \WP_REST_Response
    {
        $updates = $this->updates->getAll();
        $info = $request->get_param('info');

        if (is_string($info) && array_key_exists($info, $updates)) {
            return new \WP_REST_Response($updates[$info]);
        }

        return new \WP_REST_Response($updates);
    }
}
