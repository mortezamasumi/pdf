@php
    $height = request()->back
        ? 'width: 100%; height: calc(100vh - 260px);'
        : 'width: 100%; height: calc(100vh - 200px);';

    $back = request()->back;
@endphp

<x-filament-panels::page>

    <x-filament::button color="gray" tag="a" download href="{{ request()->path }}" class="md:hidden block"
        style="margin-left: auto; margin-right: auto; width: 240px;">
        @lang('pdf::pdf.download')
    </x-filament::button>

    @if ($back)
        <x-filament::button color="gray" tag="a" :href="$back"
            style="margin-left: auto; margin-right: auto; width: 240px;">
            @lang('pdf::pdf.return')
        </x-filament::button>
    @endif

    <embed src="{{ request()->path }}" class="md:block hidden w-full" style="{{ $height }}"></embed>

</x-filament-panels::page>
