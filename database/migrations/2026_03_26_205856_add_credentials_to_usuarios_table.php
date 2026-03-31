<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            //
        });
    }// database/migrations/xxxx_add_credentials_to_usuarios_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('usuario', 60)->unique()->after('apellido');
            $table->string('password')->after('usuario');
            $table->string('email', 150)->nullable()->unique()->after('password');
            $table->rememberToken()->after('email');
            $table->timestamp('email_verified_at')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['usuario', 'password', 'email', 'remember_token', 'email_verified_at']);
        });
    }
};

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            //
        });
    }
};
