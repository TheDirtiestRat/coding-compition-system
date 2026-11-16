<?php

return [
    'enabled' => env('LOCAL_AI_ENABLED', false),
    'url' => env('LOCAL_AI_URL', 'http://127.0.0.1:11434'),
    'model' => env('LOCAL_AI_MODEL', 'qwen2.5-coder:3b'),
];
