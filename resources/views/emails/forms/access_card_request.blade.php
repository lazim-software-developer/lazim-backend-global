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
        We are pleased to confirm that your access card request has been successfully submitted.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        Below are the details of your ticket:
    </td>
</tr>
<tr>
    <td width="100%" height="10"></td>
</tr>
<tr>
    <td class="paragraph">
        <strong>Ticket Number: </strong> {{$ticket_number}}
    </td>
</tr>
<tr>
    <td class="paragraph">
        <strong>Building: </strong> {{$building}}
    </td>
</tr>
<tr>
    <td class="paragraph">
        <strong>Flat: </strong> {{$flat}}
    </td>
</tr>
<tr>
    <td class="paragraph">
        <strong>Request Type: </strong> Access Card
    </td>
</tr>
<tr>
    <td class="paragraph">
        <strong>Card Type: </strong> {{$card_type}}
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        Thank you for choosing Lazim. We are committed to ensuring a seamless process and look forward to assisting you further.
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
        <img src="{{url('images/logo.png')}}" alt="Lazim" style="max-width: 100px; height: 50px;">
    </td>
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
