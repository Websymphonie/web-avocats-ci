<?php

declare(strict_types=1);

namespace Websymphonie\AdminContext\Application\Service;

final class SystemBootstrapData
{
    /** @return list<array{name: string, label: string, value: string, type: string}> */
    public static function settings(): array
    {
        return [
            ['name' => 'app_title', 'label' => 'Nom de la plateforme', 'value' => 'Avocat CI', 'type' => 'text'],
            ['name' => 'app_auth_title', 'label' => 'Titre de connexion', 'value' => 'Votre espace Avocat CI', 'type' => 'text'],
            ['name' => 'app_auth_description', 'label' => 'Description de connexion', 'value' => 'Connectez-vous pour accéder à votre espace.', 'type' => 'text'],
            ['name' => 'app_paginate_limit', 'label' => 'Éléments par page', 'value' => '10', 'type' => 'number'],
            ['name' => 'contact_address', 'label' => 'Adresse publique du Barreau', 'value' => 'Maison de l’Avocat – Cocody, Les Deux Plateaux ENA, Rue J9', 'type' => 'text'],
            ['name' => 'contact_phone', 'label' => 'Téléphones publics du Barreau', 'value' => '+225 27 22 41 56 05 / +225 27 22 41 56 13', 'type' => 'text'],
            ['name' => 'contact_email', 'label' => 'Email public du Barreau', 'value' => 'info@ordredesavocats.ci', 'type' => 'text'],
            ['name' => 'contact_hours', 'label' => 'Horaires publics du Barreau', 'value' => 'Lun - Ven, 08:00-17:00', 'type' => 'text'],
        ];
    }

    /** @return list<array{name: string, label: string}> */
    public static function images(): array
    {
        return [
            ['name' => 'app_logo', 'label' => 'Logo de la plateforme'],
            ['name' => 'app_favicon', 'label' => 'Favicon de la plateforme'],
        ];
    }

    /** @return array{name: string, label: string, value: string, type: string}|null */
    public static function setting(string $name): ?array
    {
        foreach (self::settings() as $setting) {
            if ($setting['name'] === $name) {
                return $setting;
            }
        }

        return null;
    }

    public static function fallbackSettingValue(string $name): string
    {
        return self::setting($name)['value'] ?? '';
    }
}
