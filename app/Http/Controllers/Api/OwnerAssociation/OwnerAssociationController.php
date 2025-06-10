<?php

namespace App\Http\Controllers\Api\OwnerAssociation;

use App\Models\User\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\AccountCreationJob;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use App\Repositories\OwnerAssociationRepository;
use App\Http\Requests\OwnerAssociation\StoreRequest;
use App\Http\Requests\OwnerAssociation\UpdateRequest;
use App\Http\Resources\OwnerAssociation\OwnerAssociationResource;

class OwnerAssociationController extends Controller
{
    private $repository;

    public function __construct(OwnerAssociationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $data = $this->repository->list($request);
            return response()->json(["success" => true, "message" => 'Data Found', "error" => [], 'data' => $data->paginate($request->per_page ?? 10)], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(StoreRequest $request)
    {
        try {
            $data = $this->repository->store($request->validated());
            $this->AssignedPermisionToOwnerAssociation($data->id);
            $this->CreateUser($data);
            return response()->json(['success' => true, 'error' => [], 'data' => new OwnerAssociationResource($data), 'message' => 'Owner Association created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $data = $this->repository->update($id, $request->validated());
            $this->UpdateUser($data);
            return response()->json(['success' => true, 'error' => [], 'data' =>  new OwnerAssociationResource($data), 'message' => 'Owner Association updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->repository->delete($id);
            return response()->json(['success' => true, 'error' => [], 'data' =>  [], 'message' => 'Owner Association deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting owner association'], 500);
        }
    }

    public function changeStatus($id)
    {
        try {
            $data = $this->repository->changeStatus($id);
            return response()->json(['success' => true, 'error' => [], 'data' => $data, 'message' => 'Status updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error changing status'], 500);
        }
    }
    // app/Http/Controllers/Api/OwnerAssociationController.php

    public function show($id)
    {
        try {
            $ownerAssociation = $this->repository->show($id);

            // Using Resource to transform the data
            return response()->json([
                'success' => true,
                'error' => [],
                'data' => new OwnerAssociationResource($ownerAssociation),
                'message' => 'Owner Association details retrieved successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Owner Association not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving owner association details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function AssignedPermisionToOwnerAssociation($id)
    {
        $oaId = $id;
        $roles = [
            ['name' => 'Owner', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
            ['name' => 'Vendor', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
            ['name' => 'Managing Director', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
            ['name' => 'Financial Manager', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
            ['name' => 'Building Engineer', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
            ['name' => 'Operations Engineer', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
            ['name' => 'Operations Manager', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
            ['name' => 'Staff', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
            // ['name' => 'Admin', 'owner_association_id' => $oaId,'guard_name' => 'web'],
            ['name' => 'OA', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
            ['name' => 'Tenant', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
            ['name' => 'Security', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
            ['name' => 'Technician', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
            ['name' => 'Accounts Manager', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
            ['name' => 'MD', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
            ['name' => 'Complaint Officer', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
            ['name' => 'Legal Officer', 'owner_association_id' => $oaId, 'guard_name' => 'web'],
        ];
        DB::table('roles')->insert($roles);

        $permissionsConfig = config('role-permission');
        // Log::info('oa_id' . $oaId);
        foreach ($permissionsConfig['roles'] as $roleName => $roleConfig) {
            $role = Role::where('name', $roleName)->where('owner_association_id', $oaId)->first();
            // Log::info("Role" . $role);
            if (isset($roleConfig['permissions'])) {
                $role->syncPermissions($roleConfig['permissions']);
            }
        }
        $allowedRoles = ['MD', 'OA', 'Owner', 'Vendor', 'Tenant', 'Technician', 'Security'];
        foreach ($allowedRoles as $role) {
            $userRole = Role::where('name', $role)->where('owner_association_id', $oaId)->first();
            $permission = Permission::all();
            $userRole->syncPermissions($permission);
        }
    }
    public function CreateUser($data)
    {

        // Create an entry in Users table
        // check if entered email and phone number is already present for other users in users table
        $emailexists = User::where(['email' => $data->email, 'phone' => $data->phone])->exists();
        if (!$emailexists) {
            $password = Str::random(12);

            $user = User::firstorcreate(
                [
                    'email'                => $data->email,
                    'phone'                => $data->phone,
                ],
                [
                    'first_name'           => $data->name,
                    'profile_photo'        => $data->profile_photo,
                    'role_id'              => Role::where('name', 'OA')->where('owner_association_id', $data->id)->value('id'),
                    'active'               => 1,
                    'password' => Hash::make($password),
                    'owner_association_id' => $data->id,
                    'email_verified' => 1,
                    'phone_verified' => 1,
                ]
            );
            $user->ownerAssociation()->attach($data->id, ['from' => now()->toDateString()]);
            $oa = Role::where('name', 'OA')->where('owner_association_id', $data->id)->first();
            DB::table('model_has_roles')->insert([
                'role_id' => $oa->id,
                'model_type' => User::class,
                'model_id' => $user->id,
            ]);
            $this->LazimAccountDatabase($data, $user, $password);
            // Send email with credentials
            $slug = $data->slug;
            AccountCreationJob::dispatch($user, $password, $slug);
        }
    }
    public function LazimAccountDatabase($data, $user, $password) {
        $connection = DB::connection('lazim_accounts');
        $building_id = DB::table('building_owner_association')->where('owner_association_id' , $data->id)->first()?->building_id;
        $connection = DB::connection('lazim_accounts');
        $connection->table('users')->insert([
            'name' => $data->name,
            'email'                => $data->email,
            'email_verified_at' => now(),
            'password'             => Hash::make($password),
            'type' => 'company',
            'lang' => 'en',
            'created_by' => 1,
            'plan' => 1,
            'owner_association_id' => $data->id,
            'building_id' => $building_id??NULL,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $accountUser = $connection->table('users')->where('email',$data->email)->where('owner_association_id',$data->id )->first();
        $role = $connection->table('roles')->where('name', 'company')->first();
        $connection->table('model_has_roles')->insertOrIgnore([
            'role_id' => $role?->id,
            'model_type' => 'App\Models\User',
            'model_id' => $accountUser?->id,
        ]);
    }
    public function UpdateUser($data)
    {
        $user = User::where('owner_association_id', $data->id)->where('phone',$data->phone)->where('email',$data->email)
        ->update([
            'first_name' => $data->name,
            'phone'      => $data->phone,
            'profile_photo' => $data->profile_photo,
            'active'  => $data->active,
        ]);
    }
}
