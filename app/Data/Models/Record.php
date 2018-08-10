<?php
namespace App\Data\Models;

class Record extends BaseModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'protocol',
        'committee_id',
        'person_id',
        'record_type_id',
        'area_id',
        'subject',
        'answer_address_id',
        'send_answer_by_email',
        'answer',
        'answered_at',
        'answered_by_id',
        'origin_id',
        'resolved_at',
        'resolved_by_id',
    ];

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    public function recordType()
    {
        return $this->belongsTo(RecordType::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
