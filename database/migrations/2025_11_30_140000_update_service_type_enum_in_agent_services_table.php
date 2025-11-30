<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Change to VARCHAR to allow any value temporarily
        DB::statement("ALTER TABLE agent_services MODIFY COLUMN service_type VARCHAR(255) DEFAULT 'not_selected'");

        // Step 2: Update existing records to match the new uppercase format
        DB::table('agent_services')->where('service_type', 'bvn_search')->update(['service_type' => 'BVN_SEARCH']);
        DB::table('agent_services')->where('service_type', 'bvn_modification')->update(['service_type' => 'BVN_MODIFICATION']);
        DB::table('agent_services')->where('service_type', 'crm')->update(['service_type' => 'CRM']);
        DB::table('agent_services')->where('service_type', 'bvn_user')->update(['service_type' => 'BVN_USER']);
        DB::table('agent_services')->where('service_type', 'approval_request')->update(['service_type' => 'APPROVAL REQUEST']);
        DB::table('agent_services')->where('service_type', 'affidavit')->update(['service_type' => 'AFFIDAVIT']);
        DB::table('agent_services')->where('service_type', 'nin_selfservice')->update(['service_type' => 'NIN_SELFSERVICE']);
        DB::table('agent_services')->where('service_type', 'nin_validation')->update(['service_type' => 'NIN_VALIDATION']);
        DB::table('agent_services')->where('service_type', 'ipe')->update(['service_type' => 'IPE']);
        DB::table('agent_services')->where('service_type', 'nin_modification')->update(['service_type' => 'NIN MODIFICATION']);
        
        // Also handle 'cac_registration' and 'corporate'/'individual' which I introduced recently if they exist
        DB::table('agent_services')->where('service_type', 'cac_registration')->update(['service_type' => 'CAC']);
        DB::table('agent_services')->where('service_type', 'individual')->update(['service_type' => 'TIN INDIVIDUAL']);
        DB::table('agent_services')->where('service_type', 'corporate')->update(['service_type' => 'TIN COOPERATE']);


        // Step 3: Restrict ENUM to only the requested new values
        DB::statement("ALTER TABLE agent_services MODIFY COLUMN service_type ENUM(
            'VNIN TO NIBSS',
            'BVN_SEARCH',
            'BVN_MODIFICATION',
            'CRM',
            'BVN_USER',
            'APPROVAL REQUEST',
            'AFFIDAVIT',
            'NIN_SELFSERVICE',
            'NIN_VALIDATION',
            'IPE',
            'NIN MODIFICATION',
            'TIN INDIVIDUAL',
            'TIN COOPERATE',
            'CAC',
            'SERVICE_001',
            'SERVICE_002',
            'SERVICE_003',
            'not_selected'
        ) DEFAULT 'not_selected'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the previous list if needed. 
        // Note: This might fail if there are values in the table that are not in the old list.
        DB::statement("ALTER TABLE agent_services MODIFY COLUMN service_type ENUM(
            'VNIN TO NIBSS', 
            'bvn_search', 
            'bvn_modification', 
            'crm', 
            'bvn_user', 
            'approval_request', 
            'affidavit', 
            'nin_selfservice', 
            'nin_validation',
            'ipe', 
            'not_selected', 
            'nin_modification'
        ) DEFAULT 'not_selected'");
    }
};
