@extends('emails.layouts.email')

@section('content')
<h2 style="color: #2b6cb0; margin: 0 0 20px 0;">Payment Receipt</h2>

<p>Dear {{ $receipt->user->name }},</p>

<p>We are pleased to confirm that we have received your payment.</p>

<div class="title">Receipt Details:</div>
<p>
    <strong>Receipt Number:</strong> {{ $receipt->receipt_number ?? 'N/A' }}<br>
    <strong>Date:</strong> {{ $receipt->date ?? 'N/A' }}<br>
    @if($receipt->building?->name)
    <strong>Building:</strong> {{ $receipt->building->name }}<br>
    @endif
    @if($receipt->flat?->property_number)
    <strong>Flat Number:</strong> {{ $receipt->flat->property_number }}<br>
    @endif
    <strong>Amount Paid:</strong> {{ $receipt->amount ?? 'N/A' }}<br>
    <strong>Payment Method:</strong> {{ $receipt->payment_method ?? 'N/A' }}
</p>

<p>Thank you for your prompt payment.</p>

<p>If you have any questions or require further assistance, please feel free to contact us.</p>

<p>We appreciate your continued trust in our services.</p>

<p>Regards,</p>

@if(isset($property_manager_logo) && $property_manager_logo)
<p>
    <img src="{{ $property_manager_logo }}" alt="Property Manager" style="max-width: 150px; height: auto;">
</p>
@endif

<p>{{ $pm_oa }}</p>
@endsection
