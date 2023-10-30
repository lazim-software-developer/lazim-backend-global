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
        $doc = [
            ['id' => 1, 'name' => 'Passport', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'master'],
            ['id' => 2, 'name' => 'Visa', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'master'],
            ['id' => 3, 'name' => 'Eid', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'master'],
            ['id' => 4, 'name' => 'Title deed', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'master'],
            ['id' => 5, 'name' => 'Acceptance from Developer', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
            ['id' => 6, 'name' => 'Paid receipt of services', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
            ['id' => 7, 'name' => 'Tenancy Contract', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
            ['id' => 8, 'name' => 'DEWA', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
            ['id' => 9, 'name' => 'Tenancy Contract', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
            ['id' => 10, 'name' => 'Cooling Registration', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
            ['id' => 11, 'name' => 'Gas Registration', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
            ['id' => 12, 'name' => 'Vehicle Registration', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
            ['id' => 13, 'name' => 'company license', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
            ['id' => 14, 'name' => 'Security Deposit', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'move-in'],
        ];
        DocumentLibrary::insert($doc);
    }
}
