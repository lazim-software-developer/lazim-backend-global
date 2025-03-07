
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owner_association_invoices', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'overdue'])->nullable()
                ->after('invoice_number');
        });
    }

    public function down(): void
    {
        Schema::table('owner_association_invoices', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
