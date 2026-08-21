<?php

/*
|--------------------------------------------------------------------------
| Portfolio content
|--------------------------------------------------------------------------
|
| Everything the site renders lives here. Edit this file to update the
| portfolio -- no Blade changes required.
|
*/

return [

    /*
    | Login for the admin panel, used only by AdminUserSeeder on a fresh
    | install. Read through config() rather than env() on purpose: env() returns
    | null once `php artisan config:cache` has run, which is exactly what
    | production does. Prefer `php artisan admin:password` for anything after
    | the first deploy.
    */
    'admin_email' => env('ADMIN_EMAIL'),
    'admin_password' => env('ADMIN_PASSWORD'),

    'name' => 'Raj Aryan',
    'role' => 'Backend Engineer',
    'tagline' => 'I build the systems that stay up.',
    'blurb' => 'Backend engineer focused on Laravel, distributed systems and the unglamorous work that keeps APIs fast under load. I care about clean domain boundaries, queues that drain, and queries that do not surprise you at 3am.',
    'location' => 'India',
    'email' => 'rajaryanz.dev@gmail.com',

    // TODO: replace with a real number before publishing.
    'phone' => '+91 00000 00000',

    'available' => true,

    // Working arrangements open for discussion. Rendered as chips in About.
    'availability_modes' => ['Remote', 'Hybrid', 'On-site', 'Open to relocation'],
    'availability_note' => 'Available worldwide, any arrangement. Happy to relocate for the right team.',

    'socials' => [
        ['label' => 'GitHub',   'url' => 'https://github.com/'],
        ['label' => 'LinkedIn', 'url' => 'https://linkedin.com/in/'],
        ['label' => 'X',        'url' => 'https://x.com/'],
    ],

    /*
    | Hero status strip. Short, current, checkable facts -- not vanity metrics.
    | Keep values under ~40 characters so they stay on one or two lines.
    */
    'status' => [
        ['label' => 'Currently',   'value' => 'Building a webhook delivery platform'],
        ['label' => 'Based in',    'value' => 'India, IST (UTC+5:30)'],
        ['label' => 'Working in',  'value' => 'PHP, Laravel, MySQL'],
        ['label' => 'Replies in',  'value' => 'Usually within a day'],
    ],

    /*
    | Skills grouped by domain. Each group renders as a card.
    */
    'skills' => [
        [
            'group' => 'Languages & Frameworks',
            'items' => ['PHP 8.3', 'Laravel 12', 'Python', 'Go', 'Node.js', 'TypeScript'],
        ],
        [
            'group' => 'Data & Storage',
            'items' => ['MySQL', 'PostgreSQL', 'Redis', 'MongoDB', 'Elasticsearch', 'S3'],
        ],
        [
            'group' => 'Infrastructure',
            'items' => ['Docker', 'Kubernetes', 'AWS', 'Nginx', 'GitHub Actions', 'Terraform'],
        ],
        [
            'group' => 'Architecture',
            'items' => ['REST & GraphQL', 'Event-driven', 'Queues & Workers', 'Caching layers', 'DDD', 'Observability'],
        ],
    ],

    /*
    | Projects. 'metrics' render as small stat chips on the card.
    */
    'projects' => [
        [
            'title' => 'Gym Pass Marketplace',
            'kind' => 'Short-term gym booking platform',
            'year' => '2025',
            'summary' => 'A marketplace where travellers across India can book a gym for exactly how long they need - one day, three days, a week or a month. Pay by UPI and walk in with a booking reference. No full-month fees, no phone calls, no cash.',
            'stack' => ['Laravel', 'MySQL', 'UPI payments'],
            // Capabilities, not invented metrics. Only claim what is true.
            'metrics' => ['Passes from 1 day', 'UPI payments', 'Walk-in booking reference'],
            'live_url' => 'https://gym.theswarmneeds.in',
            'repo_url' => null,
        ],
    ],

    /*
    | Experience timeline, most recent first.
    */
    'experience' => [
        [
            'role' => 'Senior Backend Engineer',
            'company' => 'Confidential SaaS',
            'period' => '2024 - Present',
            'points' => [
                'Led the extraction of a billing monolith into three owned services with a zero downtime cutover.',
                'Cut median API latency from 340ms to 78ms by reworking the caching layer and killing N+1 hotspots.',
                'Introduced structured logging and tracing; mean time to diagnose dropped from hours to minutes.',
            ],
        ],
        [
            'role' => 'Backend Engineer',
            'company' => 'Product Studio',
            'period' => '2022 - 2024',
            'points' => [
                'Built and owned the queue infrastructure processing several million jobs per day.',
                'Designed the multi-tenant data model still in production across 200k accounts.',
                'Wrote the deployment pipeline that took releases from weekly to several times a day.',
            ],
        ],
        [
            'role' => 'Software Engineer',
            'company' => 'Early-stage startup',
            'period' => '2021 - 2022',
            'points' => [
                'First backend hire. Shipped the original REST API, auth and admin tooling.',
                'Moved reporting off the primary database onto a read replica with materialised views.',
            ],
        ],
    ],

    /*
    | Short "how I work" principles shown in the about section.
    */
    'principles' => [
        ['title' => 'Boring on purpose', 'body' => 'Proven tools over novel ones. The excitement belongs in the product, not the infrastructure.'],
        ['title' => 'Measure, then cut', 'body' => 'No optimisation without a trace. Profiles decide what gets rewritten, not intuition.'],
        ['title' => 'Failure is a feature', 'body' => 'Retries, backoff, idempotency and dead-letter queues designed in from the first commit.'],
        ['title' => 'Readable beats clever', 'body' => 'Code is read far more than written. The next engineer is the real user.'],
    ],
];
