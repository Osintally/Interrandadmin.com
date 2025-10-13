<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_settings', 'very_late_threshold_minutes')) {
                $table->integer('very_late_threshold_minutes')->default(30)->after('late_threshold_minutes');
            }
            if (!Schema::hasColumn('attendance_settings', 'early_leave_threshold_minutes')) {
                $table->integer('early_leave_threshold_minutes')->default(30)->after('very_late_threshold_minutes');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $columnsToDropCheck = [];
            if (Schema::hasColumn('attendance_settings', 'very_late_threshold_minutes')) {
                $columnsToDropCheck[] = 'very_late_threshold_minutes';
            }
            if (Schema::hasColumn('attendance_settings', 'early_leave_threshold_minutes')) {
                $columnsToDropCheck[] = 'early_leave_threshold_minutes';
            }
            if (!empty($columnsToDropCheck)) {
                $table->dropColumn($columnsToDropCheck);
            }
        });
    }
};
