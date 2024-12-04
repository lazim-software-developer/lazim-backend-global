@extends('beautymail::templates.minty')

@section('content')

@include('beautymail::templates.minty.contentStart')
<tr>
    <td class="paragraph">
        Dear {{$user->first_name}},
    </td>
</tr>
<tr>
    <td width="100%" height="20"></td>
</tr>
<tr>
    <td class="paragraph">
        We are pleased to confirm that your {{ $type }} request has been successfully submitted.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        Below are the details of your request:
    </td>
</tr>
<tr>
    <td class="title">
        Ticket Details:
    </td>
</tr>
<tr>
    <td width="100%" height="10"></td>
</tr>
<tr>
    <td class="paragraph">
        ● <strong>Ticket Number: </strong> {{$ticket_number}}
    </td>
</tr>
<tr>
    <td class="paragraph">
        ● <strong>Building: </strong> {{$building}}
    </td>
</tr>
<tr>
    <td class="paragraph">
        ● <strong>Flat: </strong> {{$flat}}
    </td>
</tr>
<tr>
    <td class="paragraph">
        ● <strong>Request Type: </strong> {{$type}}
    </td>
</tr>
<tr>
    <td class="paragraph">
        ● <strong>Moving Date: </strong> {{$moving_date}}
    </td>
</tr>
<tr>
    <td class="paragraph">
        ● <strong>Moving Time: </strong> {{$moving_time}}
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        @if ($type == 'move-in')
            We are excited to welcome you and are committed to ensuring a seamless {{ $type }} experience. If you have any questions or require assistance, feel free to contact us.
        @else
            Thank you for choosing us. We appreciate your cooperation and are here to ensure a smooth process for your {{ $type }}.
        @endif
    </td>
</tr>
<tr>
    <td class="paragraph">
        If you need any further assistance, please feel free to reach out to us.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        Regards,
    </td>
</tr>
<tr>
    <td width="100%" height="15"></td>
</tr>
<tr>
    <td>
        <img src="{{url('images/logo.png')}}" alt="Company Logo" style="max-width: 150px; height: auto;">
    </td>
</tr>
<tr>
    <td width="100%" height="10"></td>
</tr>
<tr>
    <td class="paragraph">
        {{$property_manager_name}}
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
@include('beautymail::templates.minty.contentEnd')

@stop
