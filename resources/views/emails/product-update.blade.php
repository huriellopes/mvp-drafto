@extends('emails.layouts.base')

@section('content')
    <h1 class="title">{{ $update->title }}</h1>

    <p class="text">{{ $user->greeting() }}, {{ $user->name }} 👋</p>

    <p class="text">Temos uma novidade fresquinha no {{ config('app.name') }} que achamos que você vai gostar:</p>

    <div class="text">{!! $update->content !!}</div>

    <div class="button-container">
        <a href="{{ $ctaUrl }}" class="button">Conferir agora</a>
    </div>

    <p class="text" style="margin-top: 40px; margin-bottom: 0;">
        Atenciosamente,<br>
        <strong style="color: #18181b;">Equipe {{ config('app.name') }}</strong>
    </p>
@endsection

@section('footer')
    Você recebe avisos de novidades como membro do {{ config('app.name') }}.<br>
    <a href="{{ $unsubscribeUrl }}" class="link">Descadastrar de avisos de novidades</a> &middot; {{ date('Y') }}
@endsection
