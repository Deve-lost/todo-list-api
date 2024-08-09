<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained();
            $table->string('todo_name', 191);
            $table->tinyInteger('status')->default(0)->comment('0 = Pending, 1 = Done');
            $table->integer('checklist_by');
            $table->integer('checklist_by_profile');
            $table->json('checklist_history');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
