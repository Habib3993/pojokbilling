<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Pojokbilling') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

                {{-- A: Active Users --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Active Users</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{-- ID DITAMBAHKAN --}}
                        <span id="active-users-count">...</span> 
                        <span id="active-users-percentage" class="text-sm font-semibold"></span>
                    </h3>
                    <div id="chart-active-users" class="mt-2"></div>
                </div>

                {{-- B: Revenue --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Revenue</p>
                        <span class="text-xs text-gray-400">Last 7 days</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{-- ID DITAMBAHKAN --}}
                        <span id="revenue-total">...</span> 
                        <span id="revenue-percentage" class="text-sm font-semibold"></span>
                    </h3>
                    <div id="chart-revenue" class="mt-2"></div>
                </div>

                {{-- C: Active Subscriptions --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Active Subscriptions</p>
                        <span class="text-xs text-gray-400">Last 7 days</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{-- ID DITAMBAHKAN --}}
                        <span id="subs-count">...</span> 
                        <span id="subs-percentage" class="text-sm font-semibold"></span>
                    </h3>
                    <div id="chart-subs" class="mt-2"></div>
                </div>

                {{-- D: Expenses --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Expenses</p>
                        <span class="text-xs text-gray-400">Monthly</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{-- ID DITAMBAHKAN --}}
                        <span id="expenses-total">...</span> 
                        <span id="expenses-percentage" class="text-sm font-semibold"></span>
                    </h3>
                    <div id="chart-expenses" class="mt-2"></div>
                </div>

                {{-- E: Growth --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Growth</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{-- ID DITAMBAHKAN --}}
                        <span id="growth-total">...</span> 
                        <span id="growth-percentage" class="text-sm font-semibold"></span>
                    </h3>
                    <div id="chart-growth" class="mt-2"></div>
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
                        <span id="custom-metric-percentage" class="text-sm font-semibold"></span>
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
            element.textContent = (value > 0 ? '+' : '') + value + '%';
            element.classList.remove('text-green-600', 'text-red-600');
            if (value > 0) {
                element.classList.add('text-green-600');
            } else if (value < 0) {
                element.classList.add('text-red-600');
            }
        }

        // 1. Inisialisasi semua chart dengan data kosong agar tampil saat loading
        const activeUsersChart = new ApexCharts(document.querySelector("#chart-active-users"), {
            chart: { type: 'radialBar', height: 150 }, series: [0], labels: ['Active'],
            plotOptions: { radialBar: { hollow: { size: '70%' }, dataLabels: { show: true, value: { fontSize: '22px', formatter: val => val + '%' } } } },
            colors: ['#206bc4']
        });
        activeUsersChart.render();

        const revenueChart = new ApexCharts(document.querySelector("#chart-revenue"), {
            chart: { type: 'line', height: 150, sparkline: { enabled: true }},
            series: [{ name: 'Revenue', data: [] }], stroke: { curve: 'smooth', width: 3 }, colors: ['#206bc4']
        });
        revenueChart.render();

        const subsChart = new ApexCharts(document.querySelector("#chart-subs"), {
            chart: { type: 'bar', height: 150, sparkline: { enabled: true }},
            series: [{ name: 'Subscriptions', data: [] }], colors: ['#206bc4'], plotOptions: { bar: { columnWidth: '60%' }}
        });
        subsChart.render();

        const expensesChart = new ApexCharts(document.querySelector("#chart-expenses"), {
            chart: { type: 'area', height: 150, sparkline: { enabled: true }},
            series: [{ name: 'Expenses', data: [] }], stroke: { curve: 'smooth', width: 2 }, colors: ['#d63939'], fill: { opacity: 0.3 }
        });
        expensesChart.render();

        const growthChart = new ApexCharts(document.querySelector("#chart-growth"), {
            chart: { type: 'radialBar', height: 150 }, series: [0], labels: ['Growth'],
            plotOptions: { radialBar: { hollow: { size: '65%' } } }, colors: ['#2fb344']
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
                const response = await fetch('/api/dashboard-stats');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();

                // Update Kartu A: Active Users
                document.getElementById('active-users-count').textContent = data.activeUsers.count;
                setPercentage(document.getElementById('active-users-percentage'), data.activeUsers.percentage);
                activeUsersChart.updateSeries(data.activeUsers.series);

                // Update Kartu B: Revenue
                document.getElementById('revenue-total').textContent = data.revenue.total;
                setPercentage(document.getElementById('revenue-percentage'), data.revenue.percentage);
                revenueChart.updateSeries([{ data: data.revenue.series }]);
                
                // Update Kartu C: Active Subscriptions
                document.getElementById('subs-count').textContent = data.subscriptions.count;
                setPercentage(document.getElementById('subs-percentage'), data.subscriptions.percentage);
                subsChart.updateSeries([{ data: data.subscriptions.series }]);

                // Update Kartu D: Expenses
                document.getElementById('expenses-total').textContent = data.expenses.total;
                setPercentage(document.getElementById('expenses-percentage'), data.expenses.percentage);
                expensesChart.updateSeries([{ data: data.expenses.series }]);

                // Update Kartu E: Growth
                document.getElementById('growth-total').textContent = data.growth.total + '%';
                setPercentage(document.getElementById('growth-percentage'), data.growth.percentage);
                growthChart.updateSeries(data.growth.series);

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
