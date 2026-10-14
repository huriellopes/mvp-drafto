# Runbook: Redis, Horizon e Reverb em produção

Guia para colocar no ar a infraestrutura de tempo real e filas do `drafto.pro`:

- **Redis**: sessão, cache, fila e pub/sub do Reverb.
- **Horizon**: workers das filas Redis (`default` e `exports`) com dashboard em `/horizon`.
- **Reverb**: servidor WebSocket que empurra eventos para o navegador (sino de
  notificações e exports prontos), substituindo o polling de 60s e de 1s.

> Layout do servidor assumido (igual ao `deploy.php`): `/var/www/drafto.pro/{releases,current,shared}`,
> usuário `deploy`, PHP 8.4-FPM, Nginx e Cloudflare na frente.

---

## 1. Redis

### 1.1 Instalação

```bash
sudo apt install redis-server php8.4-redis
sudo systemctl enable --now redis-server
```

O app usa `REDIS_CLIENT=phpredis` (extensão nativa, mais rápida que `predis`).
O Horizon também precisa da extensão `pcntl`, que já vem no PHP CLI do Ubuntu.

### 1.2 Política de memória (importante)

Fila e sessão **não podem ser despejadas** pela política de memória do Redis: um
job perdido nunca roda, e uma sessão perdida desloga o usuário. Já o cache pode
ser despejado à vontade. Há duas opções.

**Opção A, recomendada: duas instâncias.**

| Instância | Porta | Uso | `maxmemory-policy` | Persistência |
|---|---|---|---|---|
| principal | 6379 | fila (Horizon), sessão, Reverb | `noeviction` | `appendonly yes` |
| cache | 6380 | cache | `allkeys-lru` | desligada |

```ini
# /etc/redis/redis.conf (principal)
bind 127.0.0.1 ::1
requirepass <senha-forte>
maxmemory 512mb
maxmemory-policy noeviction
appendonly yes

# /etc/redis/redis-cache.conf (cópia com port 6380)
port 6380
bind 127.0.0.1 ::1
requirepass <senha-forte>
maxmemory 512mb
maxmemory-policy allkeys-lru
save ""
appendonly no
```

**Opção B: uma instância só**, com `maxmemory-policy noeviction` e memória com
folga. É mais simples, mas um cache cheio passa a gerar erro de escrita em vez de
despejar chaves antigas. Monitore o uso de memória.

### 1.3 `.env` de produção (Redis)

```dotenv
SESSION_DRIVER=redis
SESSION_CONNECTION=session
CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=<senha-forte>

# DBs lógicos na instância principal: 0 = fila/Horizon/Reverb, 2 = sessão
REDIS_SESSION_DB=2

# Opção A: cache na instância 6380
REDIS_CACHE_HOST=127.0.0.1
REDIS_CACHE_PORT=6380
REDIS_CACHE_DB=0
```

> **Já é obrigatório hoje:** o código usa `Cache::tags()` (perfil, explorar e
> compartilhar post/perfil), que **não funciona** com `CACHE_STORE=database` ou
> `file`: a página de post responde 500. Ou seja, o cache em produção precisa ser Redis.

> **Na virada:** trocar o driver de sessão desloga todo mundo uma vez, porque as
> sessões antigas ficam na tabela `sessions`. Faça a troca fora do horário de pico.

---

## 2. Horizon (filas)

### 2.1 Supervisores configurados (`config/horizon.php`)

| Supervisor | Conexão | Fila | Produção | Uso |
|---|---|---|---|---|
| `supervisor-default` | `redis` | `default` | até 8 processos (auto-balance) | e-mails, notificações, SEO/mídia, broadcasts |
| `supervisor-exports` | `redis-exports` | `exports` | 2 processos | Excel e códigos de recuperação (timeout de 600s) |

A conexão `redis-exports` tem `retry_after=660`, maior que o timeout do export.
Sem isso, um export com mais de 90s seria entregue a um segundo worker no meio
da execução.

### 2.2 Supervisor (processo do sistema)

`/etc/supervisor/conf.d/drafto-horizon.conf`:

```ini
[program:drafto-horizon]
process_name=%(program_name)s
command=php /var/www/drafto.pro/current/artisan horizon
user=deploy
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/www/drafto.pro/shared/storage/logs/horizon.log
; maior que o maior timeout de job (export = 600s), para terminar com segurança
stopwaitsecs=660
```

```bash
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl status drafto-horizon
```

Desligue o worker antigo (`queue:work`), se houver, para não processar a mesma
fila duas vezes.

### 2.3 Scheduler e dashboard

- O cron do scheduler (`* * * * * php /var/www/drafto.pro/current/artisan schedule:run`)
  já roda o `horizon:snapshot` a cada 5 minutos. Esse comando alimenta os gráficos do dashboard.
- Dashboard: `https://drafto.pro/horizon`, liberado **apenas para super_admin**
  (gate `viewHorizon` em `App\Providers\HorizonServiceProvider`).

---

## 3. Reverb (WebSocket)

### 3.1 Chaves e `.env`

Gere as credenciais **uma vez** (não reaproveite as do ambiente local):

```bash
php -r 'echo "REVERB_APP_ID=".random_int(100000,999999).PHP_EOL."REVERB_APP_KEY=".bin2hex(random_bytes(10)).PHP_EOL."REVERB_APP_SECRET=".bin2hex(random_bytes(10)).PHP_EOL;'
```

```dotenv
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=<gerado>
REVERB_APP_KEY=<gerado>
REVERB_APP_SECRET=<gerado>

# Laravel -> Reverb: direto no processo local, sem passar por Nginx/Cloudflare
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

# Onde o reverb:start escuta (só localhost; o Nginx faz o proxy público)
REVERB_SERVER_HOST=127.0.0.1
REVERB_SERVER_PORT=8080

# Só o site pode abrir WebSocket
REVERB_ALLOWED_ORIGINS=drafto.pro,www.drafto.pro

# Navegador -> Reverb (entra no bundle JS no `npm run build`)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST=ws.drafto.pro
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

> As variáveis `VITE_*` são lidas **no build dos assets**, não em runtime. Elas
> precisam estar no `.env` da máquina onde roda o `npm run build`. Hoje o
> `deploy.php` não executa esse build. Confirme onde ele roda e garanta essas
> variáveis lá. Depois de mudá-las, refaça o build.

### 3.2 Supervisor

`/etc/supervisor/conf.d/drafto-reverb.conf`:

```ini
[program:drafto-reverb]
process_name=%(program_name)s
command=php /var/www/drafto.pro/current/artisan reverb:start --host=127.0.0.1 --port=8080
user=deploy
autostart=true
autorestart=true
; cada conexão WebSocket é um file descriptor
minfds=10000
redirect_stderr=true
stdout_logfile=/var/www/drafto.pro/shared/storage/logs/reverb.log
```

Para mais de algumas centenas de conexões simultâneas, instale a extensão `ev`
(`sudo pecl install ev`). O Reverb passa a usar o event loop dela, que não tem o
limite de 1.024 conexões do `stream_select`. Para escalar em mais de um
servidor, ative `REVERB_SCALING_ENABLED=true`, que distribui as mensagens via Redis pub/sub.

### 3.3 Nginx: subdomínio `ws.drafto.pro`

Um subdomínio próprio isola o WebSocket das regras de cache e WAF do site.

```nginx
server {
    listen 443 ssl;
    http2 on;
    server_name ws.drafto.pro;

    ssl_certificate     /etc/ssl/drafto/origin.pem;   # certificado de origem Cloudflare
    ssl_certificate_key /etc/ssl/drafto/origin.key;

    location / {
        proxy_http_version 1.1;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header SERVER_PORT $server_port;
        proxy_set_header REMOTE_ADDR $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_read_timeout 120s;

        proxy_pass http://127.0.0.1:8080;
    }
}
```

### 3.4 Cloudflare

- DNS: registro `ws` **proxied** (nuvem laranja). WebSockets já vêm habilitados por padrão.
- SSL/TLS: **Full (strict)**.
- Regra de cache: **Bypass** para `ws.drafto.pro/*`.
- O Cloudflare encerra conexões ociosas após 100s. O Reverb envia ping a cada 60s
  (`REVERB_APP_PING_INTERVAL`), então as conexões se mantêm vivas.

---

## 4. Deploy

O `deploy.php` já faz o necessário depois de trocar o symlink `current`:

1. `artisan horizon:terminate`: o Horizon termina os jobs em andamento e sai, e
   o Supervisor sobe de novo já no release novo.
2. `artisan reverb:restart`: o mesmo para o Reverb.
3. `php-fpm:reload`.

**Ordem do primeiro deploy:**

1. Redis instalado e configurado (seção 1) e `.env` de produção atualizado (seções 1.3 e 3.1).
2. Programas do Supervisor criados (2.2 e 3.2), **mas ainda parados**.
3. Nginx e Cloudflare (3.3 e 3.4).
4. Build dos assets com as `VITE_REVERB_*` corretas.
5. Deploy (`vendor/bin/dep deploy production`).
6. `sudo supervisorctl start drafto-horizon drafto-reverb`.
7. Desligar o `queue:work` antigo, se existir.

---

## 5. Verificação pós-deploy

```bash
# Processos de pé
sudo supervisorctl status drafto-horizon drafto-reverb
php artisan horizon:status          # => running

# Redis respondendo e com as políticas certas
redis-cli -a "$REDIS_PASSWORD" ping
redis-cli -a "$REDIS_PASSWORD" config get maxmemory-policy

# Reverb escutando localmente
ss -ltnp | grep 8080
```

No navegador, logado no dashboard:

1. DevTools → Network → WS: deve existir uma conexão `wss://ws.drafto.pro/app/<key>`
   com status **101**, sem requests `livewire/update` periódicos do sino.
2. Peça para outra conta curtir um post seu: o contador do sino atualiza na hora.
3. Admin → Usuários → Exportar: o botão "Baixar Excel" aparece assim que o job
   termina, sem requests a cada segundo.

---

## 6. Fallbacks e o que acontece se algo cair

| Falha | Comportamento |
|---|---|
| Reverb fora do ar | O navegador detecta que o Echo está desconectado e volta a consultar o servidor: sino a cada 60s, export a cada 5s. Nada quebra. |
| Reverb fora do ar durante um export | O arquivo é gerado mesmo assim (o broadcast falho é reportado, mas não falha o job). O fallback encontra o arquivo. |
| Horizon parado | Jobs se acumulam no Redis e são processados quando ele volta. Veja `/horizon`, alerta "long wait" por fila. |
| Cache Redis despejado/limpo | Só perde cache. A regra de sessão única deixa de valer até o próximo login de cada usuário, com falha segura: nenhuma sessão é derrubada por engano. |
