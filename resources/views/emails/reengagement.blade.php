@extends('emails.layouts.base')

@section('content')
    <h1 class="title">{{ $user->greeting() }}, {{ $user->name }} 👋</h1>

    @if($mode === 'writer')
        <p class="text">
            Faz cerca de <strong>{{ $inactiveDays }} dias</strong> que você não aparece por aqui — e o
            {{ config('app.name') }} continua sendo o lugar certo para registrar seus
            <strong>pensamentos, ideias e ensinamentos</strong>.
        </p>
        <p class="text">
            Que tal transformar uma ideia em texto agora? Pode ser só um rascunho rápido — a gente cuida do resto.
        </p>
    @elseif($mode === 'reader_read')
        <p class="text">
            Faz cerca de <strong>{{ $inactiveDays }} dias</strong> que você não passa por aqui — e, desde então,
            muita <strong>história, artigo e ensinamento</strong> novo foi publicado no {{ config('app.name') }}.
        </p>
        <p class="text">
            Que tal voltar e descobrir o que os escritores andaram compartilhando? Tem leitura boa esperando por você.
        </p>
    @else
        <p class="text">
            Sentimos a sua falta por aqui! E que tal aproveitar a volta para ir além da leitura?
            No {{ config('app.name') }}, qualquer pessoa pode <strong>compartilhar suas próprias ideias,
            histórias e ensinamentos</strong>.
        </p>
        <p class="text">
            Você já tem tudo o que precisa para começar — vire <strong>escritor</strong> e publique o seu primeiro texto.
            Pode ser simples assim: uma ideia que você gostaria de ler.
        </p>
    @endif

    <div class="button-container">
        <a href="{{ $ctaUrl }}" class="button">{{ $ctaText }}</a>
    </div>
@endsection

@section('footer')
    Você recebe este lembrete como membro do {{ config('app.name') }}.<br>
    <a href="{{ $unsubscribeUrl }}" class="link">Não quero mais receber lembretes de retorno</a> &middot; {{ date('Y') }}
@endsection
