@extends('beautymail::templates.minty')

@section('content')

@include('beautymail::templates.minty.contentStart')
<tr>
	<td class="paragraph">
		Dear {{$name}},
	</td>
</tr>
<tr>
	<td width="100%" height="20"></td>
</tr>
<tr>
	<td class="paragraph">
		Fitout request has been submitted, you will find a link below.
	</td>
</tr>
<tr>
	<td width="100%" height="20"></td>
</tr>
<tr>
	<td class="paragraph">
		<a href= "{{ env('CONTRACTOR_REQUEST_PAGE') . '/' . $id }}">Link</a>
	</td>
</tr>
<tr>
	<td class="paragraph">
		Please fill the form and upload respective documents and submit the form.
	</td>
</tr>
<tr>
	<td width="100%" height="20"></td>
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
		<strong>Building: </strong> {{$building}}
	</td>
</tr>
<tr>
	<td class="paragraph">
		<strong>Flat: </strong> {{$flat}}
	</td>
</tr>
<tr>
	<td width="100%" height="25"></td>
</tr>
<tr>
	<td class="paragraph">
		Thank you for choosing Lazim. We're confident that you'll find great value in our platform, and we look forward to serving you.
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
		Lazim team
	</td>
</tr>

<tr>
	<td width="100%" height="25"></td>
</tr>
@include('beautymail::templates.minty.contentEnd')

@stop
