    <?php

    use App\Models\Setting;
    use Illuminate\Support\Facades\Cache;

    if (!function_exists('setting')) {
        /**
         * Mengambil nilai dari tabel settings.
         *
         * @param string $key
         * @param mixed|null $default
         * @return mixed
         */
        function setting($key, $default = null)
        {
            // Ambil semua pengaturan dari cache untuk efisiensi, atau dari DB jika belum ada di cache.
            $settings = Cache::rememberForever('settings', function () {
                return Setting::all()->pluck('value', 'key');
            });

            return $settings->get($key, $default);
        }
    }
    