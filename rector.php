<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
// Exemple de règle utile pour Symfony/Request (optionnelle, voir plus bas)
use Rector\Symfony\Rector\MethodCall\GetRequestRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__,
        __DIR__ . '/tests',
    ])
    ->withSkip([
        __DIR__ . '/vendor',
    ])
    // 1) Migration PHP jusqu’à 8.4
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
    ])
    // 2) Migration Symfony basée sur les versions réellement installées
    ->withComposerBased(
        symfony: true,
        phpunit: true // utile si tu veux aussi moderniser les tests selon ta version PHPUnit
    )
    // 3) (Optionnel) Ajoute quelques règles ciblées fréquentes en Symfony 7.4→8
    ->withRules([
        // Remplace les usages de Request::get() par query/request/attributes quand applicable
        // (très utile car Request::get() est déprécié puis supprimé)
        //GetRequestRector::class,
    ]);