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
            There is a resident moving out.
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
                <strong>Email: </strong> {{$moveout->email}}
			</td>
		</tr>
        <tr>
            <td class="paragraph">
                <strong>Name: </strong> {{$moveout->name}}
			</td>
		</tr>
        <tr>
            <td class="paragraph">
                <strong>Phone: </strong> {{$moveout->phone}}
			</td>
		</tr>
        <tr>
            <td class="paragraph">
                <strong>Moving Time: </strong> {{$moveout->moving_date}} .' '.{{$moveout->moving_time}}
			</td>
		</tr>
        <tr>
            <td class="paragraph">
                <strong>Unit: </strong> {{$moveout->flat->property_number}}
			</td>
		</tr>
        <tr>
            <td class="paragraph">
                <strong>Building: </strong> {{$moveout->building->name}}
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
