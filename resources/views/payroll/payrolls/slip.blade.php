<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $payroll->employee->name }} - {{ $payroll->period }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .slip-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border: 1px solid #ddd;
        }
        
        /* Header */
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .slip-title {
            font-size: 16px;
        }
        
        /* Info Table */
        .info-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        
        .info-table td {
            padding: 5px;
            font-size: 14px;
        }
        
        .info-table .label {
            width: 150px;
            font-weight: bold;
        }
        
        /* Section Title */
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 30px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #000;
        }
        
        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .data-table th {
            background: #f5f5f5;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 13px;
            border: 1px solid #000;
        }
        
        .data-table td {
            padding: 8px;
            border: 1px solid #000;
            font-size: 13px;
        }
        
        .data-table .text-right {
            text-align: right;
        }
        
        .data-table .text-center {
            text-align: center;
        }
        
        .data-table .bold {
            font-weight: bold;
        }
        
        .data-table .total-row {
            background: #e0e0e0;
            font-weight: bold;
        }
        
        /* Signature */
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
        }
        
        .signature-label {
            margin-bottom: 60px;
            font-size: 13px;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            padding-top: 5px;
            font-size: 13px;
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .slip-container {
                border: none;
                padding: 20px;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        /* Buttons */
        .action-buttons {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-back {
            background: #666;
            color: white;
        }
        
        .btn-print {
            background: #0066cc;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="slip-container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">{{ config('app.name', 'PERUSAHAAN') }}</div>
            <div class="slip-title">SLIP GAJI KARYAWAN</div>
        </div>
        
        <!-- Employee Info -->
        <table class="info-table">
            <tr>
                <td class="label">Nama</td>
                <td>: {{ $payroll->employee->name }}</td>
                <td class="label">Periode</td>
                <td>: {{ $payroll->period }}</td>
            </tr>
            <tr>
                <td class="label">NIK</td>
                <td>: {{ $payroll->employee->employee_id }}</td>
                <td class="label">Tanggal Cetak</td>
                <td>: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Jabatan</td>
                <td>: {{ $payroll->employee->position ?? '-' }}</td>
                <td class="label">Departemen</td>
                <td>: {{ $payroll->employee->department ?? '-' }}</td>
            </tr>
        </table>
        
        <!-- Attendance -->
        <div class="section-title">DATA KEHADIRAN</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center">Total Hari Kerja</th>
                    <th class="text-center">Hadir</th>
                    <th class="text-center">Terlambat</th>
                    <th class="text-center">Tidak Hadir</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center bold">{{ $payroll->total_days }}</td>
                    <td class="text-center bold">{{ $payroll->present_days }}</td>
                    <td class="text-center bold">{{ $payroll->late_days }}</td>
                    <td class="text-center bold">{{ $payroll->absent_days }}</td>
                </tr>
            </tbody>
        </table>
        
        <!-- Salary Details -->
        <div class="section-title">RINCIAN GAJI</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50%;">KOMPONEN</th>
                    <th style="width: 25%;">KETERANGAN</th>
                    <th style="width: 25%;" class="text-right">JUMLAH (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <!-- Gaji Pokok -->
                <tr>
                    <td>Gaji Pokok</td>
                    <td>-</td>
                    <td class="text-right">{{ number_format($payroll->basic_salary, 0, ',', '.') }}</td>
                </tr>
                
                <!-- Tunjangan -->
                @php
                    $allowances = $payroll->details->where('type', 'allowance');
                @endphp
                
                @if($allowances->count() > 0)
                    @foreach($allowances as $detail)
                    <tr>
                        <td>{{ $detail->name }}</td>
                        <td>
                            @if($detail->calculation_type === 'fixed')
                                Tetap
                            @else
                                {{ $detail->amount }}%
                            @endif
                            @if($detail->quantity > 1)
                                x {{ $detail->quantity }}
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($detail->total, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                @endif
                
                <!-- Gross Salary -->
                <tr class="total-row">
                    <td colspan="2">GAJI KOTOR (Gaji Pokok + Tunjangan)</td>
                    <td class="text-right">{{ number_format($payroll->gross_salary, 0, ',', '.') }}</td>
                </tr>
                
                <!-- Potongan -->
                @php
                    $deductions = $payroll->details->where('type', 'deduction');
                @endphp
                
                @if($deductions->count() > 0)
                    @foreach($deductions as $detail)
                    <tr>
                        <td>{{ $detail->name }}</td>
                        <td>
                            @if($detail->calculation_type === 'fixed')
                                Tetap
                            @elseif($detail->calculation_type === 'percentage')
                                {{ $detail->amount }}%
                            @else
                                {{ $detail->calculation_type }}
                            @endif
                            @if($detail->quantity > 1)
                                x {{ $detail->quantity }}
                            @endif
                        </td>
                        <td class="text-right">({{ number_format($detail->total, 0, ',', '.') }})</td>
                    </tr>
                    @endforeach
                    
                    <tr class="total-row">
                        <td colspan="2">TOTAL POTONGAN</td>
                        <td class="text-right">({{ number_format($payroll->total_deductions, 0, ',', '.') }})</td>
                    </tr>
                @endif
                
                <!-- Net Salary -->
                <tr class="total-row">
                    <td colspan="2"><strong>GAJI BERSIH (TAKE HOME PAY)</strong></td>
                    <td class="text-right"><strong>{{ number_format($payroll->net_salary, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
        
        @if($payroll->notes)
        <p style="margin: 20px 0; font-size: 13px;"><strong>Catatan:</strong> {{ $payroll->notes }}</p>
        @endif
        
        <!-- Signature -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-label">Karyawan,</div>
                <div class="signature-line">{{ $payroll->employee->name }}</div>
            </div>
            <div class="signature-box">
                <div class="signature-label">HRD / Finance,</div>
                <div class="signature-line">(.......................)</div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="action-buttons no-print">
        <a href="{{ route('payrolls.show', $payroll) }}" class="btn btn-back">Kembali</a>
        <button onclick="window.print()" class="btn btn-print">Print / Save PDF</button>
    </div>
    
    <script>
        // Auto print saat halaman dibuka (optional, bisa diaktifkan jika ingin langsung print)
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
