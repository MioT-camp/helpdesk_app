<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen antialiased"
    style="background-image: url('/main_background.jpg'); background-size: 100% 100%; background-position: center; background-repeat: no-repeat; background-attachment: fixed;">
    <!-- 透過オーバーレイ -->
    <div class="absolute inset-0" style="background-color: rgba(248, 250, 252, 0.7); z-index: 1;"></div>
    <div class="relative min-h-screen flex items-center justify-center" style="z-index: 2;">
        <div class="flex w-full max-w-sm flex-col gap-2"
            style="background-color: rgba(255, 255, 255, 0.9); padding: 32px; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                <span class="flex h-9 w-9 mb-1 items-center justify-center rounded-md">
                    <x-app-logo-icon class="size-9 fill-current" style="color: #2d2d2d;" />
                </span>
                <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
            </a>
            <div class="flex flex-col gap-6" style="color: #2d2d2d;">
                {{ $slot }}
            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>
