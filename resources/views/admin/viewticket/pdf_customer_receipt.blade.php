<!DOCTYPE html>
<html>
<head>
    <title>Customer Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        .header {
            text-align: center;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body style="border: 1px solid #000;">
    <div class="header">
        <h2>Customer Receipt</h2>
        <table style="border: 1px solid #fff;">
            <tr>
                <td style="width:50%; text-align:left;border: 1px solid #fff;">
                    Service Center Name :
                    <br>
                    Service Center Address :
                    <br>
                    Service Center Contact :
                </td>    
                <td style="width:50%; text-align:right;border: 1px solid #fff;">
                    <img src="{{asset($displaybarCodeImg)}}">
                </td>
            </tr>
        </table>
    </div>

    <table style="border: 1px solid #000;">
        <tr style="border: 1px solid #000;">
            <td style="border: 1px solid #000;border-right: 1px solid #FFF;border-bottom: 1px solid #FFF;text-align:left;border-left: 1px solid #FFF;vertical-align: top; width:25%">Customer Name:</td>
            <td style="border: 1px solid #000; border-bottom: 1px solid #FFF;text-align:left;vertical-align: top;width:25%">
                {{$data['ticket']->cust->firstname}} {{$data['ticket']->cust->lastname}}
            </td>
            <td style="border: 1px solid #000;border-right: 1px solid #FFF;border-bottom: 1px solid #FFF;text-align:left;vertical-align: top;width:25%">Complain No:</td>
            <td style="border: 1px solid #000; border-bottom: 1px solid #FFF;text-align:left;border-right: 1px solid #FFF;vertical-align: top;width:25%">{{$data['ticket_id']}}</td>
        </tr>
        <tr style="border: 1px solid #000;">
            <td style="border: 1px solid #000;border-right: 1px solid #FFF;border-bottom: 1px solid #FFF;text-align:left;border-left: 1px solid #FFF;vertical-align: top;">Customer Address:</td>
            <td style="border: 1px solid #000;border-bottom: 1px solid #FFF;text-align:left;vertical-align: top;">
                @php $customfields = $data['ticket']->ticket_customfield()->get(); $state = ''; $mobile = ''; @endphp
                @if($customfields->isNotEmpty())
					@foreach ($customfields as $customfield)
						@if($customfield->fieldtypes == 'textarea')
							@if($customfield->privacymode == '1')
								@php
									$extrafieldds = decrypt($customfield->values);
								@endphp
								{{$extrafieldds}}
							@else
                                {{$customfield->values}}

							@endif
						@endif
                        @if($customfield->fieldtypes == 'text')
                            @if($customfield->fieldnames == 'Mobile no.')
                                @php $mobile = $customfield->values; @endphp
                            @endif
                            @if($customfield->fieldnames == 'State')
                                @php $state = $customfield->values; @endphp
                            @endif  
                        @endif        
					@endforeach
				@endif
            </td>
            <td style="border: 1px solid #000;border-right: 1px solid #FFF;border-bottom: 1px solid #FFF;text-align:left;vertical-align: top;">
                Document Date: <br>
                VAT/TIN:<br>
                CST/TIN:<br>
                Service Tax No.:
            </td>
            <td style="border: 1px solid #000;border-bottom: 1px solid #FFF;text-align:left;border-right: 1px solid #FFF;vertical-align: top;">
                <br>
                .<br>
                .<br>
                .<br>
            </td>
        </tr>
        <tr style="border: 1px solid #000;">
            <td style="border: 1px solid #000;border-right: 1px solid #FFF;border-bottom: 1px solid #FFF;text-align:left;border-left: 1px solid #FFF;">Mobile No:</td>
            <td style="border: 1px solid #000;border-bottom: 1px solid #FFF;text-align:left;">{{$mobile}}</td>
            <td style="border: 1px solid #000;border-right: 1px solid #FFF;border-bottom: 1px solid #FFF;text-align:left;"></td>
            <td style="border: 1px solid #000;border-bottom: 1px solid #FFF;text-align:left;border-right: 1px solid #FFF;"></td>
        </tr>
        <tr style="border: 1px solid #000;">
            <td style="border: 1px solid #000;border-right: 1px solid #FFF;text-align:left;border-bottom: 1px solid #FFF;border-left: 1px solid #FFF;">E-Mail ID: </td>
            <td style="border: 1px solid #000;border-bottom: 1px solid #FFF;text-align:left;">{{ $data['ticket']->cust->email }}</td>
            <td style="border: 1px solid #000;border-right: 1px solid #FFF;border-bottom: 1px solid #FFF;text-align:left;"></td>
            <td style="border: 1px solid #000;border-bottom: 1px solid #FFF;text-align:left;border-right: 1px solid #FFF;"></td>
        </tr>
    </table>

    <table>
        <tr>
            <th style="border-left: 1px solid #FFF;">SR No.</th>
            <th>Item Description</th>
            <th>Serial/IMEI No</th>
            <th>Quantity</th>
            <th>Warranty Status</th>
            <th>Estimation Amt</th>
            <th>Warranty Start Date</th>
            <th style="border-right: 1px solid #FFF;">Warranty End Date</th>
        </tr>
        @php $k = 1; @endphp
        @foreach ($data['products'] as $product)
            <tr>
                <td style="border-left: 1px solid #FFF;">{{$k}}</td>
                <td>{{ $product->brand }} {{ $product->product_type }} {{ $product->material }} </td>
                <td></td>
                <td>{{ $product->quantity }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td style="border-right: 1px solid #FFF;"></td>
            </tr>
            @php $k++; @endphp
        @endforeach 
    </table>

    <div class="footer" style="text-align:right; padding-right:10px;">
        Total Amount: &nbsp;&nbsp;&nbsp;
    </div>

    <div class="footer" style="text-align:left;">
        Cust. Complaint:<br> {!! $data['ticket']->message !!}
    </div>

    <h4>Terms And Conditions</h4><br><br><br><br>

    <table style="padding-left:0px;">    
        <tr style="padding-left:0px;">
            <td style="border: 1px solid #FFF; width:70%;text-align:left;padding-left:0px;">Customer Signature with Name<br>Date:</td>
            <td style="border: 1px solid #FFF; text-align: center;">Signature of Service Center<br>Call Center 01</td>
        <tr>
    </table>

    <p>Note: Only For Technician Use</p>

    <table>
        <tr>
            <th style="border-left: 1px solid #FFF;">Symptom</th>
            <th>Defect</th>
            <th>Repair</th>
            <th>Consume Material</th>
            <th style="border-right: 1px solid #FFF;">Remark</th>
        </tr>
        <tr>
            <td style="border-left: 1px solid #FFF;"></td>
            <td><br><br></td>
            <td></td>
            <td></td>
            <td style="border-right: 1px solid #FFF;"></td>
        </tr>
    </table>
</body>
</html>