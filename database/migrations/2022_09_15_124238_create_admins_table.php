<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Admin;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('admin_id')->unique();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->bigInteger('flags')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        $admin = new Admin;
		$admin->admin_id = (String) Str::uuid();
		$admin->name = 'Admin';
		$admin->email = 'admin@gmail.com';
		$admin->password = Hash::make('123123');
		$admin->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admins');
    }
}
