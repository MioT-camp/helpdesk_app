@props([
    'text' => '',
    'label' => 'コピー',
    'successLabel' => 'コピー完了!',
    'icon' => 'copy',
    'size' => 'sm',
    'variant' => 'default',
    'title' => 'テキストをコピー',
])

@php
    $sizeClasses = [
        'xs' => 'px-2 py-1 text-xs',
        'sm' => 'px-3 py-2 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-4 py-3 text-base',
    ];

    $variantClasses = [
        'default' =>
            'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300',
        'primary' =>
            'bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-300',
        'success' =>
            'bg-green-100 hover:bg-green-200 dark:bg-green-900 dark:hover:bg-green-800 text-green-700 dark:text-green-300',
        'warning' =>
            'bg-yellow-100 hover:bg-yellow-200 dark:bg-yellow-900 dark:hover:bg-yellow-800 text-yellow-700 dark:text-yellow-300',
        'danger' => 'bg-red-100 hover:bg-red-200 dark:bg-red-900 dark:hover:bg-red-800 text-red-700 dark:text-red-300',
    ];

    $iconClasses = [
        'copy' =>
            'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z',
        'link' =>
            'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1',
        'check' => 'M5 13l4 4L19 7',
    ];
@endphp

<button type="button" x-data="{ copied: false }"
    @click="
        const textToCopy = @js($text);
        console.log('Copying text:', textToCopy);
        
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(textToCopy).then(() => {
                console.log('Clipboard API success');
                copied = true;
                setTimeout(() => copied = false, 2000);
            }).catch((err) => {
                console.error('Clipboard API failed:', err);
                fallbackCopy(textToCopy);
            });
        } else {
            console.log('Using fallback copy');
            fallbackCopy(textToCopy);
        }
        
        function fallbackCopy(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                console.log('Fallback copy result:', successful);
                if (successful) {
                    copied = true;
                    setTimeout(() => copied = false, 2000);
                }
            } catch (err) {
                console.error('Fallback copy failed:', err);
            }
            
            document.body.removeChild(textArea);
        }
    "
    class="inline-flex items-center gap-1 rounded-md font-medium transition-colors {{ $sizeClasses[$size] }} {{ $variantClasses[$variant] }}"
    title="{{ $title }}" {{ $attributes }}>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path x-show="!copied" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="{{ $iconClasses[$icon] }}"></path>
        <path x-show="copied" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="{{ $iconClasses['check'] }}"></path>
    </svg>
    <span x-show="!copied">{{ $label }}</span>
    <span x-show="copied" x-cloak class="text-green-600 dark:text-green-400">{{ $successLabel }}</span>
</button>
