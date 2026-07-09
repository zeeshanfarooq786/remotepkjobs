@extends('layouts.app')

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-bold" style="color: var(--color-text-primary);">{{ $heading }}</h1>
        <p class="mt-2" style="color: var(--color-text-secondary);">
            Estimate your take-home pay after platform fees, payout processor costs, and live currency conversion — for freelancers worldwide.
        </p>
    </div>

    <script>
        window.DevRates = {
            rates: @json($rates),
            defaults: @json($defaults),
            logUrl: @json(route('calculator.calculate')),
            csrfToken: @json(csrf_token()),
        };
    </script>

    @include('calculator.partials.widget', ['footerPage' => $footerPage])
@endsection
