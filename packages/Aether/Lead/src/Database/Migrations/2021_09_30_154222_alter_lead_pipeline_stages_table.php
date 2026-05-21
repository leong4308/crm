<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecute las migraciones.
     *
     * @return void
     */
    public function up()
    {
        $tablePrefix = DB::getTablePrefix();

        Schema::table('lead_pipeline_stages', function (Blueprint $table) {
            $table->string('code')->after('id')->nullable();
            $table->string('name')->after('code')->nullable();
        });

        $pipelineStages = DB::table('lead_pipeline_stages')->get();
        foreach ($pipelineStages as $stage) {
            $leadStage = DB::table('lead_stages')->where('id', $stage->lead_stage_id)->first();
            if ($leadStage) {
                DB::table('lead_pipeline_stages')->where('id', $stage->id)->update([
                    'code' => $leadStage->code,
                    'name' => $leadStage->name,
                ]);
            }
        }

        Schema::table('lead_pipeline_stages', function (Blueprint $table) use ($tablePrefix) {
            $table->dropForeign($tablePrefix.'lead_pipeline_stages_lead_stage_id_foreign');
            $table->dropColumn('lead_stage_id');

            $table->unique(['code', 'lead_pipeline_id']);
            $table->unique(['name', 'lead_pipeline_id']);
        });
    }

    /**
     * Revertir las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_pipeline_stages', function (Blueprint $table) {
            $table->dropColumn('code');
            $table->dropColumn('name');

            $table->integer('lead_stage_id')->unsigned();
            $table->foreign('lead_stage_id')->references('id')->on('lead_stages')->onDelete('cascade');

            $table->dropUnique(['lead_pipeline_stages_code_lead_pipeline_id_unique', 'lead_pipeline_stages_name_lead_pipeline_id_unique']);
        });
    }
};
