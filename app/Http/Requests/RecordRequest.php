<?php

namespace App\Http\Requests;

use App\Rules\AuthorizedCommitteeUser;
use App\Rules\ContactWorkflow;

class RecordRequest extends Request
{
    protected $errorBag = 'validation';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'origin_id' => 'required_without:record_id', // Origem → Workflow
            'committee_id' => ['required', new AuthorizedCommitteeUser()], // Comissão
            'record_type_id' => 'required', //Tipo
            'area_id' => 'required', //Area
            'cpf_cnpj' => 'sometimes|required_if:is_anonymous,==,false|cpf_cnpj',
            'name' => 'required_if:is_anonymous,==,false',
            'original' => 'required_without:record_id', // Solicitação  → Workflow
            'mobile' => [
                $this->is_anonymous_protocol()
                    ? ''
                    : 'required_without_all:whatsapp,phone,person_id',
            ],
            'whatsapp' => [
                $this->is_anonymous_protocol() ? '' : 'required_without_all:mobile,phone,person_id',
            ],
            'phone' => [
                $this->is_anonymous_protocol()
                    ? ''
                    : 'required_without_all:mobile,whatsapp,person_id',
            ],
            'email' => [
                $this->is_anonymous_protocol()
                    ? ''
                    : ($this->has_send_answer_by_email_on()
                        ? 'email'
                        : ''),
            ],
            'neighbourhood' => [$this->create_address() ? 'required' : ''],
            'city' => [$this->create_address() ? 'required' : ''],
            'state' => [$this->create_address() ? 'required' : ''],
        ];
    }

    private function has_send_answer_by_email_on()
    {
        return !is_null($this->send_answer_by_email) && $this->send_answer_by_email == 'on';
    }

    private function is_anonymous_protocol()
    {
        return $this->is_anonymous == 'true';
    }

    private function create_address()
    {
        return $this->create_address == 'true';
    }

    public function sanitize()
    {
        if (!empty($this->get('cpf_cnpj'))) {
            $input = $this->all();

            $input['cpf_cnpj'] = only_numbers($input['cpf_cnpj']);

            $this->replace($input);
        }

        if (!empty($this->get('files_array'))) {
            $input = $this->all();

            $input['files_array'] = json_decode($this->get('files_array'));

            $this->replace($input);
        }

        return $this->all();
    }
}
