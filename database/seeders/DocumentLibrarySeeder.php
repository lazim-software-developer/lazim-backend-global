<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Master\DocumentLibrary;

class DocumentLibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documents = [
            ['name' => 'Passport', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'master'],
            ['name' => 'Visa', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'master'],
            ['name' => 'Eid', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'master'],
            ['name' => 'Title deed', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'master'],
            ['name' => 'Acceptance from Developer', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
            ['name' => 'Paid receipt of services', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
            ['name' => 'Tenancy Contract', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
            ['name' => 'DEWA', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
            ['name' => 'Tenancy Contract', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
            [ 'name' => 'Cooling Registration', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
            [ 'name' => 'Gas Registration', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
            [ 'name' => 'Vehicle Registration', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
            [ 'name' => 'company license', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
            [ 'name' => 'Security Deposit', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
        ];
        DocumentLibrary::insert($documents);
    }
}
