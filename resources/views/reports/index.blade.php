<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Laporan Keuangan Bulanan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    {{-- Form Filter Bulan & Tahun --}}
                    <form method="GET" action="{{ route('reports.index') }}" class="flex items-end space-x-4 mb-6">
                        <div>
                            <label for="month" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bulan</label>
                            <select name="month" id="month" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun</label>
                            <select name="year" id="year" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <x-primary-button type="submit">Tampilkan</x-primary-button>
                    </form>

                    {{-- Tabel Laporan --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                            <h3 class="font-semibold text-lg mb-2">Ringkasan Utama</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between"><span>GROSS INCOME</span> <span class="font-bold">Rp {{ number_format($grossIncome) }}</span></div>
                                <div class="flex justify-between"><span>OPERASIONAL</span> <span class="font-bold">Rp {{ number_format($operationalExpense) }}</span></div>
                                <div class="flex justify-between border-t border-gray-200 dark:border-gray-600 pt-2 mt-2"><span>NET INCOME</span> <span class="font-bold text-lg text-green-600 dark:text-green-400">Rp {{ number_format($netIncome) }}</span></div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                            <h3 class="font-semibold text-lg mb-2">Rincian Keuangan</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between"><span>Saldo Bulan Lalu</span> <span>Rp {{ number_format($saldoBulanLalu) }}</span></div>
                                <div class="flex justify-between"><span>Total Debit (Modal)</span> <span>Rp {{ number_format($totalDebit) }}</span></div>
                                <div class="flex justify-between"><span>Total Kredit - Operasional</span> <span>Rp {{ number_format($totalKreditOperasional) }}</span></div>
                                <div class="flex justify-between"><span>Total Kredit - Ekspansi</span> <span>Rp {{ number_format($totalKreditEkspansi) }}</span></div>
                                <div class="flex justify-between border-t border-gray-200 dark:border-gray-600 pt-2 mt-2"><span>SALDO AKHIR</span> <span class="font-bold text-lg">Rp {{ number_format($saldo) }}</span></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
