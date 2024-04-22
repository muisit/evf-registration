@extends('layouts.email')

@section('preheader')
Your code is {{ $code }} for verification of e-mail address '{{ $email }}'
@endsection

@section('content')
<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
    <tr>
      <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">
        <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">
            Dear user,
        </p>
        <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">
            A request was made to verify e-mail address <span style='color: blue;'>'{{ $email }}'</span> for use with the European Veterans Fencing
            Mobile application. In order to complete verification, open your application on your mobile
            device, go to the account settings and enter the following verification code:<br/>
            <div style="font-family: sans-serif; font-size: 24px; font-weight: bold; margin: 20px; background-color: #bbb; text-align: center">
            {{ $code }}
            </div>
        </p>
        <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">
          Enjoy your day,<br/><br/>European Veterans Fencing
        </p>
      </td>
    </tr>
  </table>
@endsection
