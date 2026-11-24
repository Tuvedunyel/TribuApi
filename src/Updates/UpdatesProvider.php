<?php

namespace TribuApi\Updates;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Récupère les mises à jour disponibles (core, plugins, thèmes, traductions).
 */
final class UpdatesProvider
{
    /**
     * @return array{counts: array<string, int>, core: list<array<mixed>>, plugins: array<array<mixed>>, themes: array<mixed>}
     */
    public function getAll(): array
    {
        $core = $this->getCore();
        $plugins = $this->getPlugins();
        $themes = $this->getThemes();

        return [
            'counts' => $this->getCounts($core, $plugins, $themes),
            'core' => $core,
            'plugins' => $plugins,
            'themes' => $themes,
        ];
    }

    /**
     * wp_get_update_data() n'est pas utilisable ici : elle filtre sur les droits de
     * l'utilisateur courant et renvoie donc 0 partout pour un appel non authentifié.
     *
     * Comme dans WordPress, `wordpress` et `translations` valent 0 ou 1.
     *
     * @param list<array<mixed>> $core
     * @param array<array<mixed>> $plugins
     * @param array<mixed> $themes
     * @return array<string, int>
     */
    private function getCounts(array $core, array $plugins, array $themes): array
    {
        $hasCoreUpdate = array_any(
            $core,
            static fn(array $update): bool => ($update['response'] ?? null) === 'upgrade',
        );

        $counts = [
            'plugins' => count($plugins),
            'themes' => count($themes),
            'wordpress' => (int) $hasCoreUpdate,
            'translations' => (int) (wp_get_translation_updates() !== []),
        ];
        $counts['total'] = array_sum($counts);

        return $counts;
    }

    /**
     * @return list<array<mixed>>
     */
    private function getCore(): array
    {
        $currentVersion = wp_get_wp_version();
        $updates = [];

        foreach ($this->getTransientList('update_core', 'updates') as $update) {
            $updates[] = $this->withCurrentVersion($update, $currentVersion);
        }

        return $updates;
    }

    /**
     * @return array<array<mixed>>
     */
    private function getPlugins(): array
    {
        $response = $this->getTransientList('update_plugins', 'response');

        if ($response === []) {
            return [];
        }

        // Non chargé par WordPress lors d'un appel REST
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $installed = get_plugins();
        $updates = [];

        foreach ($response as $file => $update) {
            $version = $installed[$file]['Version'] ?? null;
            $updates[$file] = $this->withCurrentVersion($update, is_string($version) ? $version : null);
        }

        return $updates;
    }

    /**
     * @return array<mixed>
     */
    private function getThemes(): array
    {
        return $this->getTransientList('update_themes', 'response');
    }

    /**
     * WordPress fournit des objets, mais certains plugins tiers injectent des tableaux.
     *
     * @return array<mixed>
     */
    private function withCurrentVersion(mixed $update, ?string $version): array
    {
        $data = is_object($update) ? get_object_vars($update) : (array) $update;
        $data['current_version'] = $version;

        return $data;
    }

    /**
     * @return array<mixed>
     */
    private function getTransientList(string $transient, string $property): array
    {
        $data = get_site_transient($transient);
        $list = is_object($data) ? ($data->{$property} ?? null) : null;

        return is_array($list) ? $list : [];
    }
}
