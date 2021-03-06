<?php

namespace App\Http\Controllers\CallCenter;

use App\Models\Progress;
use App\Data\Repositories\Areas;
use App\Data\Repositories\ProgressTypes as ProgressTypesRepository;
use App\Http\Requests\AdvancedSearchRequest;
use App\Services\Workflow;
use Illuminate\Http\Request;
use App\Http\Requests\SearchProtocolRequest;
use App\Http\Requests\ViaRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\RecordRequest;
use Illuminate\Support\Facades\Auth;
use App\Data\Repositories\Records as RecordsRepository;
use App\Data\Repositories\Committees as CommittesRepository;
use App\Data\Repositories\Areas as AreasRepository;
use App\Data\Repositories\RecordTypes as RecordTypesRepository;

class Records extends Controller
{
    public function recoverAccessCode($id)
    {
        $record = $this->recordsRepository->findById($id);
        return $record->sendAccessCode();
    }

    /**
     * @param $person_id
     */

    public function create($person_id = null)
    {
        $person = $this->peopleRepository->findById($person_id);

        request()
            ->session()
            ->forget('workflow');

        return view('callcenter.records.form')
            ->with('laravel', ['mode' => 'create'])
            ->with('person', $person)
            ->with('progressFiles', [])
            ->with('record', $this->recordsRepository->new())
            ->with($this->getComboBoxMenus());
    }

    /**
     * @param $person_id
     */

    public function createPersonAndRecord()
    {
        $person = null;

        $search = request()->get('name') ? request()->get('name') : request()->get('cpf_cnpj');

        if (validate_cpf_cnpj(remove_punctuation($search))) {
            $cpfCnpj = $search;
            $name = null;
        } else {
            $cpfCnpj = null;
            $name = $search;
        }

        if ($cpfCnpj) {
            $person = $this->peopleRepository->findByCpfCnpj($cpfCnpj);
        }

        request()
            ->session()
            ->forget('workflow');

        /**
         * Se encontrou o $person, significa q não precisa cadastrar o endereço e não vai ser anônimo
         */
        if ($person) {
            return redirect()->to(route('records.create', [$person->id]));
        }

        return view('callcenter.records.form-create-person-record')
            ->with('laravel', ['mode' => 'create'])
            ->with('name', $name)
            ->with('cpf_cnpj', $cpfCnpj)
            ->with('anonymous_id', get_anonymous_person()->id)
            ->with('record', $this->recordsRepository->new())
            ->with($this->getComboBoxMenus());
    }

    public function searchShowPublic(SearchProtocolRequest $request)
    {
        $record = app(RecordsRepository::class)->findByProtocol($request->protocol);

        return view('callcenter.records.show-public')->with('record', $record);
    }

    public function createFromWorkflow($person_id)
    {
        $person = $this->peopleRepository->findById($person_id);

        return view('callcenter.records.form')
            ->with('laravel', ['mode' => 'create'])
            ->with('person', $person)
            ->with('record', $this->recordsRepository->new())
            ->with($this->getComboBoxMenus());
    }

    protected function makeViewDataFromRecord($record)
    {
        return array_merge($this->getComboBoxMenus(), [
            'person' => $record->person,
            'record' => $record,
            'records' => $this->recordsRepository->allWherePaginate(
                'person_id',
                $record->person_id
            ),
            'addresses' => $this->peopleAddressesRepository->findByPerson($record->person_id),
            'contacts' => $this->peopleContactsRepository->findByPerson($record->person_id),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(RecordRequest $request)
    {
        $record = $this->storeRecord($request);

        $record->sendNotifications();

        $this->showSuccessMessage(
            'Protocolo ' . ($record->wasRecentlyCreated ? 'criado' : 'gravado') . ' com sucesso.'
        );

        return redirect()->to(
            route(
                Workflow::started()
                    ? 'people_addresses.create'
                    : ($record->wasRecentlyCreated
                        ? 'records.show-protocol'
                        : 'records.show'),

                Workflow::started() ? $record->person->id : $record->id
            )
        );
    }

    public function storePersonRecord(RecordRequest $request)
    {
        $record = $this->storeRecord($request);

        $this->peopleContactsRepository->createContact(
            $request->get('mobile'),
            $record->person_id,
            'mobile'
        );
        $this->peopleContactsRepository->createContact(
            $request->get('whatsapp'),
            $record->person_id,
            'whatsapp'
        );
        $this->peopleContactsRepository->createContact(
            $request->get('email'),
            $record->person_id,
            'email'
        );
        $this->peopleContactsRepository->createContact(
            $request->get('phone'),
            $record->person_id,
            'phone'
        );

        if ($request->get('create_address') == 'true') {
            $data = array_merge($request->toArray(), [
                'person_id' => $record->person_id,
            ]);
            $this->peopleAddressesRepository->create($data);
        }

        $record->sendNotifications();

        $this->showSuccessMessage(
            'Protocolo ' . ($record->wasRecentlyCreated ? 'criado' : 'gravado') . ' com sucesso.'
        );

        /**
         * significa que no formulário de criação do protocolo o nome informado
         * não é o nome cadastrado, será iniciado uma nova tela para acerto do cadastro.
         *
         */
        if (
            $request->get('is_anonymous') == 'false' &&
            $request->get('name') != $record->person->name
        ) {
            return view('callcenter.people.diverge')->with([
                'newName' => $request->get('name'),
                'record' => $record,
            ]);
        }

        return redirect()->to(
            route(
                Workflow::started()
                    ? 'people_addresses.create'
                    : ($record->wasRecentlyCreated
                        ? 'records.show-protocol'
                        : 'records.show'),

                Workflow::started() ? $record->person->id : $record->id
            )
        );
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsResolved($id)
    {
        $record = $this->recordsRepository->findById($id);
        $progress = $this->progressesRepository->create([
            'original' => 'Protocolo finalizado sem observações em ' . now() . '.',
            'progress_type_id' => app(ProgressTypesRepository::class)->findByName('Finalização')
                ->id,
            'record_id' => $record->id,
        ]);

        $this->recordsRepository->markAsResolved($record->id, $progress);

        $record->sendNotifications();

        $this->showSuccessMessage('Protocolo finalizado com sucesso.');

        return redirect()->route(Workflow::started() ? 'people_addresses.create' : 'people.show', [
            'person_id' => $record->person->id,
        ]);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reopen($id)
    {
        $record = $this->recordsRepository->findById($id)->reopen();

        $progress = $this->progressesRepository->create([
            'original' => 'Protocolo reaberto sem observações em ' . now() . '.',
            'progress_type_id' => app(ProgressTypesRepository::class)->findByName('Reabertura')->id,
            'record_id' => $record->id,
        ]);

        $this->recordsRepository->markAsNotResolved($record->id);

        $record->sendNotifications();

        $this->showSuccessMessage('Protocolo reaberto com sucesso.');

        return redirect()->route(Workflow::started() ? 'people_addresses.create' : 'people.show', [
            'person_id' => $record->person->id,
        ]);
    }

    /**
     * @param $id
     */
    public function show($id)
    {
        if (!($record = $this->recordsRepository->findById($id))) {
            abort(404);
        }

        $person = $this->peopleRepository->findById($record->person_id);

        return view('callcenter.records.form')
            ->with($this->getComboBoxMenus($record))
            ->with('progresses', $this->progressesRepository->allWherePaginate('record_id', $id))
            ->with('record', $record)
            ->with('person', $person)
            ->with('has_email', $record->person->emails->count() > 0 ? true : false);
    }

    public function index()
    {
        return view('callcenter.records.index')->with(
            'records',
            $this->recordsRepository->allPaginate()
        );
    }

    public function nonResolved()
    {
        return view('callcenter.records.index')->with([
            'records' => ($records = $this->recordsRepository->allNotResolved()),
            'onlyNonResolved' => true,
        ]);
    }

    public function showProtocol($record_id)
    {
        return view('callcenter.records.show-protocol')->with(
            'record',
            $this->recordsRepository->findById($record_id)
        );
    }

    public function showPublic($protocol)
    {
        $record = app(RecordsRepository::class)->findByProtocol($protocol);
        return !$record || (!Auth::user() && $record->access_code)
            ? abort(404)
            : view('callcenter.records.show-public')->with('record', $record);
    }

    public function searchProtocol()
    {
        return view('callcenter.records.search');
    }

    public function showByProtocolNumber(Request $request)
    {
        $record = app(RecordsRepository::class)->findByProtocol($request->protocol);

        return view('callcenter.records.search')
            ->with('record', $record)
            ->with('protocol', $request->protocol);
    }

    public function getRecordsData()
    {
        return [
            'committees' => app(CommittesRepository::class)->allOrderBy('name'),
            'areas' => app(AreasRepository::class)->allOrderBy('name'),
            'recordTypes' => app(RecordTypesRepository::class)->allOrderBy('name'),
        ];
    }

    public function advancedSearch(AdvancedSearchRequest $request)
    {
        $data = $request->all();

        $data['per_page'] = $data['per_page'] ?? 5;
        $data['page'] = $data['page'] ?? 1;

        $records = app(RecordsRepository::class)->advancedSearch($data);
        return view('callcenter.records.advanced-search')
            ->with('records', $records)
            ->with($this->getRecordsData())
            ->with($data)
            ->with('mode', 'advanced-search')
            ->with('pageSizes', [
                ['label' => '5', 'value' => '5'],
                ['label' => '10', 'value' => '10'],
                ['label' => '15', 'value' => '15'],
                ['label' => '25', 'value' => '25'],
                ['label' => '50', 'value' => '50'],
                ['label' => '100', 'value' => '100'],
                ['label' => '250', 'value' => '250'],
                ['label' => 'TODOS', 'value' => 'all'],
            ])
            ->with('per_page', $data['per_page']);
    }

    private function storeRecord(RecordRequest $request)
    {
        $record = $this->recordsRepository->create(coollect($request->all()));

        if (is_null($request->get('record_id'))) {
            //Se é um protocolo novo
            $request->merge(['record_id' => $record->id]);
            $request->merge([
                'progress_type_id' => app(ProgressTypesRepository::class)->findByName('Entrada')
                    ->id,
            ]);
            $request->merge([
                'is_private' => 1,
            ]);
            $progress = $this->progressesRepository->createFromRequest($request);

            $this->progressesRepository->attachFilesFromRequest($request, $progress->id);
        }

        return $record;
    }
}
