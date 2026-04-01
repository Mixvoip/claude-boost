<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Claude Boost Configuration
    |--------------------------------------------------------------------------
    |
    | Minimal config for the Laravel wrapper. The real project configuration
    | lives in .claude/claude-boost.json, created interactively by Claude
    | when it runs learn.md.
    |
    | This file only affects the artisan commands (claude:init, claude:doctor,
    | claude:update). Everything else is handled by learn.md + Claude.
    |
    */

    // Package name (used by update command to find version in composer.lock)
    'package_name' => 'codewithali/claude-boost',

];
