<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Image;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class ImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        /***********************
         *** Get the parameters from command
         **********************/

        $grpid=env('TEAM_ID', 1);
        $csv=public_path('images').'/csv/'.env('CSV_FILE', 1).'.csv';



        /***********************
         *** Parse the csv file of clinical information
         **********************/

        // Read the csv file to get the attributes and read the dzi to get the image size
        $n = 0;
        if (($handle = fopen($csv, "r")) !== FALSE) {
            while (($data = fgetcsv($handle)) !== FALSE) {

                if ($n>0) {
                    $data=$this->escapeCSV($data);

                    $project_codename=$data[1]; // Go to batch table
                    $dataset_codename=$data[2]; // The batch name, go to batch table

                    // Search the project/batch name and get the batch id from existing/created batch
                    $batch = Batch::where('name', $dataset_codename)
                        ->where('project', $project_codename)
                        ->first();
                    $id='';
                    if (!$batch) {
                        $id = Batch::create([
                            'group_id'=>$grpid,
                            'name'=>$dataset_codename,
                            'project'=>$project_codename,
                            'is_delete'=>0,
                            'created_at'=>now(),
                        ])->id;

                        Permission::create([
                            'batch_id'=>$id,
                            'group_id'=>$grpid,
                            'own'=>1,
                            'created_at'=>now(),
                        ]);
                    }else{
                        $id = $batch->id;
                    }

                    $input=[];

                    $input['name']=$this->removeSuffixIfExists($data[0]);
                    $input['sub_dataset_codename']=$data[3];
                    $input['associated_dataset']=$data[4];
                    $input['associated_subdataset']=$data[5];
                    $input['parent_project_codename']=$data[6];
                    $input['qbrc_pathology_case_id']=$data[7];
                    $input['final_pathologic_diagnosis']=$data[8];
                    $input['stain']=$data[9];
                    $input['stain_marker']=$data[10];
                    $input['block_number']=$data[11];
                    $input['magnification']=$data[12];
                    $input['scanner_specs']=$data[13];
                    $input['width']=$data[14];
                    $input['height']=$data[15];
                    $input['batch_id']=$id;
                    $input['created_at']=now();

                    echo 'Loading #'.$n.' image: '.$input['name']."\n";


                    $path=$project_codename.'/'.$dataset_codename.'/';
                    // Sub dataset name is optional
                    $path.=empty($input['sub_dataset_codename'])?'':$input['sub_dataset_codename'].'/';
                    // Extract the image name and read the corresponding dzi

                    $file=public_path('images')."/original/".$path.$input['name'].".dzi";

                    if (file_exists($file)) {
                        $xml = json_decode(json_encode(simplexml_load_file($file)), true);

                        $input['width']=$xml['Size']['@attributes']['Width'];
                        $input['height']=$xml['Size']['@attributes']['Height'];
                    }

                    // Check if there exists same project+dataset+sub_dataset+image
                    // and check if project/dataset/sub_dataset/image physically exists
                    Image::create($input);
                }
                $n++;
            }
            fclose($handle);
        }
    }


    public function escapeCSV($arr){
        $res=[];
        foreach ($arr as $k=>$v){
            $v=htmlentities(stripslashes(utf8_encode($v)), ENT_QUOTES);
            $v=htmlentities(stripslashes(utf8_encode($v)), ENT_QUOTES);
            $res[$k]=trim($v);// remove any non-printable characters
        }
        return $res;
    }

    public function getFileExtension($filename) {
        $pathinfo = pathinfo($filename);
        if (isset($pathinfo['extension'])) {
            return $pathinfo['extension'];
        } else {
            dd('No extension found: '.$filename);
        }
    }
    public function removeSuffixIfExists($fileName) {
        // Find the last dot (.) in the file name
        $lastDotPosition = strrpos($fileName, '.');

        // If a dot is found, remove the suffix
        if ($lastDotPosition !== false) {
            $fileName = substr($fileName, 0, $lastDotPosition);
        }

        return $fileName;
    }
}
