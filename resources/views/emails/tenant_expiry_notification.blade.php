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
            Resident contract is expiring.
			</td>
		</tr>
		<tr>
			<td width="100%" height="25"></td>
		</tr>
		<tr>
			<td class="title">
            Resident Details:
			</td>
		</tr>

		<tr>
			<td width="100%" height="10"></td>
		</tr>
		<tr>
            <td class="paragraph">
                <strong>Building: </strong> {{$tenant->building->name}}
			</td>
		</tr>
		<tr>
            <td class="paragraph">
                <strong>Unit: </strong> {{$tenant->flat->property_number}}
			</td>
		</tr>
		<tr>
            <td class="paragraph">
                <strong>Name: </strong> {{$tenant->user->first_name}}
			</td>
		</tr>
        <tr>
            <td class="paragraph">
                <strong>Email: </strong> {{$tenant->user->email}}
			</td>
		</tr>
        <tr>
            <td class="paragraph">
                <strong>End Date: </strong> {{$tenant->end_date->toDateString()}}
			</td>
		</tr>
		<tr>
			<td width="100%" height="25"></td>
		</tr>
		<tr>
			<td class="paragraph">
            Please take necessary actions.
			</td>
		</tr>
        <tr>
			<td width="100%" height="25"></td>
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
