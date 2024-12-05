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
        Thank you for reaching out to us. We are pleased to confirm that your complaint request has been successfully submitted. Please find the details of your ticket below:
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
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
        <strong>● Ticket Number: </strong> {{$ticket_number}}
    </td>
</tr>
<tr>
    <td class="paragraph">
        <strong>● Building: </strong> {{$building}}
    </td>
</tr>
<tr>
    <td class="paragraph">
        <strong>● Flat: </strong> {{$flat}}
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        We appreciate your trust in us and are committed to addressing your concerns promptly.
    </td>
</tr>
<tr>
    <td width="100%" height="15"></td>
</tr>
<tr>
    <td class="paragraph">
        If you have any further questions or require updates, please don't hesitate to contact our support team.
    </td>
</tr>
<tr>
    <td width="100%" height="15"></td>
</tr>
<tr>
    <td>
        <img src="{{url('images/logo.png')}}" alt="Lazim" style="max-width: 80px; height: 30px;">
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
