    <div class="card-body d-none d-sm-block">

        <table id="progressesTable" class="table table-striped table-hover table-progress" cellspacing="0" width="100%">
            <thead>
                <tr>
                  <th>Tipo de Andamento</th>
                  <th>Origem</th>
                  <th>Solicitação</th>
                  {!! (Auth::user() ?'<th>Privacidade</th>   ': '') !!}
                  <th>Finalizador</th>
                  <th>Notificação</th>
                    <th>Criado em</th>
                    <th>Anexos</th>
                  {!! (Auth::user() ? '<th>Atendente</th>' : '') !!}
                </tr>
            </thead>

            @forelse ($progresses as $progress)
                <tr v-on:click='detail("{{$progress->link}}")' style="cursor: pointer;">
                    <td>
                        {{ $progress->progressType->name ?? '' }}
                    </td>

                    <td>
                        {{ $progress->origin->name ?? '' }}
                    </td>

                    <td style="word-wrap: break-word; width: 40%; max-width: 20px;">
                        {{ $progress->original }}
                    </td>
                    @if(Auth::user())
                    <td>
                        @if($progress->is_private)
                            <span class="label-group"><span class="label label-danger">Privado</span></span>
                        @else
                            <h5><span class="badge badge-success">Público</span></h5>
                        @endif
                    </td>
                    @endif

                    <td class="">
                        @if ($progress->record->resolve_progress_id == $progress->id)
                            @if($progress->finalize)
                                <h5><span class="badge badge-success">Sim</span></h5>
                            @endif
                        @else
                            <span class="label-group"><span class="label label-danger"><i class="fas fa-times-circle"></i></span><span class="label label-danger ng-binding">Não</span>
                            {{--<h5><span class="badge badge-danger">Não</span></h5>--}}
                        @endif
                    </td>

                    <td class="">
                        @if ($progress->email_sent_at)
                            <h5>
                                <span class="badge badge-success">
                                    E-mail
                                </span>
                            </h5>
                        @endif
                    </td>

                    <td>{{ $progress->created_at_formatted ?? '' }}</td>

                    <td>
                        @forEach($progress->progressFiles as $attach)
                           <p> <a href="{{$attach->file->public_url}}" download>{{$attach->description}}</a></p>
                        @endForEach
                    </td>

                    {!!Auth::user() ? '<td>'.($progress->creator->name ?? '').'</td>' : ''!!}
                </tr>
            @empty
                <p>Nenhum andamento encontrado.</p>
            @endforelse
        </table>

        {{ $progresses instanceof \Illuminate\Contracts\Pagination\Paginator ? $progresses->links() : '' }}
    </div>


    <!-------------------- Start of MOBILE VERSION -------------------->

    <div class="card-body d-block d-sm-none" id="vue-progress">
        @forelse ($progresses as $progress)
        <div class="mobile-tables"  v-on:click='detail("{{$progress->link}}")' style="cursor: pointer;">

            <div class="contact-line"><span class="mobile-label">Tipo de Andamento :</span> {{ $progress->progressType->name ?? '' }}</div>
            <div class="contact-line"><span class="mobile-label">Origem :</span> {{ $progress->origin->name ?? '' }}</div>
            <div class="contact-line"><span class="mobile-label">Assunto :</span> {{ $progress->area->name ?? '' }}</div>
            <div class="contact-line"><span class="mobile-label">Solicitação :</span> {{ $progress->original }}</div>

                <div class="contact-line"><span class="mobile-label">Privacidade :</span>
                    @if($progress->is_private)
                        <span class="label-group"><span class="label label-danger"><i class="fas fa-times-circle"></i></span><span class="label label-danger ng-binding">Privado</span>
                    @else
                        <h5><span class="badge badge-success">Público</span></h5>
                    @endif
                </div>

            <div class="contact-line"><span class="mobile-label">Finalizador :</span>
                @if ($progress->record->resolve_progress_id == $progress->id)
                    @if($progress->finalize)
                        <h5><span class="badge badge-success">Sim</span></h5>
                    @endif
                @else
                    <span class="label-group"><span class="label label-danger"><i class="fas fa-times-circle"></i></span><span class="label label-danger ng-binding">Não</span>
                    {{--<h5><span class="badge badge-danger">Não</span></h5>--}}
                @endif
            </div>
            <div class="contact-line">
                <span class="mobile-label">Notificação :</span>@if ($progress->email_sent_at)
                    <span class="label-group"><span class="label label-primary"><i class="far fa-envelope"></i></span><span class="label label-primary ng-binding"> E-Mail </span>
                @endif
            </div>
            <div class="contact-line"><span class="mobile-label">Criado em :</span> {{ $progress->created_at_formatted ?? '' }}</div>

            @if(Auth::user())
                <div class="contact-line"><span class="mobile-label">Atendente :</span> {{ $progress->creator->name ?? '' }}</div>
            @endif
        </div>
        @empty
            <p>Nenhum andamento encontrado.</p>
        @endforelse
        {{ $progresses instanceof \Illuminate\Contracts\Pagination\Paginator ? $progresses->links() : '' }}

    </div>

    <!-------------------- Start of MOBILE VERSION -------------------->
