@extends('beautymail::templates.minty')

@section('content')

@include('beautymail::templates.minty.contentStart')
<tr>
	<td class="paragraph">
		Dear User,
	</td>
</tr>
<tr>
	<td width="100%" height="20"></td>
</tr>
<tr>
	<td class="paragraph">
		You have a new enquiry!
	</td>
</tr>
<tr>
	<td class="paragraph">
		Company Name : {{$enquiry->company_name}}
	</td>
</tr>
<tr>
	<td class="paragraph">
		Name : {{$enquiry->name}}
	</td>
</tr>
<tr>
	<td class="paragraph">
		Email : {{$enquiry->email}}
	</td>
</tr>
<tr>
	<td class="paragraph">
		Phone : {{$enquiry->phone}}
	</td>
</tr><tr>
	<td class="paragraph">
		Message : {{$enquiry->message}}
	</td>
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