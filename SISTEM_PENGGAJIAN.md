# SISTEM PENGGAJIAN - DOKUMENTASI LENGKAP

## 📋 Fitur yang Sudah Dibuat

### ✅ Database & Migration
1. **salary_components** - Komponen Gaji (Gaji Pokok, Tunjangan Tetap)
2. **deductions** - Potongan Gaji (Terlambat, Alpha, BPJS)
3. **allowances** - Tunjangan/Bonus (Lembur, Bonus Kehadiran)
4. **payrolls** - Data Penggajian per Karyawan per Periode
5. **payroll_details** - Detail Perhitungan Gaji

### ✅ Models
- `SalaryComponent` - Model komponen gaji
- `Deduction` - Model potongan
- `Allowance` - Model tunjangan
- `Payroll` - Model payroll dengan relasi ke Employee
- `PayrollDetail` - Model detail payroll

### ✅ Controllers
- `SalaryComponentController` - CRUD komponen gaji
- `DeductionController` - CRUD potongan (perlu diimplementasi)
- `AllowanceController` - CRUD tunjangan (perlu diimplementasi)
- `PayrollController` - Kelola payroll & generate gaji (perlu diimplementasi)

### ✅ Routes
Semua route sudah terdaftar di `routes/web.php`:
- `/salary-components` - Master komponen gaji
- `/deductions` - Master potongan  
- `/allowances` - Master tunjangan/bonus
- `/payrolls` - Penggajian
- `/payrolls/generate` - Generate gaji otomatis
- `/payrolls/{payroll}/approve` - Approve gaji
- `/payrolls/{payroll}/pay` - Tandai sudah dibayar

### ✅ Views (Sudah Dibuat)
- `payroll/salary-components/index.blade.php` - List komponen gaji
- `payroll/salary-components/create.blade.php` - Form tambah komponen

---

## 🚀 Cara Penggunaan

### 1. Setup Master Data

#### A. Komponen Gaji (Salary Components)
Komponen gaji adalah pendapatan tetap yang diterima karyawan.

**Contoh Data:**
```
Nama: Gaji Pokok
Tipe: Tetap (Fixed)
Nominal: Rp 5.000.000
Status: Aktif

Nama: Tunjangan Jabatan
Tipe: Persentase
Persentase: 20% (dari gaji pokok)
Status: Aktif
```

**Cara Input:**
1. Buka menu **Penggajian → Komponen Gaji**
2. Klik **Tambah Komponen**
3. Isi form dan simpan

#### B. Potongan (Deductions)
Potongan otomatis berdasarkan absensi atau manual.

**Contoh Data:**
```
Nama: Potongan Terlambat
Tipe: Per Hari
Nominal: Rp 50.000
Auto Calculate: Ya (otomatis dari absensi)
Status: Aktif

Nama: BPJS Kesehatan
Tipe: Persentase
Persentase: 1% (dari gaji pokok)
Auto Calculate: Tidak
Status: Aktif

Nama: Potongan Alpha/Tidak Hadir
Tipe: Per Hari
Nominal: (Gaji Pokok / Total Hari Kerja)
Auto Calculate: Ya
Status: Aktif
```

#### C. Tunjangan/Bonus (Allowances)
Tunjangan tambahan yang bisa diberikan.

**Contoh Data:**
```
Nama: Lembur
Tipe: Per Jam
Nominal: Rp 50.000
Requires Approval: Ya
Status: Aktif

Nama: Bonus Kehadiran Penuh
Tipe: Tetap
Nominal: Rp 500.000
Requires Approval: Ya
Status: Aktif
```

### 2. Generate Payroll

#### Proses Otomatis:
```php
// Di PayrollController (akan dibuat)
public function generate(Request $request) 
{
    $period = $request->period; // Format: 2025-12
    $employees = Employee::where('is_active', true)->get();
    
    foreach ($employees as $employee) {
        // 1. Hitung data absensi bulan ini
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->get();
            
        $presentDays = $attendances->where('status', 'present')->count();
        $lateDays = $attendances->where('status', 'late')->count();
        $absentDays = $totalDays - $presentDays - $lateDays;
        
        // 2. Hitung gaji pokok
        $basicSalary = $employee->basic_salary;
        
        // 3. Hitung tunjangan
        $totalAllowances = 0;
        $activeAllowances = Allowance::where('is_active', true)->get();
        foreach ($activeAllowances as $allowance) {
            // Hitung berdasarkan tipe
        }
        
        // 4. Hitung potongan
        $totalDeductions = 0;
        $activeDeductions = Deduction::where('is_active', true)->get();
        foreach ($activeDeductions as $deduction) {
            if ($deduction->auto_calculate) {
                if ($deduction->type === 'per_day') {
                    // Potongan terlambat
                    $total = $lateDays * $deduction->amount;
                }
            }
        }
        
        // 5. Hitung gaji bersih
        $grossSalary = $basicSalary + $totalAllowances;
        $netSalary = $grossSalary - $totalDeductions;
        
        // 6. Simpan payroll
        Payroll::create([
            'employee_id' => $employee->id,
            'period' => $period,
            'basic_salary' => $basicSalary,
            'total_allowances' => $totalAllowances,
            'total_deductions' => $totalDeductions,
            'gross_salary' => $grossSalary,
            'net_salary' => $netSalary,
            'status' => 'draft',
        ]);
    }
}
```

### 3. Workflow Penggajian

```
1. Setup Master Data → Komponen, Potongan, Tunjangan
2. Karyawan Absen → Data masuk dari Fingerspot
3. Akhir Bulan → Generate Payroll Otomatis
4. Review → Cek perhitungan, edit jika perlu
5. Approve → Manager/Admin approve gaji
6. Pembayaran → Tandai sebagai "Paid"
7. Slip Gaji → Print/Export slip gaji karyawan
```

---

## 📊 Struktur Data Payroll

### Tabel: payrolls
```
employee_id: 1
period: 2025-12
period_start: 2025-12-01
period_end: 2025-12-31
basic_salary: 5000000
total_days: 26 (hari kerja)
present_days: 24
late_days: 2
absent_days: 0
total_allowances: 500000
total_deductions: 100000
gross_salary: 5500000
net_salary: 5400000
status: approved
```

### Tabel: payroll_details
```
payroll_id: 1
type: salary_component
name: Gaji Pokok
calculation_type: fixed
amount: 5000000
quantity: 1
total: 5000000

---

payroll_id: 1
type: allowance
name: Tunjangan Jabatan
calculation_type: percentage
amount: 1000000 (20% x 5.000.000)
quantity: 1
total: 1000000

---

payroll_id: 1
type: deduction
name: Potongan Terlambat
calculation_type: per_day
amount: 50000
quantity: 2
total: 100000
```

---

## 🔧 Yang Perlu Diselesaikan Selanjutnya

### Priority 1 - Core Functionality
1. ✅ Migration & Model (DONE)
2. ✅ Routes (DONE)
3. ⏳ Lengkapi Controller:
   - `DeductionController` - Copy dari SalaryComponentController
   - `AllowanceController` - Copy dari SalaryComponentController
   - `PayrollController` - Implementasi logic generate

4. ⏳ Lengkapi Views:
   - Deductions (copy dari salary-components)
   - Allowances (copy dari salary-components)
   - Payrolls (list, create, show, edit)
   - Slip gaji (print view)

### Priority 2 - Advanced Features
5. ⏳ Tambah Permission di Spatie:
   ```php
   Permission::create(['name' => 'view payroll']);
   Permission::create(['name' => 'create payroll']);
   Permission::create(['name' => 'edit payroll']);
   Permission::create(['name' => 'delete payroll']);
   Permission::create(['name' => 'approve payroll']);
   Permission::create(['name' => 'pay payroll']);
   Permission::create(['name' => 'export payroll']);
   ```

6. ⏳ Tambah Menu Sidebar:
   ```blade
   <li class="nav-item">
       <a href="#" class="nav-link">
           <i class="fas fa-money-bill-wave"></i>
           <span>Penggajian</span>
       </a>
       <ul class="submenu">
           <li><a href="/salary-components">Komponen Gaji</a></li>
           <li><a href="/deductions">Potongan</a></li>
           <li><a href="/allowances">Tunjangan/Bonus</a></li>
           <li><a href="/payrolls">Penggajian</a></li>
       </ul>
   </li>
   ```

7. ⏳ Implementasi Export:
   - Export ke Excel (pakai Maatwebsite Excel)
   - Export Slip Gaji PDF (pakai DomPDF)

### Priority 3 - Reporting
8. ⏳ Laporan Penggajian:
   - Rekap Gaji per Periode
   - Rekap Gaji per Departemen
   - Rekap Potongan
   - Rekap Tunjangan

---

## 💡 Contoh Perhitungan Lengkap

### Karyawan: John Doe
**Data Master:**
- Gaji Pokok: Rp 5.000.000
- Tunjangan Jabatan: 20% dari gaji pokok
- Potongan Terlambat: Rp 50.000/hari
- BPJS: 1% dari gaji pokok

**Data Absensi Bulan Desember 2025:**
- Total Hari Kerja: 26 hari
- Hadir Tepat Waktu: 24 hari
- Terlambat: 2 hari
- Tidak Hadir: 0 hari

**Perhitungan:**
```
Gaji Pokok:              Rp 5.000.000
Tunjangan Jabatan (20%): Rp 1.000.000
─────────────────────────────────────
Gaji Kotor:              Rp 6.000.000

Potongan Terlambat:     -Rp   100.000 (2 hari x Rp 50.000)
BPJS (1%):              -Rp    50.000
─────────────────────────────────────
Total Potongan:         -Rp   150.000

═════════════════════════════════════
GAJI BERSIH (Take Home): Rp 5.850.000
═════════════════════════════════════
```

---

## 📝 Catatan Penting

1. **Gaji Pokok** disimpan di tabel `employees.basic_salary`
2. **Komponen Gaji** adalah tunjangan tetap yang selalu ada setiap bulan
3. **Allowances** adalah bonus yang bisa ditambah/tidak (perlu approval)
4. **Deductions** bisa otomatis (dari absensi) atau manual
5. **Status Payroll:**
   - `draft` - Baru digenerate, bisa diedit
   - `approved` - Sudah disetujui, tidak bisa diedit
   - `paid` - Sudah dibayar

6. **Auto Calculate Deductions:**
   - Sistem otomatis hitung potongan berdasarkan absensi
   - Contoh: Terlambat 2 hari = 2 x Rp 50.000

---

## 🎯 Next Steps

1. **Sekarang:** Lengkapi controller untuk Deduction dan Allowance
2. **Kemudian:** Implementasi PayrollController.generate()
3. **Terakhir:** Buat tampilan payroll dan slip gaji

Struktur database sudah siap, tinggal implementasi logic bisnis! 🚀
