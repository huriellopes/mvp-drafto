<?php

declare(strict_types=1);

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Dashboard Home
Breadcrumbs::for('dashboard.index', function (BreadcrumbTrail $trail) {
    $trail->push('Dashboard', route('dashboard.index'));
});

// Perfil & Conta
Breadcrumbs::for('dashboard.profile', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Meu Perfil Público', route('dashboard.profile'));
});

Breadcrumbs::for('dashboard.account', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Configurações', route('dashboard.account'));
});

// Escritor: Posts
Breadcrumbs::for('dashboard.posts.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Minhas Obras', route('dashboard.posts.index'));
});

Breadcrumbs::for('dashboard.posts.draft', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Rascunhos', route('dashboard.posts.draft'));
});

Breadcrumbs::for('dashboard.posts.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard.posts.index');
    $trail->push('Nova Publicação', route('dashboard.posts.create'));
});

Breadcrumbs::for('dashboard.posts.edit', function (BreadcrumbTrail $trail, $post) {
    $trail->parent('dashboard.posts.index');
    $trail->push('Editar Publicação', route('dashboard.posts.edit', $post));
});

// Leitor: Salvos & Social
Breadcrumbs::for('dashboard.posts.saved', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Itens Salvos', route('dashboard.posts.saved'));
});

Breadcrumbs::for('dashboard.comments', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Meus Comentários', route('dashboard.comments'));
});

Breadcrumbs::for('dashboard.follows', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Comunidade', route('dashboard.follows'));
});

// Admin: Master
Breadcrumbs::for('dashboard.modules.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Módulos do Sistema', route('dashboard.admin.modules.index'));
});

Breadcrumbs::for('dashboard.users.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Controle de Usuários', route('dashboard.admin.users.index'));
});

Breadcrumbs::for('dashboard.newsletter.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Newsletter', route('dashboard.admin.newsletter.index'));
});

Breadcrumbs::for('dashboard.reports.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Moderação', route('dashboard.admin.reports.index'));
});

Breadcrumbs::for('dashboard.posts.views', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Estatísticas de Views', route('dashboard.admin.posts.views'));
});
