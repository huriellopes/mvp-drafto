# ✍️ Drafto — The Ultimate Writing Ecosystem

[![Laravel 12+](https://img.shields.io/badge/Laravel-12+-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP 8.4+](https://img.shields.io/badge/PHP-8.4+-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![TailwindCSS 4.0](https://img.shields.io/badge/TailwindCSS-4.0-38B2AC?style=for-the-badge&logo=tailwind-css)](https://tailwindcss.com)
[![Livewire 4](https://img.shields.io/badge/Livewire-4.0-FB70A9?style=for-the-badge&logo=livewire)](https://livewire.laravel.com)
[![Redis Optimized](https://img.shields.io/badge/Redis-Optimized-DC382D?style=for-the-badge&logo=redis)](https://redis.io)

**Drafto** é uma plataforma de alta performance desenvolvida para escritores e entusiastas que buscam um ambiente estruturado para criação, publicação e gestão de conteúdo. Focada em **SEO avançado**, **escalabilidade** e **interação social**, a aplicação utiliza o que há de mais moderno no ecossistema PHP.

---

## 🚀 High-Level Tech Stack (Senior Tier)

O Drafto foi construído sobre o **TALL Stack** moderno, priorizando tipagem estrita e performance:

- **Core:** Laravel 12+ & PHP 8.3 (utilizando `readonly classes`, `enums` e `strict types`).
- **Frontend:** Livewire 4+ (Full-stack components) & Alpine.js para reatividade client-side.
- **Styling:** Tailwind CSS 4+ (Engine de alta performance e CSS-first).
- **Persistence:** MySQL 8.4 (Otimizado com índices estratégicos e suporte a JSON).
- **Infrastructure:** Docker (Laravel Sail), Redis (Caching & High-speed Queues).

---

## 🏗️ Senior Architecture & Patterns

A arquitetura do projeto segue princípios de **Clean Code** e **SOLID**, garantindo manutenibilidade em escala:

### 1. Atomic Business Logic (Actions)
Toda a lógica de negócio é encapsulada em **Actions** (`app/Actions`), garantindo que os controllers e componentes Livewire permaneçam magros e testáveis.
*Exemplo: `SavePostAction`, `ToggleLikeAction`.*

### 2. Type-Safe Data Transfer (DTOs)
Utilizamos **Data Transfer Objects** imutáveis para mover dados entre camadas, eliminando o uso de arrays associativos "mágicos" e garantindo integridade via `spatie/laravel-data`.

### 3. Event-Driven & Asynchronous
Tarefas pesadas (Processamento de Imagens, SEO, Notificações) são despachadas para **Background Jobs** via Redis, garantindo um tempo de resposta (TTFB) extremamente baixo para o usuário.

### 4. Enterprise-Grade Middlewares
Implementação de **Terminable Middlewares** para processamento de estatísticas e **Static Request Caching** para evitar consultas redundantes ao banco de dados no mesmo ciclo de vida da requisição.

---

## 💎 Key Features

- **Drafting Engine:** Sistema de rascunhos e rastro de auditoria para publicações.
- **Advanced SEO Suite:** Geração automática de metadados e Schema.org (JSON-LD) para Posts e Perfis.
- **Social Core:** Interações em tempo real (Likes, Follows, Menções) com sistema de notificações dinâmico.
- **Subscription Engine:** Gestão de planos (Free, Plus, Pro) com travas automáticas de limites de uso.
- **Admin Dashboard:** Gestão completa de faturamento, usuários e moderadores.

---

## 🛠️ Performance & Scalability Benchmarks

O Drafto foi testado sob estresse para garantir estabilidade:
- **Throughput:** ~65 interações complexas por segundo (escrita).
- **Latency:** < 200ms para salvamento de posts complexos via processamento assíncrono.
- **Infra:** 100% Redis-backed para Sessões, Cache e Filas.

---

## ⚙️ Quick Start (Development)

Certifique-se de ter o Docker instalado e execute:

```bash
# Clone o repositório
git clone https://github.com/usuario/mvp-drafto.git

# Instale dependências e suba os containers
./vendor/bin/sail composer install
./vendor/bin/sail up -d

# Prepare o banco e o ambiente
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

---

## 🧪 Testing Suite

Mantemos uma cobertura rigorosa utilizando **Pest Framework**:

```bash
./vendor/bin/sail artisan test
```

---

## 📄 License

Drafto is open-sourced software licensed under the [MIT license](LICENSE.md).

---
Developed with ❤️ by [Huriel Lopes] - *Senior Software Engineer Perspective*
