<?php

declare(strict_types=1);

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Page d'accueil
Breadcrumbs::for('fontawesome-migrator.home', function (BreadcrumbTrail $trail): void {
    $trail->push('Accueil', route('fontawesome-migrator.home'));
});

// Rapports - Liste
Breadcrumbs::for('fontawesome-migrator.reports.index', function (BreadcrumbTrail $trail): void {
    $trail->parent('fontawesome-migrator.home');
    $trail->push('Rapports', route('fontawesome-migrator.reports.index'));
});

// Rapports - Détail
Breadcrumbs::for('fontawesome-migrator.reports.show', function (BreadcrumbTrail $trail, string $filename): void {
    $trail->parent('fontawesome-migrator.reports.index');
    $trail->push('Détail');
});

// Sessions - Liste
Breadcrumbs::for('fontawesome-migrator.sessions.index', function (BreadcrumbTrail $trail): void {
    $trail->parent('fontawesome-migrator.home');
    $trail->push('Sessions', route('fontawesome-migrator.sessions.index'));
});

// Sessions - Détail
Breadcrumbs::for('fontawesome-migrator.sessions.show', function (BreadcrumbTrail $trail, string $sessionId): void {
    $trail->parent('fontawesome-migrator.sessions.index');
    $trail->push('Détail');
});

// Tests
Breadcrumbs::for('fontawesome-migrator.tests.index', function (BreadcrumbTrail $trail): void {
    $trail->parent('fontawesome-migrator.home');
    $trail->push('Tests', route('fontawesome-migrator.tests.index'));
});
