@extends('emails.layouts.default')

@section('content')

<div style="padding: 50px 30px 0; border-top: 1px solid rgba(0,0,0,0.05);">

	<h1 style="margin-top: 0px;">AEPS SDK Credential Generated</h1>

	<div style="color: #636363; font-size: 14px; margin-bottom: 30px">
		Hi {{$data->name}}, New AEPS SDK credential has been generated.
	</div>

	<div style="background-color: #F4F4F4; margin: 20px 0px 40px;">
		<div style="padding: 20px; text-transform: uppercase; color: #8D929D; font-size: 11px; font-weight: bold; letter-spacing: 1px; text-align: center;">
			SDK Credential
		</div>
		<table style="border-collapse: collapse; width: 100%;">
			<tr>
				<td colspan="2" style="padding: 20px 20px 20px 20px; color: #111; border: 1px solid #e7e7e7; border-left: none; border-right: none;">
					<div style="text-transform: uppercase; letter-spacing: 1px; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
						APP ID :
					</div>
					<div style="font-weight: bold; color: #B8B8B8;">
						{{$data->appId}}
					</div>
				</td>
			</tr>
			<tr>
				<td style="padding: 20px 20px 20px 20px; color: #111; width: 50%; ">
					<div style="text-transform: uppercase; letter-spacing: 1px; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
						APP SECRET :
					</div>
					<div style="font-weight: bold; color: #B8B8B8;">
						{{$data->appSecret}}
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>

@endsection