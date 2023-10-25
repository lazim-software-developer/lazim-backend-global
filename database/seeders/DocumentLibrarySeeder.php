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
            ['id' => 1, 'name' => 'Passport', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant'],
            ['id' => 2, 'name' => 'Visa', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant'],
            ['id' => 3, 'name' => 'Eid', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant'],
            ['id' => 4, 'name' => 'Title deed', 'url'=>'dev/B5iVUw6NbQ4bSkss0cUQ3fmIRSippW-metaU2NyZWVuc2hvdCAoMTEpLnBuZw==-.png', 'type'=>'tenant'],
        ];
        DocumentLibrary::insert($doc);
    }
}
