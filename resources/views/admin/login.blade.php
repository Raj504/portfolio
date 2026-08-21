<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#05060a">
    <title>Sign in — Admin</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-svh items-center justify-center bg-void px-6 text-bright antialiased">

<div class="grid-bg pointer-events-none fixed inset-0" aria-hidden="true"></div>

<div class="panel panel-sheen relative w-full max-w-sm p-8">

    <h1 class="font-mono text-xs tracking-[0.3em] text-cyan-glow uppercase">Admin access</h1>
    <p class="mt-3 text-sm text-muted">Sign in to manage the portfolio.</p>

    <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
        @csrf

        <x-admin.input name="email" label="Email" type="email" :value="old('email')" required />
        <x-admin.input name="password" label="Password" type="password" required />

        <label class="flex cursor-pointer items-center gap-3">
            <input type="checkbox" name="remember" value="1"
                   class="h-4 w-4 rounded border-edge bg-void accent-cyan-glow">
            <span class="text-sm text-muted">Stay signed in</span>
        </label>

        <button type="submit" class="btn-glow w-full justify-center">
            Sign in
            <span aria-hidden="true">&rarr;</span>
        </button>
    </form>

    <a href="{{ route('home') }}"
       class="mt-6 block text-center font-mono text-[10px] tracking-widest text-faint uppercase hover:text-cyan-glow">
        Back to site
    </a>
</div>

</body>
</html>
