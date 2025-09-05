<nav x-data="{ open: false }" class="sticky top-0 z-50 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex" style="z-index: 999">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('img/logo.png') }}" alt="Pojok Net Logo" class="block h-9 w-auto">
                    </a>
                </div>

                <div class="hidden space-x-4 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="48" class="z-50">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    <div>Pelanggan</div>
                                    <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('customers.index')">{{ __('Daftar Semua Pelanggan') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('customers.active')">{{ __('Pelanggan Aktif') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('customers.inactive')">{{ __('Pelanggan Tidak Aktif') }}</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="48" class="z-50">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    <div>Layanan</div>
                                    <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('packages.index')">{{ __('Daftar Paket') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('recharge.create')">{{ __('Isi Ulang / Recharge') }}</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="48" class="z-50">
                            <x-slot name="trigger">
                                 <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    <div>Jaringan</div>
                                    <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('routers.index')">{{ __('Daftar Router') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('ip-pools.index')">{{ __('IP Pool') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('olts.index')">{{ __('OLT') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('vlans.index')">{{ __('VLAN') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('radius.index')">{{ __('FreeRADIUS') }}</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    
                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="48" class="z-50">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    <div>Peta</div>
                                    <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('map.index')">{{ __('Peta Pelanggan') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('inventory.map.index')">{{ __('Inventaris Jaringan') }}</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="48" class="z-50">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    <div>Administrasi</div>
                                    <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('transactions.index')">{{ __('Transaksi') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('reports.index')">{{ __('Laporan') }}</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    
                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="48" class="z-50">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    <div>Pesan</div>
                                    <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('whatsapp.private.create')">{{ __('Pesan Pribadi (WA)') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('whatsapp.bulk.create')">{{ __('Pesan Massal (WA)') }}</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    
                    <x-nav-link :href="route('genieacs-servers.index')" :active="request()->routeIs('genieacs-servers.*')">
                        {{ __('GenieACS') }}
                    </x-nav-link>
                    
                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="48" class="z-50">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    <div>Pengaturan</div>
                                    <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('settings.general.index')">{{ __('Umum') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('users.index')" :active="request()->routeIs('users.*')">{{ __('User') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('logs.index')" :active="request()->routeIs('logs.index')">{{ __('Log') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('backup.index')" :active="request()->routeIs('backup.index')">{{ __('Cadangkan/Pulihkan') }}</x-dropdown-link>
                                <x-dropdown-link href="#">{{ __('Kontak Kami') }}</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48" class="z-50">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <div class="pt-2 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">Pelanggan</div>
                <x-responsive-nav-link :href="route('customers.index')">{{ __('Daftar Semua Pelanggan') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customers.active')">{{ __('Pelanggan Aktif') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customers.inactive')">{{ __('Pelanggan Tidak Aktif') }}</x-responsive-nav-link>
            </div>

             <div class="pt-2 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">Layanan</div>
                <x-responsive-nav-link :href="route('packages.index')">{{ __('Daftar Paket') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('recharge.create')">{{ __('Isi Ulang / Recharge') }}</x-responsive-nav-link>
            </div>

            <div class="pt-2 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">Jaringan</div>
                <x-responsive-nav-link :href="route('routers.index')">{{ __('Daftar Router') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('ip-pools.index')">{{ __('IP Pool') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('olts.index')">{{ __('OLT') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('vlans.index')">{{ __('VLAN') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('radius.index')">{{ __('FreeRADIUS') }}</x-responsive-nav-link>
            </div>

            <div class="pt-2 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">Peta</div>
                <x-responsive-nav-link :href="route('map.index')">{{ __('Peta Pelanggan') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('inventory.map.index')">{{ __('Inventaris Jaringan') }}</x-responsive-nav-link>
            </div>

            <div class="pt-2 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">Administrasi</div>
                <x-responsive-nav-link :href="route('transactions.index')">{{ __('Transaksi') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('reports.index')">{{ __('Laporan') }}</x-responsive-nav-link>
            </div>

            <div class="pt-2 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">Pesan</div>
                <x-responsive-nav-link :href="route('whatsapp.private.create')">{{ __('Pesan Pribadi (WA)') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('whatsapp.bulk.create')">{{ __('Pesan Massal (WA)') }}</x-responsive-nav-link>
            </div>
            
            <div class="pt-2 pb-1 border-t border-gray-200 dark:border-gray-600">
                 <x-responsive-nav-link :href="route('genieacs-servers.index')" :active="request()->routeIs('genieacs-servers.*')">
                    {{ __('GenieACS') }}
                </x-responsive-nav-link>
            </div>

        </div>

        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>