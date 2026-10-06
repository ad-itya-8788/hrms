<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveApplicationsTable extends Migration
{
    public function up()
    {
        Schema::create('leave_applications', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->unsignedBigInteger('employee_id');

            $table->string('leave_type', 100);

            $table->date('from_date');

            $table->date('to_date');

            $table->text('reason');

            $table->string('status', 30)
                ->default('Pending');

            $table->unsignedBigInteger('approved_by')
                ->nullable();

            $table->timestamp('approved_at')
                ->nullable();

            $table->text('admin_remark')
                ->nullable();

            $table->timestamps();

            $table->index('employee_id');
            $table->index('status');
            $table->index('from_date');
            $table->index('to_date');

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_applications');
    }
}