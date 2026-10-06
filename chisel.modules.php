<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Optional modules — single source of truth
|--------------------------------------------------------------------------
|
| The starter ships three optional modules (Notes, Posts, Passkeys). This file
| declares what each one owns, so two consumers can never disagree:
|
|   chisel.php                    — removes a module (files, marked blocks,
|                                   routes, tests, docs, npm dependencies)
|   scripts/module-integrity.php  — the gate: a module is either fully present
|                                   or fully gone, never half-removed
|
| Add a module here first, then teach chisel.php how to remove it.
|
| - `anchor`   the file whose presence means "this module is here"
| - `owned`    files that exist only while the module does (chisel deletes them)
| - `shared`   files the module also touches: marker-carrying blocks chisel
|              strips, plus imports/AST it edits
| - `generated` Wayfinder output the module's routes and controllers produced
|              (gitignored, so nothing else prunes it: a stale helper is an
|              importable route to a 404 — the command deletes it, then
|              regenerates)
| - `residue`  [file, needle] pairs that MUST be gone once the module is gone —
|              this is what catches a hand-deletion chisel never performed
|
*/

return [
    'modules' => [
        'notes' => [
            'label' => 'Notes — team-scoped CRUD reference (list, detail, create/edit, policies, tests)',
            'anchor' => 'app/Models/Note.php',
            'owned' => [
                'app/Http/Controllers/Notes/NoteController.php',
                'app/Http/Requests/Notes/StoreNoteRequest.php',
                'app/Http/Requests/Notes/UpdateNoteRequest.php',
                'app/Models/Note.php',
                'app/Policies/NotePolicy.php',
                'database/factories/NoteFactory.php',
                'database/migrations/2026_07_19_041832_create_notes_table.php',
                'resources/js/components/notes/delete-note-dialog.tsx',
                'resources/js/components/notes/markdown-editor.tsx',
                'resources/js/components/notes/note-breadcrumbs.ts',
                'resources/js/components/notes/note-form.tsx',
                'resources/js/components/notes/note-list.tsx',
                'resources/js/pages/notes/create.tsx',
                'resources/js/pages/notes/edit.tsx',
                'resources/js/pages/notes/index.tsx',
                'resources/js/pages/notes/show.tsx',
                'tests/Feature/Notes/NoteTest.php',
            ],
            'generated' => [
                'resources/js/routes/notes',
                'resources/js/actions/App/Http/Controllers/Notes',
            ],
            'shared' => [
                'routes/web/app.php',
                'app/Models/Team.php',
                'app/Http/Controllers/DashboardController.php',
                'database/seeders/DatabaseSeeder.php',
                'resources/js/lib/nav.ts',
                'resources/js/pages/dashboard.tsx',
                'tests/Feature/DashboardTest.php',
                'AGENTS.md',
                'README.md',
            ],
            'residue' => [
                ['routes/web/app.php', 'NoteController'],
                ['app/Models/Team.php', 'Note'],
                ['app/Http/Controllers/DashboardController.php', 'Note'],
                ['database/seeders/DatabaseSeeder.php', 'Note::upsert'],
                ['resources/js/lib/nav.ts', 'notesIndex'],
                ['resources/js/pages/dashboard.tsx', 'recentNotes'],
                ['tests/Feature/DashboardTest.php', 'stats.notes'],
                ['AGENTS.md', '**Notes**'],
                ['README.md', '@chisel-notes'],
            ],
        ],

        'posts' => [
            'label' => 'Posts — public content reference (index, detail, markdown body)',
            'anchor' => 'app/Models/Post.php',
            'owned' => [
                'app/Http/Controllers/Public/PostController.php',
                'app/Models/Post.php',
                'database/factories/PostFactory.php',
                'database/migrations/2026_07_30_034924_create_posts_table.php',
                'resources/js/pages/public/posts/index.tsx',
                'resources/js/pages/public/posts/show.tsx',
                'tests/Feature/Posts/PostTest.php',
            ],
            'generated' => [
                'resources/js/routes/posts',
                'resources/js/actions/App/Http/Controllers/Public',
            ],
            'shared' => [
                'routes/web/public.php',
                'database/seeders/DatabaseSeeder.php',
                'resources/js/lib/nav.ts',
                'AGENTS.md',
                'README.md',
            ],
            'residue' => [
                ['routes/web/public.php', 'PostController'],
                ['database/seeders/DatabaseSeeder.php', 'Post::upsert'],
                ['resources/js/lib/nav.ts', 'postsIndex'],
                ['AGENTS.md', '**Posts**'],
                ['README.md', '@chisel-posts'],
            ],
        ],

        'passkeys' => [
            'label' => 'Passkeys — WebAuthn sign-in and management (Fortify + @laravel/passkeys)',
            'anchor' => 'app/Http/Responses/PasskeyLoginResponse.php',
            'owned' => [
                'app/Http/Responses/PasskeyLoginResponse.php',
                'database/migrations/2024_01_01_000000_create_passkeys_table.php',
                'resources/js/components/manage-passkeys.tsx',
                'resources/js/components/passkey-item.tsx',
                'resources/js/components/passkey-register.tsx',
                'resources/js/components/passkey-verify.tsx',
            ],
            'generated' => [
                'resources/js/routes/passkey',
                'resources/js/routes/well-known',
                'resources/js/actions/Laravel/Passkeys',
            ],
            'shared' => [
                'app/Models/User.php',
                'app/Providers/FortifyServiceProvider.php',
                'app/Http/Controllers/Settings/SecurityController.php',
                'config/fortify.php',
                'routes/web/settings.php',
                'resources/js/types/auth.ts',
                'resources/js/pages/auth/login.tsx',
                'resources/js/pages/auth/confirm-password.tsx',
                'resources/js/pages/settings/security.tsx',
                'tests/Feature/Auth/AuthenticationTest.php',
                'tests/Feature/Settings/SecurityTest.php',
                'AGENTS.md',
                'README.md',
            ],
            'residue' => [
                ['app/Models/User.php', 'PasskeyAuthenticatable'],
                ['app/Providers/FortifyServiceProvider.php', 'PasskeyLoginResponse'],
                ['app/Http/Controllers/Settings/SecurityController.php', 'canManagePasskeys'],
                ['config/fortify.php', 'Features::passkeys'],
                ['routes/web/settings.php', 'well-known.passkeys'],
                ['resources/js/types/auth.ts', 'Passkey'],
                ['resources/js/pages/auth/login.tsx', 'PasskeyVerify'],
                ['resources/js/pages/auth/confirm-password.tsx', 'PasskeyVerify'],
                ['resources/js/pages/settings/security.tsx', 'ManagePasskeys'],
                ['tests/Feature/Auth/AuthenticationTest.php', 'PasskeyLoginResponse'],
                ['tests/Feature/Settings/SecurityTest.php', 'canManagePasskeys'],
                ['resources/js/pages/public/home.tsx', 'passkeys'],
                ['AGENTS.md', '**Passkeys**'],
                ['README.md', '@chisel-passkeys'],
                ['package.json', '@laravel/passkeys'],
            ],
        ],
    ],

    /*
    | Tooling that only the template needs: tests that prove the removal works on
    | a pristine checkout. A project that has run chisel is past that question,
    | and they would fail there (the modules they exercise are gone).
    */
    'template_tooling' => [
        'tests/Feature/Agent/ChiselScriptTest.php',
    ],

    /*
    | Pieces that exist only for a set of modules: removed once none of their
    | owners is left. Notes and Posts share the markdown renderer, so it goes
    | with whichever of the two leaves last — and its npm dependency with it.
    */
    'shared_pieces' => [
        [
            'owners' => ['notes', 'posts'],
            'owned' => [
                'resources/js/components/markdown-body.tsx',
            ],
            'packages' => ['react-markdown'],
            'residue' => [
                ['package.json', 'react-markdown'],
            ],
        ],
    ],

    /*
    | What the project must have replaced once no optional module is left: the
    | starter's own scaffolding (its README) describes modules that no longer
    | exist — the product's docs are the agent's job from the foundation on.
    */
    'scaffolding' => [
        'residue' => [
            ['README.md', '# Herman Laravel Starter'],
        ],
    ],
];
