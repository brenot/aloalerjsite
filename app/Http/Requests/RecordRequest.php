<?php
namespace App\Http\Requests;

use Illuminate\Auth\Access\Gate;

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
            'committee_id' => 'required', // Comissão
            'record_type_id' => 'required', //Tipo
            'progress_type_id' => 'required_without:record_id', // Assunto → Workflow
            'area_id' => 'required', //Area
            'original' => 'required_without:record_id', // Solicitação  → Workflow
        ];
    }

    public function authorize()
    {
        return \Gate::allows('committee-canEdit', [
            $this->request->get('committee_id'),
            $this->user()->id,
        ]);
    }
}
