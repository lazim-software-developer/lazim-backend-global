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
		We are pleased to inform you that your {{ $type }} request has been successfully submitted. Below are your ticket details:
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
		<strong>Ticket Number: </strong> {{$ticket_number}}
	</td>
</tr>
<tr>
	<td class="paragraph">
		<strong>Building ID: </strong> {{$building_id}}
	</td>
</tr>
<tr>
	<td class="paragraph">
		<strong>Flat ID: </strong> {{$flat_id}}
	</td>
</tr>
<tr>
	<td class="paragraph">
		<strong>Type: </strong> {{$type}}
	</td>
</tr>
<tr>
	<td class="paragraph">
		<strong>Moving Date: </strong> {{$moving_date}}
	</td>
</tr>
<tr>
	<td class="paragraph">
		<strong>Moving Time: </strong> {{$moving_time}}
	</td>
</tr>
<tr>
	<td width="100%" height="25"></td>
</tr>
<tr>
	<td class="paragraph">
		Thank you for choosing Lazim. We are confident that you will find great value in our platform, and we look forward to serving you.
	</td>
</tr>
<tr>
	<td width="100%" height="25"></td>
</tr>
<tr>
	<td class="paragraph">
		Warm regards,
	</td>
</tr>
<tr>
	<td width="100%" height="5"></td>
</tr>
<tr>
	<td class="paragraph">
		The Lazim Team
	</td>
</tr>
<tr>
	<td width="100%" height="25"></td>
</tr>
@include('beautymail::templates.minty.contentEnd')

@stop
