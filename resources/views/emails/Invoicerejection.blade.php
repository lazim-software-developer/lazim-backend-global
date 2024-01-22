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
            We regret to inform you that your invoice submission has been rejected.
			</td>
		</tr>
		<tr>
			<td width="100%" height="25"></td>
		</tr>
		<tr>
			<td class="title">
            Details:
			</td>
		</tr>

		<tr>
			<td width="100%" height="10"></td>
		</tr>

        <tr>
            <td class="paragraph">
                <strong>Invoice Number: </strong> {{$invoice->invoice_number}}
			</td>
		</tr>
        <tr>
            <td class="paragraph">
                <strong>Remarks: </strong> {{$remarks}}
                <!-- (We recommend changing this password upon your first login for security reasons.) -->
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
