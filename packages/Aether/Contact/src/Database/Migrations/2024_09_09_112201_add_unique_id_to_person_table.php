<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecute las migraciones.
     */
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->string('unique_id')->nullable()->unique();
        });

        $persons = DB::table('persons')->get();
        foreach ($persons as $person) {
            $emails = json_decode($person->emails, true);
            $contactNumbers = json_decode($person->contact_numbers, true);
            $email = $emails[0]['value'] ?? '';
            $contact = $contactNumbers[0]['value'] ?? '';

            DB::table('persons')->where('id', $person->id)->update([
                'unique_id' => $person->user_id.'|'.$person->organization_id.'|'.$email.'|'.$contact,
            ]);
        }
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn('unique_id');
        });
    }
};
