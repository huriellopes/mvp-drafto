# Runbook de Performance (PageSpeed / Core Web Vitals)

Guia operacional para manter o `drafto.pro` com boa performance no PageSpeed.
Cobre o que é **código** (já no repo) e o que é **infraestrutura** (Cloudflare /
comando pós-deploy).

> Contexto: a home é um componente Livewire full-page servido através do
> **Cloudflare**. O CLS foi zerado, A11y/SEO/Boas Práticas estão em 100 e o LCP
> foi corrigido. Os itens abaixo são a camada de entrega (cache/compressão) e a
> otimização das mídias já existentes.

---

## 1. Reotimizar mídias existentes (pós-deploy) — **ação manual**

Os jobs de upload já reduzem avatares (≤400×400) e capas de post (≤1200px) e
convertem para WebP. Porém **imagens enviadas antes dessa correção continuam
grandes** (ex.: avatar de 1440×1440 ≈ 190 KB servido para 96px) até serem
reprocessadas.

Depois de cada deploy que traga essa mudança (a partir da `v1.0.12`), rode
**uma vez** em produção:

```bash
php artisan media:optimize
```

Opções:

| Flag | Efeito |
|------|--------|
| _(sem flag)_ | Processa perfis (avatar + capa) e capas de post, de forma síncrona |
| `--queue` | Enfileira os jobs em vez de processar na hora (recomendado se houver muitas imagens) |
| `--profiles` | Só perfis |
| `--posts` | Só capas de post |

O comando é **idempotente**: rodar de novo não degrada imagens já otimizadas
(pula reencode quando a imagem já é WebP dentro do tamanho-alvo).

**Validação:**

```bash
# pega um avatar atual do HTML e confere o tamanho
AV=$(curl -s https://drafto.pro/ | grep -oE '/storage/avatars/[a-zA-Z0-9._-]+\.(webp|jpg|jpeg|png)' | head -1)
curl -sI "https://drafto.pro${AV}" | grep -i "content-length\|content-type"
# esperado: content-type: image/webp e content-length de poucas dezenas de KB
```

---

## 2. Cache de longa duração no Cloudflare

**Objetivo:** eliminar o aviso "Use ciclos de vida eficientes de cache". Assets
com hash (`/build/*`) e uploads com nome único (`/storage/*`) podem ser
cacheados por **1 ano** com segurança.

### Cache Rule (recomendado)

Painel Cloudflare → `drafto.pro` → **Caching → Cache Rules → Create rule**

- **Nome:** `Static assets - long cache`
- **Expressão** (Edit expression):

```
(starts_with(http.request.uri.path, "/build/")) or
(starts_with(http.request.uri.path, "/storage/")) or
(starts_with(http.request.uri.path, "/images/")) or
(http.request.uri.path.extension in {"css" "js" "mjs" "woff" "woff2" "ttf" "otf" "webp" "avif" "png" "jpg" "jpeg" "gif" "svg" "ico"})
```

- **Then:**
  - Cache eligibility → **Eligible for cache** (força cache mesmo se a origem
    mandar `private`)
  - Edge TTL → **Ignore cache-control header and use this TTL** → **1 year**
  - Browser TTL → **Override origin** → **1 year**

> ⚠️ **Nunca** cachear o HTML / rota raiz — o Livewire precisa de HTML fresco
> (CSRF/estado). A expressão acima só pega extensões estáticas e as pastas de
> assets; o HTML fica de fora.

### Alternativa: Page Rules (planos sem Cache Rules)

`Rules → Page Rules` (limite de 3 no grátis):

- `drafto.pro/build/*` → Cache Level: *Cache Everything* · Edge Cache TTL: *a month* · Browser Cache TTL: *a year*
- `drafto.pro/storage/*` → idem
- `drafto.pro/images/*` → idem

### Validação

```bash
# use SEMPRE o hash atual (hashes antigos dão 404 e cacheiam a página de erro)
CSS=$(curl -s https://drafto.pro/ | grep -oE '/build/assets/[a-zA-Z0-9._-]+\.css' | head -1)
curl -sI "https://drafto.pro${CSS}" | grep -i "cache-control\|cf-cache-status"
# 1a chamada pode dar MISS/REVALIDATED; a 2a deve dar: cf-cache-status: HIT
# cache-control deve conter max-age=31536000
```

> Após deploy, os bundles do Vite mudam de hash automaticamente — não precisa
> purgar. Para forçar: **Caching → Configuration → Purge Everything**.

---

## 3. Ganhos extras (opcionais)

- **Brotli** — Cloudflare → **Speed → Optimization → Content Optimization** →
  ativar Brotli. Comprime melhor que gzip (ajuda o render-block do `app.css`).
- **Early Hints** — ativar (pré-carrega CSS/fonte antes do HTML completo).
- **Cloudflare Web Analytics beacon** — o `beacon.min.js` gera uma _long task_
  (~60 ms) que pesa no TBT. Como o projeto já tem analytics próprio (com
  consentimento LGPD), dá para **desativar** em **Analytics & Logs →
  Web Analytics** e remover esse custo de main-thread.

---

## 4. O que já está resolvido no código

| Item | Onde |
|------|------|
| **CLS 0.418 → 0** — remoção do `#[Lazy]` do `Home` + `width/height` em imagens | `app/Livewire/Public/Site/Home.php`, componentes de card |
| **LCP** — remoção do fade de entrada do hero + fonte assíncrona (`preload`+swap) | `resources/views/livewire/public/site/home.blade.php`, `layouts/site.blade.php` |
| **A11y 100** — aria-labels, contraste AA, ordem de headings, label-in-name | componentes públicos / navbar / footer |
| **Imagens** — jobs reduzem avatar ≤400×400 e capa ≤1200px (idempotente) | `app/Jobs/ProcessProfileMediaJob.php`, `app/Jobs/ProcessPostMediaAndSeoJob.php` |
| **Cache/compressão (Apache)** — `Cache-Control` imutável + `mod_deflate` | `public/.htaccess` (só vale se a origem for Apache; atrás de Cloudflare use a Cache Rule) |

---

## 5. Checklist após um deploy relevante

1. [ ] Deploy concluído (Cloud Prime).
2. [ ] `php artisan media:optimize` (se houver mídias antigas não otimizadas).
3. [ ] Cache Rule do Cloudflare ativa (validar com `cf-cache-status: HIT`).
4. [ ] Re-medir o PageSpeed (mobile **e** desktop).
