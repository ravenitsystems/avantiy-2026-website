<?php

return [
    'permission_groups' => [
        [
            'key' => 'team_management',
            'name' => 'Team Management',
            'description' => 'Permissions for inviting, removing, and managing team members and their roles.',
            'sort_order' => 100,
            'is_high_impact' => true,
        ],
        [
            'key' => 'clients',
            'name' => 'Clients',
            'description' => 'Permissions for viewing and managing client records.',
            'sort_order' => 200,
            'is_high_impact' => true,
        ],
        [
            'key' => 'websites',
            'name' => 'Websites',
            'description' => 'Permissions for viewing and managing website records.',
            'sort_order' => 300,
            'is_high_impact' => true,
        ],
        [
            'key' => 'conversation',
            'name' => 'Conversation',
            'description' => 'Permissions for the team conversation stream.',
            'sort_order' => 400,
            'is_high_impact' => false,
        ],
    ],

    'permissions' => [
        ['key' => 'team.invite_user', 'label' => 'Invite users', 'description' => 'Can invite new members to the team.', 'group_key' => 'team_management', 'is_high_impact' => false, 'sort_order' => 100],
        ['key' => 'team.remove_user', 'label' => 'Remove users', 'description' => 'Can remove members from the team. Use with care.', 'group_key' => 'team_management', 'is_high_impact' => true, 'sort_order' => 101],
        ['key' => 'team.change_user_roles', 'label' => 'Change user roles', 'description' => 'Can assign or change roles for team members. Can elevate privileges.', 'group_key' => 'team_management', 'is_high_impact' => true, 'sort_order' => 102],
        ['key' => 'team.edit_roles', 'label' => 'Edit roles', 'description' => 'Can create or edit custom roles and their permissions. Affects team-wide access.', 'group_key' => 'team_management', 'is_high_impact' => true, 'sort_order' => 103],

        ['key' => 'clients.view', 'label' => 'View clients', 'description' => 'Can view client records.', 'group_key' => 'clients', 'is_high_impact' => false, 'sort_order' => 200],
        ['key' => 'clients.edit', 'label' => 'Create / edit clients', 'description' => 'Can create and edit client records.', 'group_key' => 'clients', 'is_high_impact' => false, 'sort_order' => 201],
        ['key' => 'clients.delete', 'label' => 'Delete clients', 'description' => 'Can permanently delete clients. This action cannot be undone.', 'group_key' => 'clients', 'is_high_impact' => true, 'sort_order' => 202],

        ['key' => 'websites.view', 'label' => 'View websites', 'description' => 'Can view website records.', 'group_key' => 'websites', 'is_high_impact' => false, 'sort_order' => 300],
        ['key' => 'websites.edit', 'label' => 'Create / edit websites', 'description' => 'Can create and edit website records.', 'group_key' => 'websites', 'is_high_impact' => false, 'sort_order' => 301],
        ['key' => 'websites.delete', 'label' => 'Delete websites', 'description' => 'Can permanently delete websites. This action cannot be undone.', 'group_key' => 'websites', 'is_high_impact' => true, 'sort_order' => 302],

        ['key' => 'messages.view', 'label' => 'View team conversation', 'description' => 'Can view the team conversation stream.', 'group_key' => 'conversation', 'is_high_impact' => false, 'sort_order' => 400],
        ['key' => 'messages.send', 'label' => 'Post to team conversation', 'description' => 'Can post messages to the team conversation.', 'group_key' => 'conversation', 'is_high_impact' => false, 'sort_order' => 401],
    ],

    'preset_roles' => [
        'owner' => [
            'name' => 'Owner',
            'description' => 'Full access to all team features.',
            'permissions' => null,
        ],
        'admin' => [
            'name' => 'Admin',
            'description' => 'Can manage team members, clients, and websites.',
            'permissions' => [
                'team.invite_user',
                'team.remove_user',
                'team.change_user_roles',
                'team.edit_roles',
                'clients.view',
                'clients.edit',
                'clients.delete',
                'websites.view',
                'websites.edit',
                'websites.delete',
                'messages.view',
                'messages.send',
            ],
        ],
        'member' => [
            'name' => 'Member',
            'description' => 'Basic access to view assets and participate in conversations.',
            'permissions' => [
                'clients.view',
                'websites.view',
                'messages.view',
                'messages.send',
            ],
        ],
    ],
];
