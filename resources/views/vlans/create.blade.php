<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah VLAN Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('vlans.store') }}">
                        @csrf
                        <div>
                            <x-input-label for="vlan_id" :value="__('VLAN ID (Hanya Angka)')" />
                            <x-text-input id="vlan_id" class="block mt-1 w-full" type="number" name="vlan_id" :value="old('vlan_id')" required autofocus />
                            <x-input-error :messages="$errors->get('vlan_id')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="name" :value="__('Nama / Keterangan (Opsional)')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Simpan VLAN') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
