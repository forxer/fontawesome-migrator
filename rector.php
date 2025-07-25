<?php

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Php84\Rector\MethodCall\NewMethodCallWithoutParenthesesRector;
use RectorLaravel\Rector\Class_\UnifyModelDatesWithCastsRector;
use RectorLaravel\Rector\FuncCall\RemoveDumpDataDeadCodeRector;
use RectorLaravel\Rector\MethodCall\EloquentWhereRelationTypeHintingParameterRector;
use RectorLaravel\Rector\MethodCall\EloquentWhereTypeHintClosureParameterRector;
use RectorLaravel\Rector\MethodCall\RedirectBackToBackHelperRector;
use RectorLaravel\Rector\MethodCall\ReplaceServiceContainerCallArgRector;
use RectorLaravel\Rector\MethodCall\ReverseConditionableMethodCallRector;
use RectorLaravel\Rector\MethodCall\ValidationRuleArrayStringValueToArrayRector;
use RectorLaravel\Rector\PropertyFetch\OptionalToNullsafeOperatorRector;
use RectorLaravel\Rector\StaticCall\RouteActionCallableRector;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withImportNames()
    ->withParallel(
        timeoutSeconds: 320,
        maxNumberOfProcess: 16,
        jobSize: 20,
    )
    ->withCache(
        cacheClass: FileCacheStorage::class,
        cacheDirectory: __DIR__.'/.rector_cache',
    )
    ->withPaths([
        __DIR__.'/src',
    ])

    // Up from PHP 5.3 to 8.2
    ->withPhpSets()

    // only PHP 8.2
    // ->withPhpSets(php82: true)

    ->withSkip([
        // Désactivation de cette règle car elle
        // transforme :     array_map('intval',
        // en :             array_map(intval(...),
        FirstClassCallableRector::class,

        // Cet attribut natif PHP n'est pas très utile ;
        // mieux vaux se baser sur de l'analyse statique
        // En plus, lors de la rédaction de ce message,
        // la règle fonctionne mal, elle est buguée...
        AddOverrideAttributeToOverriddenMethodsRector::class,

        // Tant que PHP-CS-Fixer ne supporte pas PHP 8.4,
        // pour que PINT fonctionne correctement
        NewMethodCallWithoutParenthesesRector::class,
    ])
    ->withRules([
        EloquentWhereRelationTypeHintingParameterRector::class,
        EloquentWhereTypeHintClosureParameterRector::class,
        OptionalToNullsafeOperatorRector::class,
        RemoveDumpDataDeadCodeRector::class,
        RedirectBackToBackHelperRector::class,
        ReplaceServiceContainerCallArgRector::class,
        ReverseConditionableMethodCallRector::class,
        RouteActionCallableRector::class,
        UnifyModelDatesWithCastsRector::class,
        ValidationRuleArrayStringValueToArrayRector::class,
    ])
    ->withSets([
        LaravelSetList::LARAVEL_FACADE_ALIASES_TO_FULL_NAMES,
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: true,
    );
