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
        //Backup the main database
        $filename=$this->argument('who')."_".strtotime(now()).".sql";
        $command="mysqldump --user=".env('DB_USERNAME')." --password=".env('DB_PASSWORD')." --host=".env('DB_HOST')." ".env('DB_DATABASE')." > ".storage_path()."/app/backup/".$filename;
        $output = exec($command, $output_array, $return_value);


        //Backup the 'CentralSlideRegistry' database
        $filenameCentralSlideDB = $this->argument('who')."_CentralSlideRegistry_".strtotime(now()).".sql";
        $commandCentralSLideDB = "mysqldump --user=".env('DB_CENTRALSR_USERNAME')." --password=".env('DB_CENTRALSR_PASSWORD')." --host=".env('DB_HOST')." ".env('DB_CENTRALSR_DATABASE')." > ".storage_path()."/app/backup/".$filenameCentralSlideDB;
        $csr_output=exec($commandCentralSLideDB, $outputArrayCentral, $returnValueCentral);

        if ($return_value !== 0 || $returnValueCentral !== 0) {
            Log::error ("Database backup failed\n");
            // Output the error message
            Log::error ("Error in Main DB: $output");
            Log::error("Error in Central DB: $csr_output");
        }

        //Backup annotations .db files
        $tarFilename = $this->argument('who')."_annotationDbFiles_".strtotime(now()).".tar.gz";
        $tarFilePath = storage_path()."/app/backup/".$tarFilename;
        $tarCommand = "tar -czf {$tarFilePath} -C ".env('ANNOTATION_DBFILE_PATH');
        exec($tarCommand, $outputArrayTar, $returnValueTar);

        // Check for errors in archiving
        if ($returnValueTar !== 0) {
            Log::error("Failed to archive the .db files from {$dbFolder}");
            Log::error("Error: $outputArrayTar");
        }

        // Delete backup copies older than 30 days
        $this->deleteOldBackups($backupDir);
        // $sql_files = glob(storage_path("app/backup/*.sql"));
        // usort($sql_files, function($a, $b) {
        //     return filemtime($b) - filemtime($a);
        // });
        // $filesToKeep = array_slice($sql_files, 0, config('app.iv.backup_limit'));

        // foreach ($sql_files as $file) {
        //     if (!in_array($file, $filesToKeep)) {
        //         unlink($file);
        //     }
        // }
    }

    protected function deleteOldBackups($sqlDir, $tarDir)
    {
        $sqlFiles = glob("{$sqlDir}/*.sql");
        $tarFiles = glob("{$tarDir}/*.tar.gz");
        $backupFiles = array_merge($sqlFiles, $tarFiles);
    
        usort($backupFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
    
        $filesToKeep = array_slice($backupFiles, 0, config('app.iv.backup_limit'));
    
        foreach ($backupFiles as $file) {
            if (!in_array($file, $filesToKeep)) {
                unlink($file);
            }
        }
    }

}
