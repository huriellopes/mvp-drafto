<x-mail::message>
# Olá, {{ $user->name }}!

Estamos muito felizes em ter você no **Drafto**. Como você se registrou como escritor, acabamos de liberar **15 dias de acesso Pro** para você experimentar o melhor da nossa plataforma.

Durante este período de degustação, você terá acesso a:
- Publicações e Rascunhos Ilimitados
- Estatísticas Avançadas de Visualização
- Identidade Visual Customizada e Crachás em Ultra-HD
- Ferramentas de Moderação de Comentários

Sua degustação expira em: **{{ $user->trial_ends_at->format('d/m/Y') }}**.

Aproveite ao máximo para criar conteúdos incríveis!

<x-mail::button :url="route('dashboard.posts.create')">
Começar a Escrever
</x-mail::button>

Se precisar de ajuda, basta responder a este e-mail.

Obrigado,<br>
Equipe {{ config('app.name') }}
</x-mail::message>
