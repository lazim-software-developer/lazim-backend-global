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
            We regret to inform you that your account application with Lazim has been declined. We appreciate your interest, but after careful review, we have decided not to proceed with your account at this time.
			</td>
		</tr>
		<tr>
			<td width="100%" height="25"></td>
		</tr>

		<tr>
			<td class="paragraph"><strong>Reason :</strong> {{$remarks}}</td>
		</tr>
		<tr>
			<td width="100%" height="25"></td>
		</tr>
        <tr>
            <td class="paragraph">
                <strong>Email: </strong> {{$user->email}}
			</td>
		</tr>
		<tr>
			<td width="100%" height="25"></td>
		</tr>
        <tr>
            <td class="paragraph">
			If you have any questions or would like further clarification, please feel free to reach out to our support team.
                <!-- (We recommend changing this password upon your first login for security reasons.) -->
			</td>
		</tr>
        <tr>
			<td width="100%" height="25"></td>
		</tr>

        <tr>
            <td class="paragraph">
            Thank you for considering Lazim.
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
			<td class="paragraph">
           Lazim team
			</td>
		</tr>

		<tr>
			<td width="100%" height="25"></td>
		</tr>
	@include('beautymail::templates.minty.contentEnd')

@stop
