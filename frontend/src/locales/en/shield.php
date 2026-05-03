<?php

return [
    'challenge' => [
        'title' => 'Verifying Connection',
        'message' => 'We are securing your session to protect against automated threats. This will only take a moment.',
        'steps' => [
            'analyze' => 'Analyze',
            'solve' => 'Solve',
            'verify' => 'Verify',
        ],
        'status' => [
            'initializing' => 'Initializing security check...',
            'verifying' => 'Verifying connection...',
            'analyzing' => 'Analyzing security metrics...',
            'finalizing' => 'Finalizing verification...',
            'verified' => 'Verified! Continuing...',
            'failed' => 'Verification failed. Retrying...',
        ],
        'retry' => 'Try Again',
    ],
];
