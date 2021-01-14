@extends('portal.ninja2020.layout.app')
@section('meta_title', ctrans('texts.entity_number_placeholder', ['entity' => ctrans('texts.quote'), 'entity_number' => $quote->number]))

@push('head')
    <meta name="pdf-url" content="{{ asset($quote->pdf_file_path()) }}">
    <script src="{{ asset('js/vendor/pdf.js/pdf.min.js') }}"></script>
@endpush

@section('body')

    @if(!$quote->isApproved() && $client->getSetting('custom_message_unpaid_invoice'))
        @component('portal.ninja2020.components.message')
            {{ $client->getSetting('custom_message_unpaid_invoice') }}
        @endcomponent
    @endif

    @if(!$quote->isApproved())
        <form action="{{ route('client.quotes.bulk') }}" method="post">
            @csrf
            <input type="hidden" name="action" value="approve">
            <input type="hidden" name="quotes[]" value="{{ $quote->hashed_id }}">
            <div class="bg-white shadow sm:rounded-lg mb-4" translate>
                <div class="px-4 py-5 sm:p-6">
                    <div class="sm:flex sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900" translate>
                                {{ ctrans('texts.waiting_for_approval') }}
                            </h3>
                            <div class="mt-2 max-w-xl text-sm leading-5 text-gray-500">
                                <p translate>
                                    {{ ctrans('texts.quote_still_not_approved') }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-0 sm:ml-6 sm:flex-shrink-0 sm:flex sm:items-center">
                            <div class="inline-flex rounded-md shadow-sm">
                                <input type="hidden" name="action" value="payment">
                                <button class="button button-primary bg-primary">{{ ctrans('texts.approve') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif

    <div class="flex items-center justify-between">
        <section class="flex items-center">
            <div class="items-center" style="display: none" id="pagination-button-container">
                <button class="input-label focus:outline-none hover:text-blue-600 transition ease-in-out duration-300" id="previous-page-button" title="Previous page">
                    <svg class="w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button class="input-label focus:outline-none hover:text-blue-600 transition ease-in-out duration-300" id="next-page-button" title="Next page">
                    <svg class="w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
            <span class="text-sm text-gray-700 ml-2">{{ ctrans('texts.page') }}: 
                <span id="current-page-container"></span>
                <span>{{ strtolower(ctrans('texts.of')) }}</span>
                <span id="total-page-container"></span>
            </span>
        </section>
        <section class="flex items-center space-x-1">
            <div class="flex items-center mr-4 space-x-1">
                <span class="text-gray-600 mr-2" id="zoom-level">175%</span>
                <a href="#" id="zoom-in">
                    <svg class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 cursor-pointer" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                </a>
                <a href="#" id="zoom-out">
                    <svg class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 cursor-pointer" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                </a>
            </div>
            <div x-data="{ open: false }" @keydown.escape="open = false" @click.away="open = false" class="relative inline-block text-left">
                <div>
                    <button @click="open = !open" class="flex items-center text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                    </svg>
                    </button>
                </div>
                <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg">
                    <div class="rounded-md bg-white shadow-xs">
                    <div class="py-1">
                        <a target="_blank" href="?mode=fullscreen" class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:bg-gray-100 focus:text-gray-900">{{ ctrans('texts.open_in_new_tab') }}</a>
                    </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="flex justify-center">
        <canvas id="pdf-placeholder" class="shadow rounded-lg bg-white mt-4 p-4"></canvas>
    </div>
@endsection

@section('footer')
    <script src="{{ asset('js/clients/shared/pdf.js') }}"></script>
@endsection