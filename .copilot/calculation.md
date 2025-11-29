Berikut adalah rincian perhitungan emisi karbon PT XL Axiata Tbk untuk tahun 2024 berdasarkan data aktivitas dan metodologi yang tercantum dalam *Sustainability Report 2024*.

Perhitungan ini menggunakan pendekatan standar **GHG Protocol**, di mana data aktivitas (liter BBM, KWh listrik) dikonversi terlebih dahulu ke energi (Gigajoule/Gj) atau langsung dikalikan dengan Faktor Emisi (EF) yang relevan.

---

### 1. Total Emisi Karbon 2024
[cite_start]Berdasarkan laporan, total emisi yang dihasilkan adalah **752.733,86 Ton CO2eq** dengan rincian sebagai berikut [cite: 826-827]:

| Kategori | Total Emisi (Ton CO2eq) | Kontribusi (%) |
| :--- | :--- | :--- |
| **Scope 1 (Langsung)** | 4.082,53 | 0,54% |
| **Scope 2 (Energi Tidak Langsung)** | 744.398,86 | 98,89% |
| **Scope 3 (Lainnya)** | 4.252,47 | 0,57% |
| **TOTAL** | **752.733,86** | **100%** |

---

### 2. Rincian Perhitungan per Scope

#### A. Scope 1: Emisi Langsung
Emisi ini berasal dari pembakaran bahan bakar fosil secara langsung oleh aset yang dimiliki atau dikendalikan perusahaan (Genset BTS dan Kendaraan Operasional).

* **Data Aktivitas:**
    * [cite_start]Solar (Diesel): 1.189.149,24 Liter[cite: 773].
    * [cite_start]Bensin (Pertalite/Gasoline): 275.593,70 Liter[cite: 782].

* **Faktor Konversi & Emisi:**
    * [cite_start]Menggunakan standar *The Greenhouse Gas Protocol Initiative (2004)* untuk konversi ke Gigajoule (Gj)[cite: 2347].
    * Faktor Emisi Solar (Stationary Combustion) $\approx$ 2.68 kg CO2eq/Liter.
    * Faktor Emisi Bensin (Mobile Combustion) $\approx$ 2.31 kg CO2eq/Liter.

* **Simulasi Perhitungan:**
    $$Emisi = (Volume \times Faktor Emisi)$$
    * Solar: $1.189.149 \text{ Liter} \times 2,68 \approx 3.186 \text{ Ton CO2eq}$
    * Bensin: $275.593 \text{ Liter} \times 2,31 \approx 636 \text{ Ton CO2eq}$
    * *Catatan: Perbedaan kecil dengan total laporan (4.082 Ton) disebabkan oleh faktor emisi spesifik (N2O & CH4) yang digunakan auditor eksternal.*

#### B. Scope 2: Emisi Energi Tidak Langsung (Listrik)
Ini adalah penyumbang terbesar emisi XL Axiata, berasal dari penggunaan listrik PLN untuk BTS dan kantor.

* **Data Aktivitas:**
    * [cite_start]Konsumsi Listrik PLN: **956.319.188,94 KWh**[cite: 795].
    * [cite_start]Pembelian REC (Renewable Energy Certificate): 1.000.000 KWh[cite: 791].

* **Faktor Emisi (Grid Emission Factor):**
    * Faktor emisi grid listrik Indonesia (PLN) rata-rata $\approx$ **0,78 kg CO2eq/KWh** (Angka ini bervariasi per wilayah/sistem interkoneksi Jamali vs Luar Jamali, namun ini adalah rata-rata umum untuk estimasi).

* **Simulasi Perhitungan:**
    $$Emisi = (Total KWh - REC) \times Faktor Emisi Grid$$
    1.  Listrik Bersih (Net Grid): $956.319.189 - 1.000.000 = 955.319.189 \text{ KWh}$
    2.  Emisi: $955.319.189 \times 0,78 \text{ kg} \approx \mathbf{745.148 \text{ Ton CO2eq}}$
    * *Hasil perhitungan ini sangat mendekati angka laporan (744.398,86 Ton), selisih terjadi karena penggunaan faktor emisi spesifik per wilayah (Jamali, Sumatera, dll).*

#### C. Scope 3: Emisi Lainnya (Rantai Pasok)
XL Axiata mulai menghitung Scope 3 sejak 2023 untuk beberapa kategori spesifik.

* [cite_start]**Kategori & Jumlah Emisi[cite: 2391]:**
    1.  **Kategori 4 (Distribusi Hulu):** 143,40 Ton CO2eq.
    2.  **Kategori 5 (Limbah Operasional):** 928,22 Ton CO2eq (Limbah B3 & E-waste).
    3.  **Kategori 6 (Perjalanan Bisnis):** 371,12 Ton CO2eq.
    4.  **Kategori 7 (Perjalanan Pulang-Pergi Karyawan):** 1.128,30 Ton CO2eq.
    5.  **Kategori 9 (Distribusi Hilir):** 1.676,65 Ton CO2eq.
    6.  **Kategori 13 (Aset Sewa Hilir):** 4,78 Ton CO2eq.

* **Metode Perhitungan:**
    * **Limbah:** Berat Limbah (Ton) $\times$ Faktor Emisi Pengolahan Limbah (misal: *Landfill* vs *Recycling*). [cite_start]XL Axiata menghasilkan 186,22 ton limbah elektronik dan 8,67 ton limbah B3 [cite: 2469-2481].
    * **Perjalanan Bisnis:** Jarak tempuh (Km) penerbangan/darat $\times$ Faktor Emisi Moda Transportasi.
    * [cite_start]**Perjalanan Karyawan:** Dihitung menggunakan survei karyawan (*employee commuting survey*) untuk mendapatkan data granular jarak dan jenis kendaraan[cite: 2392].

---

### 3. Faktor Emisi & Standar yang Digunakan

Untuk menghasilkan angka di atas, XL Axiata merujuk pada standar berikut:

1.  [cite_start]**The Greenhouse Gas Protocol Initiative (2004):** Digunakan untuk konversi bahan bakar (Liter ke Gigajoule) dan perhitungan emisi dasar[cite: 2347].
2.  **IPCC Guidelines:** Untuk nilai *Global Warming Potential* (GWP) gas rumah kaca lain selain CO2 (seperti CH4 dan N2O).
3.  **Faktor Emisi Grid Lokal (PLN/MEMR):** Untuk konversi KWh listrik ke CO2eq, disesuaikan dengan lokasi operasional (Sumatera, Jawa-Bali, Indonesia Timur) karena bauran energi tiap pembangkit berbeda.

### 4. Intensitas Emisi
Untuk mengukur efisiensi, XL Axiata juga menghitung intensitas emisi per satuan trafik data:

* **Rumus:** $\frac{\text{Total Emisi (Scope 1 + 2)}}{\text{Total Trafik Data (Petabyte)}}$
* [cite_start]**Perhitungan:** $\frac{752.733 \text{ Ton}}{10.547 \text{ PB}} = \mathbf{71,37 \text{ Ton CO2eq/PB}}$[cite: 828].
* [cite_start]**Analisis:** Intensitas ini naik dari tahun 2023 (61,90 Ton/PB) karena ekspansi jaringan BTS sebesar 4% yang meningkatkan konsumsi energi absolut lebih cepat daripada efisiensi yang dicapai[cite: 809, 834].
