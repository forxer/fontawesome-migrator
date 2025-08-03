<?php

declare(strict_types=1);

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Page d'accueil
Breadcrumbs::for('fontawesome-migrator.home', function (BreadcrumbTrail $trail): void {
    $trail->push('Accueil', route('fontawesome-migrator.home'));
});

// Migrations - Liste
Breadcrumbs::for('fontawesome-migrator.reports.index', function (BreadcrumbTrail $trail): void {
    $trail->parent('fontawesome-migrator.home');
    $trail->push('Migrations', route('fontawesome-migrator.reports.index'));
});

// Migration - Détail
Breadcrumbs::for('fontawesome-migrator.reports.show', function (BreadcrumbTrail $trail, string $sessionId): void {
    $trail->parent('fontawesome-migrator.reports.index');
    $trail->push('Rapport');
});

// Tests
Breadcrumbs::for('fontawesome-migrator.tests.index', function (BreadcrumbTrail $trail): void {
    $trail->parent('fontawesome-migrator.home');
    $trail->push('Tests', route('fontawesome-migrator.tests.index'));
});
