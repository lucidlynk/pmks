<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            AppSetting::APP_NAME        => 'PUSKESOSGCT Buleleng',
            AppSetting::APP_DESCRIPTION => 'Pusat Kesejahteraan Sosial Kabupaten Buleleng',
            AppSetting::APP_LOGO        => null,
            AppSetting::APP_FAVICON     => null,
            AppSetting::PEMKAB_LOGO     => null,
        ];

        foreach ($defaults as $key => $value) {
            AppSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
