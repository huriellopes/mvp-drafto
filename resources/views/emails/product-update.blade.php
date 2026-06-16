@extends('emails.layouts.base')

@section('content')
    <h1 class="title">{{ $update->title }}</h1>
    <p class="text">Olá, {{ $user->name }}! Temos novidades no {{ config('app.name') }}:</p>
    {{-- Conteúdo já sanitizado (Purifier) ao salvar o comunicado. --}}
    <div class="text">{!! $update->content !!}</div>
    <div class="button-container">
        <a href="{{ $ctaUrl }}" class="button">Conferir agora</a>
    </div>
@endsection

@section('footer')
    Você recebe avisos de novidades como membro do {{ config('app.name') }}.<br>
    <a href="{{ $unsubscribeUrl }}" class="link">Descadastrar de avisos de novidades</a> &middot; {{ date('Y') }}
@endsection
