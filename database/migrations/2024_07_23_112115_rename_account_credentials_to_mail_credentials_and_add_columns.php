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
        Schema::rename('account_credentials', 'mail_credentials');

         Schema::table('mail_credentials', function (Blueprint $table) {
            $table->string('mailer')->nullable()->after('password');
            $table->string('username')->nullable()->after('email');
            $table->string('host')->nullable()->after('mailer');
            $table->string('port')->nullable()->after('host');
            $table->string('encryption')->nullable()->after('port');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mail_credentials', function (Blueprint $table) {
            
            $table->dropColumn(['mailer', 'username', 'host', 'port', 'encryption']);
            
        });
        Schema::rename('mail_credentials', 'account_credentials');
    }
};
