@extends('layouts.email')

@section('preheader')
General Notification
@endsection

@section('content')
<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
    <tr>
      <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">
        <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">
            Dear administration,
        </p>
        <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">
            This is the daily EVF website report.
        </p>
        <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">
            {!! $content !!}
        </p>
        <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">
          Enjoy your day,<br/><br/>EVF Website.
        </p>
      </td>
    </tr>
  </table>
@endsection
