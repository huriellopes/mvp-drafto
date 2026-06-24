@extends('emails.layouts.base')

@section('content')
    <p class="text">Olá, {{ $user->name }}! Temos novidades no {{ config('app.name') }}:</p>

    <div class="text">{!! $update->content !!}</div>

    <div class="button-container">
        <a href="{{ $ctaUrl }}" class="button">Conferir agora</a>
    </div>
@endsection

@section('footer')
    Você recebe avisos de novidades como membro do {{ config('app.name') }}.<br>
    <a href="{{ $unsubscribeUrl }}" class="link">Descadastrar de avisos de novidades</a> &middot; {{ date('Y') }}
@endsection
