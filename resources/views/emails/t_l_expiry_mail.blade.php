@extends('beautymail::templates.minty')

@section('content')

    @include('beautymail::templates.minty.contentStart')
        <tr>
            <td class="paragraph">
                Dear Team,
            </td>
        </tr>
        <tr>
            <td width="100%" height="20"></td>
        </tr>
        <tr>
            <td class="paragraph">
                This is a reminder that your Trade License is set to expire on {{$vendor->tl_expiry->format('Y-m-d')}}.
            </td>
        </tr>
        <tr>
            <td width="100%" height="25"></td>
        </tr>
        <tr>
            <td class="title">
                Trade License Details:
            </td>
        </tr>
        <tr>
            <td width="100%" height="10"></td>
        </tr>
        @php
            use Carbon\Carbon;
            $endDate = Carbon::parse($vendor->tl_expiry);
            $remainingDays = $endDate->diffInDays(Carbon::now());
        @endphp
        <tr>
            <td class="paragraph">
                <strong>Days Remaining: </strong> {{$remainingDays}}
            </td>
        </tr>
        <tr>
            <td class="paragraph">
                <strong>Trade License Number: </strong> {{$vendor->tl_number}}
            </td>
        </tr>
        <tr>
            <td width="100%" height="25"></td>
        </tr>
        <tr>
            <td class="title">
                Action Required:
            </td>
        </tr>
        <tr>
            <td width="100%" height="10"></td>
        </tr>
        <tr>
            <td class="paragraph">
                To ensure uninterrupted services, please update your trade license details in your profile immediately.
            </td>
        </tr>
        <tr>
            <td width="100%" height="10"></td>
        </tr>
        <tr>
            <td class="paragraph">
                For assistance, feel free to contact us at +971 50 709 8272 / 043206789.
            </td>
        </tr>
        <tr>
            <td width="100%" height="25"></td>
        </tr>
        <tr>
            <td class="paragraph">
                Thank you for your prompt attention to this matter.
            </td>
        </tr>
        <tr>
            <td width="100%" height="25"></td>
        </tr>
        <tr>
            <td class="paragraph">
                -Lazim Team
            </td>
        </tr>
        <tr>
            <td width="100%" height="25"></td>
        </tr>
    @include('beautymail::templates.minty.contentEnd')

@stop
