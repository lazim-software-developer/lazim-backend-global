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
                You have received a payment request.
			</td>
		</tr>
		<tr>
			<td width="100%" height="25"></td>
		</tr>
		<tr>
			<td class="title">
                Cheque Details:
			</td>
		</tr>
		<tr>
			<td width="100%" height="10"></td>
		</tr>
        <tr>
            <td class="paragraph">
                ● <strong>Cheque Number: </strong> {{$rentalCheque->cheque_number}}<br>
                ● <strong>Amount: </strong> {{$rentalCheque->amount}}<br>
                ● <strong>Due Date of Cheque: </strong> {{$rentalCheque->due_date}}<br>
			</td>
		</tr>
        <tr>
			<td width="100%" height="25"></td>
		</tr>
        <tr>
            <td class="paragraph">
                Please approve this request by providing the payment link for the cheque in the admin panel at your earliest convenience.
			</td>
		</tr>
        <tr>
			<td width="100%" height="25"></td>
		</tr>
        <tr>
            <td class="paragraph">
                For any questions, feel free to reach out to us at +971 501362428 / 043954525.
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
			<td width="100%" height="5"></td>
		</tr>
		<tr>
			<td class="paragraph">
                {{$requestedBy}} {{$rentalCheque?->rentalDetail?->flat?->property_number}}<br>
                {{$rentalCheque?->rentalDetail?->flat?->building?->name}}
			</td>
		</tr>
		<tr>
			<td width="100%" height="25"></td>
		</tr>
	@include('beautymail::templates.minty.contentEnd')

@stop
