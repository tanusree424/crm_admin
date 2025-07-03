<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Challan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            width: 80%;
            margin: 0 auto;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .section {
            border: 1px solid black;
            padding: 10px;
            margin-bottom: 20px;
        }
        .section h3 {
            text-align: center;
        }
        .flex {
            display: flex;
            justify-content: space-between;
        }
        .barcode {
            text-align: center;
            margin: 10px 0;
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Delivery Challan</h1>
            <p>Original For Consignee/duplicate For Transporter/triplicate For Consignor (For Goods)</p>
        </div>

        <div class="section">
            <div class="barcode">
                <p>490004345</p>
            </div>
            <div class="flex">
                <div>
                    <p>GSTIN of Consignor:</p>
                    <p>Name of Consignor: Honeywell Replacement center -HO</p>
                    <p>Address of Consignor: Mumbai 400067</p>
                    <p>Serial No. of Ref. No: 490004345</p>
                    <p>Date of Ref. No: 30/08/2024</p>
                    <p>State: Maharashtra</p>
                    <p>StateCode: MH</p>
                </div>
                <div>
                    <p>Transportation Mode: .</p>
                    <p>Veh. No: .</p>
                    <p>Date & Time of Supply: .</p>
                    <p>Place of Supply: Telangana</p>
                    <p>Challan No: 490004345</p>
                    <p>Challan Date: 30/08/2024</p>
                    <p>Courier Name:</p>
                    <p>Docket No:</p>
                    <p>Weight:</p>
                    <p>Total Qty: 1</p>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="flex">
                <div>
                    <h3>Bill To:</h3>
                    <p>Name of Consignee: Honeywell Replacement center -HO</p>
                    <p>Address of Consignee: MUMBAI</p>
                    <p>State: Maharashtra</p>
                    <p>StateCode: MH</p>
                </div>
                <div>
                    <h3>Ship To:</h3>
                    <p>Ticket No: 2408130011</p>
                    <p>Name of Consignee: Shweta Computers & Peripherals</p>
                    <p>Address of Consignee: Shop No 1 to 4, Cellar, CTC Parklane, Secunderabad-500003 500003 Mob No:-9346617772</p>
                    <p>State: Telangana</p>
                    <p>StateCode:</p>
                </div>
            </div>
        </div>

        <div class="section table-container">
            <table>
                <thead>
                    <tr>
                        <th>Sr. No.</th>
                        <th>Description of goods/service</th>
                        <th>HSN code of Goods/service</th>
                        <th>Qty.</th>
                        <th>Unit/Unique Quantity Code</th>
                        <th>Rate (per item)</th>
                        <th>Total</th>
                        <th>Discount</th>
                        <th>Taxable value</th>
                        <th>SGST</th>
                        <th>CGST</th>
                        <th>IGST</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>IM008957-N - HC000023/CAB/1.2M/BLK/B-USB to Micro USB Cable 1.2Mtr - (Braided) - Black</td>
                        <td></td>
                        <td>1</td>
                        <td>NOS</td>
                        <td>380.59</td>
                        <td>380.59</td>
                        <td></td>
                        <td></td>
                        <td>Rate</td>
                        <td>Amt.</td>
                        <td>Rate</td>
                        <td>Amt.</td>
                        <td>Rate</td>
                        <td>Amt.</td>
                    </tr>
                </tbody>
            </table>
            <p>Total 380.59</p>
            <p>Amount Of Tax 0</p>
            <p>Freight Charges 0</p>
            <p>Loading and Packing Charges 0</p>
            <p>Insurance charges 0</p>
            <p>Other Charges 0</p>
            <p>Invoice Total 381</p>
        </div>

        <div class="section">
            <p>Invoice Value (In words): three hundred eighty one (Round Off)</p>
        </div>

        <div class="section">
            <p>Terms And Conditions</p>
            <p>Certified that the particulars given above are true & Correct</p>
            <p>Signature Supplier/Authorised Signatory</p>
        </div>

        <div class="footer">
            <p>Not for Sale, Only for Replacement under Warranty</p>
        </div>
    </div>
</body>
</html>
