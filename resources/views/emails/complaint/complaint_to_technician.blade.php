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
        A new complaint has been assigned to you.
    </td>
</tr>
<tr>
    <td class="paragraph">
        Please find the details below:
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="title">
        Complaint Details:
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
        ● <strong>Complaint Details: </strong> {{$description}}
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        Kindly address this complaint at your earliest convenience. Your prompt attention and resolution are greatly appreciated.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        Best regards,
    </td>
</tr>
<tr>
    <td width="100%" height="5"></td>
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
@include('beautymail::templates.minty.contentEnd')

@stop
