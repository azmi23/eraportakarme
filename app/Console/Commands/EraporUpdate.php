<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use File;
use App\Setting;
use App\Tahun_ajaran;
use App\Semester;
class EraporUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'erapor:update {json?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $json = $this->argument('json');
        if($json){
            $a = ['foo'=>
                ['bar', 'i', $json]
            ];
            $a = collect($a);
            $this->info($a->toJson());
        } else {
            //system('composer update');
            $version = File::get(base_path().'/version.txt');
            $db_version = File::get(base_path().'/db_version.txt');
            \Artisan::call('migrate');
            \Artisan::call('cache:clear');
            \Artisan::call('view:clear');
            \Artisan::call('config:cache');
            $path = base_path('bootstrap/cache');
            $files = File::files($path);
            $config = FALSE;
            $config_ = FALSE;
            foreach($files as $file){
                if($file->getRelativePathname() == 'config-.php'){
                    $config_ = $file->getPathname();
                }
                if($file->getRelativePathname() == 'config.php'){
                    $config = $file->getPathname();
                }
            }
            if($config_ && $config){
                File::move($config_,$config);
            } elseif($config_ && !$config){
                File::move($config_,$path.'/config.php');
            }
            //Semester::where('semester_id', '!=', '20192')->update(['periode_aktif' => 0]);
            //Semester::where('semester_id', '20192')->update(['periode_aktif' => 1]);
            Tahun_ajaran::updateOrCreate(
                [
                    'tahun_ajaran_id' => 2021,
                ],
                [
                    'nama' => '2021/2022',
                    'periode_aktif' => 1,
                    'tanggal_mulai' => '2021-07-20',
                    'tanggal_selesai' => '2022-06-01',
                    'last_sync' => date('Y-m-d H:i:s'),
                ]
            );
            Semester::updateOrCreate(
                [
                    'semester_id' => '20211',
                ],
                [
                    'tahun_ajaran_id' => 2021,
                    'nama' => '2021/2022 Ganjil',
                    'semester' => 1,
                    'periode_aktif' => 1,
                    'tanggal_mulai' => '2021-07-01',
                    'tanggal_selesai' => '2022-12-31',
                    'last_sync' => date('Y-m-d H:i:s'),
                ]
            );
            Semester::where('semester_id', '!=', '20211')->update(['periode_aktif' => 0]);
            Setting::where('key', 'app_version')->update(['value' => $version]);
            Setting::where('key', 'db_version')->update(['value' => $db_version]);
            $this->info('Berhasil memperbaharui aplikasi e-Rapor SMK ke versi '.$version);
        }
    }
}