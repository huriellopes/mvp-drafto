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
| Reinício do Horizon e do Reverb (corrige __PHP_Incomplete_Class após deploy)
|--------------------------------------------------------------------------
| Sem isto, os workers continuam rodando o código do release anterior e
| falham ao desserializar classes novas (ex.: notifications).
| `horizon:terminate` encerra o Horizon de forma graciosa (termina os jobs em
| andamento); o Supervisor o sobe de novo já no release novo. O mesmo vale
| para o Reverb com `reverb:restart`.
|
| Supervisor (servidor) — programas esperados:
|   [program:drafto-horizon]  command=php /var/www/drafto.pro/current/artisan horizon
|   [program:drafto-reverb]   command=php /var/www/drafto.pro/current/artisan reverb:start
*/
task('artisan:horizon:terminate', function (): void {
    run('{{bin/php}} {{release_or_current_path}}/artisan horizon:terminate');
})->desc('Reinicia o Horizon para carregar o novo release');

task('artisan:reverb:restart', function (): void {
    run('{{bin/php}} {{release_or_current_path}}/artisan reverb:restart');
})->desc('Reinicia o Reverb para carregar o novo release');

/*
|--------------------------------------------------------------------------
| Build dos assets (Vite) no release
|--------------------------------------------------------------------------
| `public/build` não é versionado: os assets são compilados a cada deploy, já
| com o `.env` compartilhado — é dele que o Vite lê as VITE_REVERB_* (host e
| porta públicos do WebSocket), que ficam embutidas no bundle do Echo.
| Requer Node.js >= 22.12 no servidor (exigência do Vite 8).
*/
task('deploy:assets', function (): void {
    if (!test('command -v npm >/dev/null 2>&1')) {
        throw new RuntimeException('npm não encontrado no servidor: instale o Node.js >= 22.12 para compilar os assets do Vite.');
    }

    cd('{{release_path}}');
    run('npm ci --no-audit --no-fund');
    run('npm run build');
    // Só o build é necessário em runtime; evita acumular node_modules por release.
    run('rm -rf node_modules');
})->desc('Compila os assets do Vite com o .env de produção');

/*
|--------------------------------------------------------------------------
| Pipeline
|--------------------------------------------------------------------------
*/
before('deploy', 'deploy:guard_branch');

// Assets antes das migrações: se o build falhar, o banco não é migrado.
after('deploy:vendors', 'deploy:assets');

// Migrações (forçadas, sem prompt) após enviar o release.
after('deploy:assets', 'artisan:migrate');

// Caches de produção + storage link na ordem correta.
after('artisan:migrate', 'artisan:storage:link');
after('artisan:storage:link', 'artisan:config:cache');
after('artisan:config:cache', 'artisan:route:cache');
after('artisan:route:cache', 'artisan:view:cache');
after('artisan:view:cache', 'artisan:event:cache');

// Após trocar o symlink (current → novo release), reinicia Horizon, Reverb e PHP-FPM.
after('deploy:symlink', 'artisan:horizon:terminate');
after('artisan:horizon:terminate', 'artisan:reverb:restart');
after('artisan:reverb:restart', 'php-fpm:reload');

// Rollback automático se qualquer passo falhar.
after('deploy:failed', 'deploy:unlock');
