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
            ['name' => 'Other Douments', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant', 'label'=>'master']
        ];
        DocumentLibrary::insert($documents);
    }
}
