<?php

return [
    /**
     * Control if the seeder should create a user per role while seeding the data.
     */
    'create_users' => false,

    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'roles_structure' => [
//        'super_admin' => [
//            'admins' => 'c,r,u,d',
//            'users' => 'c,r,u,d',
//            'payments' => 'c,r,u,d',
//            'profiles' => 'r,u',
//            'roles' => 'c,r,u,d',
//        ],
        'merchant_admin' => [
            'users' => 'c,r,u,d',
            'products' => 'c,r,u,d',
            'orders' => 'c,r,u,d',
            'roles' => 'c,r,u,d',
        ],
    ],

    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete',
    ],

    'private_roles' => [
        'super_admin',
    ],

    'not_editable_roles' => [
        'super_admin',
        'merchant_admin',
    ],

    'roles_translations' => [
        'super_admin' => [
            'display_name' => [
                'ar' => 'مدير النظام',
                'en' => 'Super Administrator'
            ],
            'description' => [
                'ar' => null,
                'en' => null
            ],
        ],
        'merchant_admin' => [
            'display_name' => [
                'ar' => 'مدير حساب التاجر',
                'en' => 'Merchant Administrator'
            ],
            'description' => [
                'ar' => null,
                'en' => null
            ],
        ],
    ],

    'permissions_map_translations' => [
        'create' => [
            'ar' => 'إنشاء',
            'en' => 'Create'
        ],
        'read' => [
            'ar' => 'عرض',
            'en' => 'Read'
        ],
        'update' => [
            'ar' => 'تحديث',
            'en' => 'Update'
        ],
        'delete' => [
            'ar' => 'حذف',
            'en' => 'Delete'
        ],
    ],

    'permissions_module_translations' => [
        'admins' => [
            'ar' => 'المديرين',
            'en' => 'Admins'
        ],
        'users' => [
            'ar' => 'المستخدمين',
            'en' => 'Users'
        ],
        'payments' => [
            'ar' => 'المدفوعات',
            'en' => 'Payments'
        ],
        'profiles' => [
            'ar' => 'الملفات الشخصية',
            'en' => 'Profiles'
        ],
        'products' => [
            'ar' => 'المنتجات',
            'en' => 'Products'
        ],
        'orders' => [
            'ar' => 'الطلبات',
            'en' => 'Orders'
        ],
    ]
];
