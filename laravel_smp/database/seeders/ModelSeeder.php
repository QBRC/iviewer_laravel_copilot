<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ModelSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $ip=env('API_IP');
        $annoport=env('ANNOTATION_PORT');
        $deepport=env('DEEPZOOM_PORT');
        $hdyoloport=env('HDYOLO_PORT');
        $chatport=env('CHAT_PORT');
        $ws=str_replace(array('http://', 'https://'), 'ws://', $ip).":".$annoport."/";
        // API
        $data = [
            [
                'name' => 'slide',
                'api' => $ip.':'.$deepport.'/proxy/dummy.dzi?image_id=IMAGE_UUID&file=FILE_PATH&registry=slide',
                'description' => 'Generate tiles with non-transparent background (jpg)',
                'type' => 0,
                'created_at' => now(),
            ],
            [
                'name' => 'scale',
                'api' => $ip.':'.$deepport.'/proxy/params?image_id=IMAGE_UUID',
                'description' => 'Extract scale information',
                'type' => 0,
                'created_at' => now(),
            ],
            [
                'name' => 'createDB',
                'api' => $ip.':'.$annoport.'/annotation/create?image_id=IMAGE_UUID',
                'description' => 'Create a sqlite db file as per image ID',
                'type' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'getAnnotator',
                'api' => $ip.':'.$annoport.'/annotation/annotators?image_id=IMAGE_UUID',
                'description' => 'Get all annotators as per image ID',
                'type' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'getLabels',
                'api' => $ip.':'.$annoport.'/annotation/labels?image_id=IMAGE_UUID',
                'description' => 'Get all labels as per image ID',
                'type' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'insert',
                'api' => $ip.':'.$annoport.'/annotation/insert?image_id=IMAGE_UUID',
                'description' => 'Create a new annotation by image ID',
                'type' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'read',
                'api' => $ip.':'.$annoport.'/annotation/read?image_id=IMAGE_UUID&item_id=',
                'description' => 'Read an annotation by image ID and item ID',
                'type' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'update',
                'api' => $ip.':'.$annoport.'/annotation/update?image_id=IMAGE_UUID&item_id=',
                'description' => 'Update an annotation by image ID and item ID',
                'type' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'delete',
                'api' => $ip.':'.$annoport.'/annotation/delete?image_id=IMAGE_UUID&item_id=',
                'description' => 'Delete an annotation by image ID and item ID',
                'type' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'search',
                'api' => $ip.':'.$annoport.'/annotation/search?image_id=IMAGE_UUID',
                'description' => 'Fetch all annotation as per image ID',
                'type' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'stream',
                'api' => $ws.'annotation/stream?image_id=IMAGE_UUID',
                'description' => 'Fetch all annotation as per image ID',
                'type' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'countAnnos',
                'api' => $ip.':'.$annoport.'/annotation/count?image_id=IMAGE_UUID',
                'description' => 'Count all annotation as per image ID',
                'type' => 1,
                'created_at' => now(),
            ],
            [
                'name' => 'HDYolo',
                'api' => $ip.':'.$hdyoloport.'/proxy/dummy.dzi?image_id=IMAGE_UUID&file=FILE_PATH&registry=yolov8-lung',
                'description' => 'Generate tiles predicted by HD-Yolo model',
                'type' => 9,
                'created_at' => now(),
            ],
            [
                'name' => 'chat',
                'api' => $ip.':'.$chatport.'/copilot?image_id=IMAGE_UUID&file=FILE_PATH&registry=llava',
                'description' => 'Generate chat response based on user inputs by GEMMA',
                'type' => 0,
                'created_at' => now(),
            ],
        ];

        DB::table('modes')->insert($data);

    }

}