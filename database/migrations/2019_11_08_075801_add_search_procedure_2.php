<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSearchProcedure2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "
            CREATE PROCEDURE `search`(text TEXT)
            BEGIN
                 SELECT * FROM vacancies WHERE position LIKE CONCAT('%',text,'%') OR salary LIKE CONCAT('%',text,'%');
            END
        ";

        DB::unprepared("DROP procedure IF EXISTS search");
        DB::unprepared($procedure);
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
