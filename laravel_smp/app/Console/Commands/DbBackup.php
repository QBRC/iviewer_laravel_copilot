<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;

class DbBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {who=system}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create database backup';

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
     * @return int
     */
    public function handle()
    {
        $filename=$this->argument('who')."_".strtotime(now()).".sql";
        $command="mysqldump --user=".env('DB_USERNAME')." --password=".env('DB_PASSWORD')." --host=".env('DB_HOST')." ".env('DB_DATABASE')." > ".storage_path()."/app/backup/".$filename;
        $output = exec($command, $output_array, $return_value);

        if ($return_value !== 0) {
            Log::error ("Database backup failed\n");
            // Output the error message
            Log::error ("Error: $output");
        }

        // Delete backup copies older than 30 days
        $sql_files = glob(storage_path("app/backup/*.sql"));
        usort($sql_files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $filesToKeep = array_slice($sql_files, 0, config('app.iv.backup_limit'));

        foreach ($sql_files as $file) {
            if (!in_array($file, $filesToKeep)) {
                unlink($file);
            }
        }
    }
}
