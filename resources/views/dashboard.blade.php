<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Pojokbilling') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8"> 
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

                {{-- A: Total Pelanggan (Desain Final & Lebih Robust) --}}
                <div class="rounded-lg shadow-md text-white overflow-hidden flex flex-col" style="background-color:rgb(122, 94, 2);"> {{-- Warna krem langsung dengan style --}}
                    {{-- Bagian Atas (Konten Utama) --}}
                    <div class="p-5 flex justify-between items-center flex-grow">
                        <div>
                            {{-- Angka Total, dibuat lebih besar dengan text-6xl --}}
                            <h3 class="text-2xl font-bold" id="total-users-count">...</h3>
                            
                            {{-- Label yang Benar: Total Pelanggan --}}
                            <p class="text-lg mt-1">Total Pelanggan</p>
                        </div>
                        <div class="opacity-70">
                            {{-- Ikon User (Contoh menggunakan Heroicons, pastikan terinstal atau ganti dengan SVG/gambar Anda) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                    {{-- Bagian Bawah (Link Footer dengan warna border/latar lebih gelap) --}}
                    <a href="{{ route('customers.index') }}" class="block p-2 text-center transition-colors" style="background-color:rgb(226, 171, 31);"> {{-- Warna lebih gelap langsung dengan style --}}
                        info <span class="inline-block ml-1">&rarr;</span>
                    </a>
                </div>

                {{-- B: Revenue (Struktur Diperbaiki) --}}
                <div class="rounded-lg shadow-md text-white overflow-hidden flex flex-col" style="background-color: #166534;"> {{-- Warna Hijau Tua --}}
                    {{-- Bagian Atas (Konten Utama) --}}
                    <div class="p-5 flex justify-between items-center flex-grow">
                        {{-- SISI KIRI: Semua teks dikelompokkan di sini --}}
                        <div>
                            <h3 class="text-4xl font-bold" id="revenue-total">...</h3>
                            <p class="text-lg mt-1">Revenue</p>
                        </div>
                        {{-- SISI KANAN: Ikon diletakkan di sini --}}
                        <div class="opacity-70">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    {{-- Bagian Tengah (Container untuk Chart Diagram) --}}
                    <div id="chart-revenue" class="px-5 -mt-4"></div>
                    {{-- Bagian Bawah (Link Footer) --}}
                    <a href="{{ route('reports.index') }}" class="block p-2 text-center transition-colors" style="background-color:rgb(38, 151, 83);"> {{-- Warna hijau lebih gelap --}}
                        info <span class="inline-block ml-1">&rarr;</span>
                    </a>
                </div>

                {{-- C: Sales Performance (Tinggi Disesuaikan) --}}
                <div class="rounded-lg shadow-md text-white overflow-hidden flex flex-col" style="background-color: #8c0000;"> {{-- Warna Merah Tua --}}
                    {{-- Bagian Atas (Konten Utama) --}}
                    <div class="p-5 flex justify-between items-center">
                        <div>
                            {{-- Angka Total --}}
                            <h3 class="text-4xl font-bold" id="sales-count">...</h3>
                            {{-- Label --}}
                            <p class="text-lg mt-1">Sales Aktif</p>
                        </div>
                        <div class="opacity-50">
                            {{-- Ikon --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                    {{-- Bagian Tengah (Container untuk Chart) - mt-auto akan mendorong footer ke bawah --}}
                    <div id="chart-sales" class="px-5 -mt-6 flex-grow"></div>
                    {{-- Bagian Bawah (Link Footer) --}}
                    <a href="{{ route('customers.index') }}" class="block p-2 text-center transition-colors" style="background-color: #bb0000;">
                        info <span class="inline-block ml-1">&rarr;</span>
                    </a>
                </div>

                {{-- D: Expenses --}}
                <div class="text-white rounded-2xl shadow-md flex flex-col overflow-hidden" style="background-color:rgb(46, 64, 178);">
                    <div class="p-5 flex-grow">
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Expenses</p>
                            <span class="text-xs text-gray-400">Monthly</span>
                        </div>
                        <div class="opacity-70">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-4xl font-bold text-gray-900 dark:text-gray-100">
                            <span id="expenses-total">...</span>
                            <span id="expenses-percentage" class="text-sm font-semibold"></span> <!-- Added missing element -->
                        </h3>
                        <div id="chart-expenses" class="mt-2"></div>
                    </div>
                    <a href="#" class="block p-2 text-center text-white transition-colors" style="background-color:rgb(0, 31, 131);">
                        info <span class="inline-block ml-1">&rarr;</span>
                    </a>
                </div>

                {{-- E: Growth --}}
                <div class="text-white rounded-2xl shadow-md flex flex-col overflow-hidden" style="background-color: #a16207;">
                    <div class="opacity-70">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="p-5 flex-grow">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Growth</p>
                        <h3 class="text-4xl font-bold text-gray-900 dark:text-gray-100">
                            <span id="growth-total">...</span>
                            <span id="growth-percentage" class="text-sm font-semibold"></span> <!-- Added missing element -->
                        </h3>
                        <div id="chart-growth" class="mt-2"></div>
                    </div>
                    <a href="#" class="block p-2 text-center text-white transition-colors" style="background-color: #ca8a04;">
                        info <span class="inline-block ml-1">&rarr;</span>
                    </a>
                </div>

                {{-- F: Custom (Free Section) --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Custom Metric</p>
                        <span class="text-xs text-gray-400">This Month</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{-- ID DITAMBAHKAN --}}
                        <span id="custom-metric-count">...</span> 
                        <span id="custom-metric-percentage" class="text-sm font-semibold"></span> <!-- Added missing element -->
                    </h3>
                    <div id="chart-custom" class="mt-2"></div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        // =================================================================================
        // SELURUH KODE JAVASCRIPT DIUBAH UNTUK MENDUKUNG DATA DINAMIS
        // =================================================================================

        // Fungsi untuk mengatur warna persentase (hijau untuk positif, merah untuk negatif)
        function setPercentage(element, value) {
            if (value === undefined || value === null) {
                element.textContent = ''; // Hide percentage text if value is undefined or null
                element.classList.remove('text-green-600', 'text-red-600');
            } else {
                element.textContent = (value > 0 ? '+' : '') + value + '%';
                element.classList.remove('text-green-600', 'text-red-600');
                if (value > 0) {
                    element.classList.add('text-green-600');
                } else if (value < 0) {
                    element.classList.add('text-red-600');
                }
            }
        }

        // 1. Inisialisasi semua chart dengan data kosong agar tampil saat loading

        // Inisialisasi Revenue Chart (sekarang menjadi Bar Chart dengan warna putih)
        const revenueChart = new ApexCharts(document.querySelector("#chart-revenue"), {
            chart: {
                type: 'bar', // 1. Tipe diubah menjadi 'bar'
                height: 80,
                sparkline: { enabled: true }
            },
            series: [{
                name: 'Revenue',
                data: []
            }],
            colors: ['rgb(110, 249, 131)'], // 2. Warna batang dibuat putih
            plotOptions: {
                bar: {
                    columnWidth: '60%' // 3. Atur lebar batang
                }
            },
            labels: [], // 4. Tambahkan properti labels agar bisa di-update
            tooltip: {
                theme: 'dark',
                x: {
                    show: true, // Tampilkan label bulan di tooltip
                },
                y: {
                    formatter: function (val) {
                        return "Rp " + new Intl.NumberFormat('id-ID').format(val)
                    }
                }
            }
        });
        revenueChart.render();
        
        // Inisialisasi salesChart
        const salesChart = new ApexCharts(document.querySelector("#chart-sales"), {
            chart: { type: 'donut', height: 120 },
            series: [],
            labels: [],
            legend: { show: false },
            dataLabels: { enabled: false },
            tooltip: {
                y: { formatter: (val) => val + " pelanggan" }
            }
        });
        salesChart.render()
        
        // Ekspansi 
        const expensesChart = new ApexCharts(document.querySelector("#chart-expenses"), {
            chart: {
                type: 'area',
                height: 150,
                // sparkline: { enabled: true }, // <-- HAPUS ATAU KOMENTARI BARIS INI
                toolbar: { show: false },      // Tambahan: Sembunyikan menu hamburger
                zoom: { enabled: false }       // Tambahan: Matikan fungsi zoom saat di-drag
            },
            grid: {
                show: false, // Tambahan: Sembunyikan garis grid
                padding: { left: 0, right: 0 } // Tambahan: Hapus padding agar grafik penuh
            },
            xaxis: {
                labels: { show: false }, // Sembunyikan label di sumbu-X
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { show: false } // Sembunyikan label di sumbu-Y
            },
            series: [{
                name: 'Pengeluaran',
                data: []
            }],
            stroke: {
                curve: 'smooth',
                width: 2
            },
            colors: ['#d63939'],
            fill: {
                opacity: 0.3
            },
            labels: [],
            tooltip: {
                theme: 'dark',
                x: {
                    show: true,
                },
                y: {
                    title: {
                        formatter: (seriesName) => ''
                    },
                    formatter: function (val) {
                        return "Rp " + new Intl.NumberFormat('id-ID').format(val);
                    }
                }
            }
        });
        expensesChart.render();

        // growth
        const growthChart = new ApexCharts(document.querySelector("#chart-growth"), {
            chart: {
                type: 'bar', // Tipe diubah menjadi 'bar'
                height: 150,
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            plotOptions: {
                bar: {
                    columnWidth: '60%',
                    distributed: true, // Setiap batang punya warna berbeda (opsional)
                }
            },
            series: [{
                name: 'PSB', // Nama series untuk tooltip
                data: []
            }],
            labels: [],
            xaxis: {
                labels: { show: false },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { show: false }
            },
            grid: {
                show: false,
                padding: { top: 0, right: 0, bottom: 0, left: 0 }
            },
            legend: {
                show: false // Sembunyikan legenda
            },
            tooltip: {
                theme: 'dark',
                x: {
                    show: true // Tampilkan nama bulan di tooltip
                },
                y: {
                    title: {
                        formatter: (seriesName) => 'Total PSB:'
                    },
                    formatter: function (val) {
                        return val + " Pelanggan"; // Format angka di tooltip
                    }
                }
            }
        });
        growthChart.render();

        const customChart = new ApexCharts(document.querySelector("#chart-custom"), {
            chart: { type: 'donut', height: 150 }, series: [], labels: [],
            colors: ['#206bc4', '#2fb344', '#d63939'], legend: { show: false }
        });
        customChart.render();

        // 2. Fungsi untuk mengambil data dari backend dan update dashboard
        async function fetchDashboardData() {
            try {
                // Ganti '/api/dashboard-stats' jika route Anda berbeda
                const response = await fetch("{{ route('dashboard.stats') }}");
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();

                // Update Kartu A: total Users
                document.getElementById('total-users-count').textContent = data.totalUsers.count;

                // Update Kartu B: Revenue
                document.getElementById('revenue-total').textContent = data.revenue.total;
                // setPercentage(document.getElementById('revenue-percentage'), data.revenue.percentage); // Baris ini di-nonaktifkan karena elemennya tidak ada di HTML Anda
                revenueChart.updateOptions({ // Gunakan updateOptions untuk memperbarui series dan labels
                    series: [{
                        data: data.revenue.series
                    }],
                    labels: data.revenue.labels
                });
                // Update Kartu C: Sales Performance
                if (data.sales) {
                    document.getElementById('sales-count').textContent = data.sales.count;
                    salesChart.updateOptions({series: data.sales.series,labels: data.sales.labels
                    });
                }
                // Update Kartu D: Expenses
                document.getElementById('expenses-total').textContent = data.expenses.total;
                setPercentage(document.getElementById('expenses-percentage'), data.expenses.percentage);
                expensesChart.updateOptions({
                    series: [{
                        data: data.expenses.series
                    }],
                    labels: data.expenses.labels,
                    dataLabels: {
                        enabled: false // Disable data labels on the graph
                    },
                    tooltip: {
                        y: {
                            formatter: function (val, opts) {
                                const label = opts.w.globals.labels[opts.dataPointIndex];
                                return label + ': Rp ' + new Intl.NumberFormat('id-ID').format(val);
                            }
                        }
                    }
                });

                // Update Kartu E: Growth
                document.getElementById('growth-total').textContent = data.growth.total;
                setPercentage(document.getElementById('growth-percentage'), data.growth.percentage);
                growthChart.updateOptions({
                    series: [{
                        data: data.growth.series
                    }],
                    labels: data.growth.labels
                });

                // Update Kartu F: Custom Metric
                document.getElementById('custom-metric-count').textContent = data.customMetric.count;
                setPercentage(document.getElementById('custom-metric-percentage'), data.customMetric.percentage);
                customChart.updateSeries(data.customMetric.series);
                // Jika chart donut punya label, Anda juga bisa mengupdatenya
                // customChart.updateOptions({ labels: data.customMetric.labels });

            } catch (error) {
                console.error('Gagal mengambil data dashboard:', error);
                // Anda bisa menampilkan pesan error di UI di sini jika perlu
            }
        }

        // 3. Panggil fungsi saat halaman selesai dimuat
        document.addEventListener('DOMContentLoaded', fetchDashboardData);
    </script>
    @endpush
</x-app-layout>
