@extends('emails.layouts.base')

@section('content')
    <h1 class="title">{{ $user->greeting() }}, {{ $user->name }} 👋</h1>
    <p class="text">
        Faz cerca de <strong>{{ $inactiveDays }} dias</strong> que você não aparece por aqui — e o
        {{ config('app.name') }} continua sendo o lugar certo para registrar seus
        <strong>pensamentos, ideias e ensinamentos</strong>.
    </p>
    <p class="text">
        Que tal transformar uma ideia em texto agora? Pode ser só um rascunho rápido — a gente cuida do resto.
    </p>
    <div class="button-container">
        <a href="{{ $ctaUrl }}" class="button">Escrever no {{ config('app.name') }}</a>
    </div>
@endsection

@section('footer')
    Você recebe este lembrete como membro do {{ config('app.name') }}.<br>
    <a href="{{ $unsubscribeUrl }}" class="link">Não quero mais receber lembretes de retorno</a> &middot; {{ date('Y') }}
@endsection
