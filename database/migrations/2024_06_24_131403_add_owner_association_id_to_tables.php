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
        Schema::table('aging_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('apartment_owners', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('asset_maintenance', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('building_facility', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('building_post', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });
        
        Schema::table('building_service', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('building_vendor', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });
        
        Schema::table('cooling_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('delinquent_owners', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('emergency_numbers', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('enquiries', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('general_funds', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('invoice_audit', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('invoice_status', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('item_inventory', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('item_vendor', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('mollak_tenants', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('oa_service_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('oacomplaint_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('oam_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('oam_receipts', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('offer_promotions', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('owner_committees', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('patrollings', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('polls', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('ppm', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('rule_regulations', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('service_technician_vendor', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('service_vendor', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('technician_assets', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('technician_vendors', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('tender_vendors', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('user_approvals', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->unsignedBigInteger('flat_id')->nullable();

            $table->foreign('owner_association_id')->references('id')->on('owner_association');
            $table->foreign('flat_id')->references('id')->on('flats');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->unsignedBigInteger('flat_id')->nullable();

            $table->foreign('owner_association_id')->references('id')->on('owner_association');
            $table->foreign('flat_id')->references('id')->on('flats');
        });

        Schema::table('vendor_escalation_matrix', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('vendor_managers', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('visitors', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('wda', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

        Schema::table('wda_audit', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_association_id')->nullable();
            $table->foreign('owner_association_id')->references('id')->on('owner_association');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aging_reports', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('apartment_owners', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('asset_maintenance', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('building_facility', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('building_post', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('building_service', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('building_vendor', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('cooling_accounts', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('delinquent_owners', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('emergency_numbers', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('general_funds', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('invoice_audit', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('invoice_status', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('item_inventory', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('item_vendor', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('mollak_tenants', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });
        
        Schema::table('oa_service_requests', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('oacomplaint_reports', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });
        
        Schema::table('oam_invoices', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });
        
        Schema::table('oam_receipts', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });
        
        Schema::table('offer_promotions', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });
        
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });
        
        Schema::table('owner_committees', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('patrollings', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('polls', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('ppm', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('rule_regulations', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('service_technician_vendor', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('service_vendor', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('technician_assets', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('technician_vendors', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('tender_vendors', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });

        Schema::table('user_approvals', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
            $table->dropColumn('flat_id');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
            $table->dropColumn('flat_id');
        });

        Schema::table('vendor_escalation_matrix', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });
        
        Schema::table('vendor_managers', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });
        
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });
        
        Schema::table('wda', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });
        
        Schema::table('wda_audit', function (Blueprint $table) {
            $table->dropColumn('owner_association_id');
        });
    }
};
