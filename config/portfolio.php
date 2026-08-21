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
    | Login for the admin panel. Set ADMIN_EMAIL and ADMIN_PASSWORD in .env
    | before running the seeder; the password is never stored in this file.
    */
    'admin_email' => env('ADMIN_EMAIL'),

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
        ['label' => 'Working in',  'value' => 'Laravel, Postgres, Go'],
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
            'title' => 'Ledger Core',
            'kind' => 'Financial ledger service',
            'year' => '2025',
            'summary' => 'Double-entry ledger handling multi-currency balances with strict consistency. Idempotent writes, an append-only journal, and a reconciliation job that has never drifted.',
            'stack' => ['Laravel', 'PostgreSQL', 'Redis', 'Kubernetes'],
            'metrics' => ['12M+ entries', 'p99 40ms', 'Zero drift'],
            'live_url' => null,   // e.g. 'https://demo.example.com'
            'repo_url' => null,   // e.g. 'https://github.com/you/repo'
        ],
        [
            'title' => 'Fanout',
            'kind' => 'Webhook delivery platform',
            'year' => '2024',
            'summary' => 'At-least-once webhook delivery with exponential backoff, per-tenant rate limiting and a dead-letter queue you can actually replay from a dashboard.',
            'stack' => ['Go', 'Redis Streams', 'Postgres', 'Docker'],
            'metrics' => ['8M events/day', '99.99% delivery', '6 retry tiers'],
            'live_url' => null,   // e.g. 'https://demo.example.com'
            'repo_url' => null,   // e.g. 'https://github.com/you/repo'
        ],
        [
            'title' => 'Querysmith',
            'kind' => 'Query performance toolkit',
            'year' => '2024',
            'summary' => 'Laravel package that traces N+1 queries, flags missing indexes from real traffic, and ships a slow-query budget you can fail CI on.',
            'stack' => ['PHP', 'Laravel', 'MySQL', 'OpenTelemetry'],
            'metrics' => ['70% fewer queries', 'CI gate', 'Open source'],
            'live_url' => null,   // e.g. 'https://demo.example.com'
            'repo_url' => null,   // e.g. 'https://github.com/you/repo'
        ],
        [
            'title' => 'Gatekeep',
            'kind' => 'Auth & permissions service',
            'year' => '2023',
            'summary' => 'Centralised OAuth2 and fine-grained permission service for a multi-tenant SaaS. Token introspection cached at the edge, policies evaluated in under a millisecond.',
            'stack' => ['Laravel', 'Redis', 'JWT', 'Nginx'],
            'metrics' => ['200k users', '0.8ms policy eval', 'SOC2 ready'],
            'live_url' => null,   // e.g. 'https://demo.example.com'
            'repo_url' => null,   // e.g. 'https://github.com/you/repo'
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
