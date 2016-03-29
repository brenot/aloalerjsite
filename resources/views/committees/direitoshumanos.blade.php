@extends('layouts.master')

@section('page-name')
    <h1 class="nome-comissao">
        Comissão de Defesa dos Direitos Humanos <br/>e Cidadania
        </h1>
@stop

@section('sidebar-name')
    <a href="/"> <img src="/templates/mv/svg/logo-alo-alerj.svg" class="logo-com-tel-dc visible-lg visible-md"></a>
    <a href="/"> <img src="/templates/mv/svg/logo-alo-alerj-branca.svg" class="logo-com-tel-dc visible-sm"></a>
@stop

@section('content-main')

    <img src="http://www.alerj.rj.gov.br/fotos/busconsumidor_04_fv_30_06_14.jpg" class="img-responsive img-comissoes">
    <p class="texto-comissao">
        Comissão de Defesa dos Direitos Humanos e Cidadania
        <a href="http://www.alerj.rj.gov.br/cdc/" target="_blank"><strong>clique aqui</strong></a>.</p>


@stop

@section('content-sidebar')
    @include('partials.committee-telephone', [
        'title' => 'Direitos Humanos',
        'telephone' => '0800 282 5108',
        'site' => '',
    ])
@stop

