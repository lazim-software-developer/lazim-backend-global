<?php

return [
    'shield_resource' => [
        'should_register_navigation' => true,
        'slug' => 'shield/roles',
        'navigation_sort' => -1,
        'navigation_badge' => true,
        'navigation_group' => true,
        'is_globally_searchable' => false,
        'show_model_path' => true,
        'is_scoped_to_tenant' => false,
    ],

    'auth_provider_model' => [
        'fqcn' => 'App\\Models\\User\\User',
    ],

    'super_admin' => [
        'enabled' => true,
        'name' => 'Admin',
        'define_via_gate' => false,
        'intercept_gate' => 'before', // after
    ],

    'panel_user' => [
        'enabled' => false,
        'name' => 'panel_user',
    ],

    'permission_prefixes' => [
        'resource' => [
            'view',
            'view_any',
            'create',
            'update',
            // 'restore',
            // 'restore_any',
            // 'replicate',
            // 'reorder',
            // 'delete',
            // 'delete_any',
            // 'force_delete',
            // 'force_delete_any',
        ],

        'page' => 'page',
        'widget' => 'widget',
    ],

    'entities' => [
        'pages' => true,
        'widgets' => true,
        'resources' => true,
        'custom_permissions' => false,
    ],

    'generator' => [
        'option' => 'policies_and_permissions',
        'policy_directory' => 'Policies',
    ],

    'exclude' => [
        'enabled' => false,

        'pages' => [
            // 'Dashboard',
            // 'OwnerAssociationInvoice',
            // 'OwnerAssociationReceipt',
            // 'AgingReport',
            // 'DelinquentOwners',
            // 'BudgetListing',
            // 'CreateTender',
            // 'ListAllReceipts'
        ],

        'widgets' => [
            // 'AccountWidget',
            // 'FilamentInfoWidget',
            // 'FormsChart',
            // 'TasksChart',
            // 'ContractsOverview',
            // 'FacilityBookingOverview',
            // 'HappinessCenterChart',
            // 'ProposalOverview',
            // 'RegistrationChart',
        ],

        'resources' => [
            // 'AccountsManagerResource',
            // 'BuildingPocResource',
            // 'DocumentsResource',
            // 'BuildingEngineerResource',
            // 'ComplaintOfficerResource',
            // 'FloorResource',
            // 'LegalOfficerResource',
            // 'MDResource',
            // 'CityResource',
            // 'DocumentLibraryResource',
            // 'RoleResource',
            // 'MediaResource',
            // 'AttendanceResource',
            // 'ComplaintResource',
            // 'FlatDocumentResource',
            // 'SnaggingResource',
            // 'OAMReceiptsResource',
            // 'OAMInvoiceResource',
            // 'BuildingDocumentResource',
            // 'FlatDomesticHelpResource',
            // 'TenantcomplaintResource',
            // 'ContactsResource'
        ],
    ],

    'discovery' => [
        'discover_all_resources' => true,
        'discover_all_widgets' => true,
        'discover_all_pages' => true,
    ],

    'register_role_policy' => [
        'enabled' => true,
    ],

];
