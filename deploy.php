<?php

declare(strict_types=1);

namespace Deployer;

use RuntimeException;

require 'recipe/laravel.php';

/*
|--------------------------------------------------------------------------
| Deploy zero-downtime (Deployer 7) — Drafto
|--------------------------------------------------------------------------
|
| Layout: /var/www/drafto.pro/{releases,current,shared}
|
| REGRA: o deploy SÓ pode ser feito a partir da branch `main`.
| Use:  vendor/bin/dep deploy production   (ou via CI, .github/workflows/deploy.yml)
|
| Configuração sensível vem de variáveis de ambiente (defina no CI/secrets):
|   DEPLOY_REPOSITORY  ex: git@github.com:huriellopes/drafto.git
|   DEPLOY_HOST        ex: drafto.pro
|   DEPLOY_USER        ex: deploy
|   DEPLOY_PHP_FPM     ex: php8.4-fpm
*/

set('application', 'drafto');

// Branch travada: NUNCA deploia algo diferente de main.
set('branch', 'main');

set('repository', getenv('DEPLOY_REPOSITORY') ?: 'git@github.com:huriellopes/drafto.git');
set('keep_releases', 5);
set('php_fpm_service', getenv('DEPLOY_PHP_FPM') ?: 'php8.4-fpm');

// Composer de produção (sem dev, otimizado).
set('composer_options', 'install --no-dev --no-interaction --prefer-dist --optimize-autoloader');

// Compartilhado entre releases.
add('shared_files', ['.env']);
add('shared_dirs', ['storage']);
add('writable_dirs', ['bootstrap/cache', 'storage']);

host('production')
    ->setHostname(getenv('DEPLOY_HOST') ?: 'drafto.pro')
    ->setRemoteUser(getenv('DEPLOY_USER') ?: 'deploy')
    ->setDeployPath('/var/www/drafto.pro');

/*
|--------------------------------------------------------------------------
| Guarda: somente a branch main
|--------------------------------------------------------------------------
*/
task('deploy:guard_branch', function (): void {
    if (get('branch') !== 'main') {
        throw new RuntimeException('Deploy bloqueado: somente a branch "main" pode ser publicada.');
    }
})->desc('Garante que apenas a main seja publicada');

/*
|--------------------------------------------------------------------------
| Reinício da fila (corrige __PHP_Incomplete_Class após deploy)
|--------------------------------------------------------------------------
| Sem isto, os workers `queue:work` continuam rodando o código do release
| anterior e falham ao desserializar classes novas (ex.: notifications).
*/
task('artisan:queue:restart', function (): void {
    run('{{bin/php}} {{release_or_current_path}}/artisan queue:restart');
})->desc('Reinicia os workers da fila para carregarem o novo release');

/*
|--------------------------------------------------------------------------
| Pipeline
|--------------------------------------------------------------------------
*/
before('deploy', 'deploy:guard_branch');

// Migrações (forçadas, sem prompt) após enviar o release.
after('deploy:vendors', 'artisan:migrate');

// Caches de produção + storage link na ordem correta.
after('artisan:migrate', 'artisan:storage:link');
after('artisan:storage:link', 'artisan:config:cache');
after('artisan:config:cache', 'artisan:route:cache');
after('artisan:route:cache', 'artisan:view:cache');
after('artisan:view:cache', 'artisan:event:cache');

// Após trocar o symlink (current → novo release), reinicia fila e PHP-FPM.
after('deploy:symlink', 'artisan:queue:restart');
after('artisan:queue:restart', 'php-fpm:reload');

// Rollback automático se qualquer passo falhar.
after('deploy:failed', 'deploy:unlock');
