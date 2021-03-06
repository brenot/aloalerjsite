<?php

use App\Models\Audit as AuditModel;
use App\Models\Committee as CommitteeModel;
use App\Models\User as UserModel;
use Illuminate\Database\Migrations\Migration;
use App\Models\Progress as ProgressModel;
use App\Models\Record as RecordModel;
use Carbon\Carbon;

class FixOriginCommittee2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (
            RecordModel::whereDate(
                'created_at',
                '>=',
                Carbon::createFromFormat('Y-m-d', '2020-08-01')
            )
                ->whereDate('created_at', '<=', Carbon::createFromFormat('Y-m-d', '2020-10-01'))
                ->cursor()
            as $record
        ) {
            $progress = $record->firstProgress();

            $audit = AuditModel::where('auditable_id', $progress->id)
                ->where('event', 'created')
                ->where('auditable_type', 'App\Models\Progress')
                ->first();

            if ($audit && ($userId = $audit->user_id)) {
                $oldCommitteeId = $progress->created_by_committee_id;

                $progress->created_by_id = $userId;

                $progress->save();

                if ($creator = $progress->creator) {
                    $newCommitteeId = $progress->created_by_committee_id = $creator->originCommittee()->id;
                } else {
                    $newCommitteeId = $progress->created_by_committee_id = CommitteeModel::where(
                        'slug',
                        'alo-alerj'
                    )->first()->id;
                }

                if ($oldCommitteeId != $newCommitteeId) {
                    dump(
                        "Alterando o progress {$progress->id} da comissão {$oldCommitteeId} para a comissão {$newCommitteeId}"
                    );
                } else {
                    dump(
                        "Mantendo o progress {$progress->id} na comissão {$progress->created_by_committee_id}"
                    );
                }

                $progress->save();
            } else {
                dump(
                    "Não foi possível resgatar o audit do progress {$progress->id}. Audit encontrado = " .
                        $audit->id ??
                        'null'
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
