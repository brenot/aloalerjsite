@extends('layouts.master')

@section('page-name')
    <h1 class="nome-comissao">{{ $committee['name'] }}</h1>
@stop

@section('sidebar-name')
    <a href="/"> <img src="/templates/mv/svg/logo-alo-alerj.svg" class="logo-com-tel-dc visible-lg visible-md"></a>
    <a href="/"> <img src="/templates/mv/svg/logo-alo-alerj-branca.svg" class="logo-com-tel-dc visible-sm"></a>
@stop

@section('content-main')
    <div class="hidden-lg">
        @include('partials.committee-telephone', [
           'title' => $committee['short_name'],
           'telephone' => $committee['phone'],
           'site' => '',
       ])
    </div>

    <div class="texto-comissao">
        {!! $committee['texto'] !!}
    </div>

    {{--<div class="ficha-comissao text-center">--}}
        {{--<div class="comissao-presidente"><h3>Presidência</h3>{!! $committee['president'] !!} </div>--}}
        {{--<div class="comissao-secretario"><h3>Secretário</h3>{!! $committee['vice-president'] !!} </div>--}}

        {{--<div class="comissao-dados">--}}
            {{--<div class="comissao-telefones"><span class="comissao-outrostelefones">Outros telefones:</span>{!! $committee['office-phone'] !!}</div>--}}
            {{--<div class="comissao-endereco">{!! $committee['office-address'] !!}</div>--}}
        {{--</div>--}}
    {{--</div>--}}
@stop

@section('content-sidebar')
    @include('partials.committee-telephone', [
        'title' => $committee['short_name'],
        'telephone' => $committee['phone'],
        'site' => '',
    ])
@stop

