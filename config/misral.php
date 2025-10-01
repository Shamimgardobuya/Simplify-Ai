<?php


/*|--------------------------------------------------------------------------

| Misral API Configuration

|--------------------------------------------------------------------------
| Here you may configure the settings for connecting to the Misral API.
| These settings include the base URL, API key, and default agent ID.
| Make sure to set the appropriate environment variables in your .env file.
|--------------------------------------------------------------------------*/


return [
    'base_url' => env('MISRAL_BASE_URL', 'https://misral.example.com'),
    'api_key' => env('MISRAL_API_KEY', 'your_api_key_here'),
    'default_agent_id' => env('MISRAL_AGENT_ID', 'your_agent_id_here'),
];