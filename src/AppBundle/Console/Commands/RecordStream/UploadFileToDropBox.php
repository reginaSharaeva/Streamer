<?php

namespace AppBundle\Console\Commands\RecordStream;

use DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use Config;
use Dropbox\Client;
use Dropbox\WriteMode;
use File;
use AppBundle\Entity\File as DbFile;

class UploadFileToDropBox extends Command
{
   
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('dropbox:upload')
            // configure an argument
            ->addArgument('filePath', InputArgument::REQUIRED, 'The path of file.')
            // configure an argument
            ->addArgument('camera', InputArgument::OPTIONAL, 'The id of the camera.')
            // configure an argument
            ->addArgument('user', InputArgument::OPTIONAL, 'The id of the user.')
            // the short description shown while running "php bin/console list"
            ->setDescription('upload file to tropbox');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeln('<info>начало загрузки файла</info>');
        $path = $input->getArgument('filePath');
        $cameraId = $input->getArgument('camera');
        $userId = $input->getArgument('user');

        if (empty($path) || empty($userId) || empty($cameraId)) {
            $output->writeln('<error>ошибка не переданы параметры для загрузки фала</error>');
            exit(0);
        }
        $fileName = explode("/", $path)[sizeof(explode("/", $path)) - 1];

        $Client = new Client(
            Config::get('dropbox.connections.main.token'),
            Config::get('dropbox.connections.main.app')
        );

        $file = fopen($path, 'rb');

        $size = filesize($path);

        $dropboxFileName = Config::get("dropbox.pre") . $userId . "/" . $fileName;

        $Client->uploadFile($dropboxFileName, WriteMode::add(), $file, $size);

        $link = $Client->createTemporaryDirectLink($dropboxFileName);


        DB::table('files')->insert([
            [
                'camera_id' => $cameraId,
                "name" => $dropboxFileName,
                "link" => $link[0],
                "size" => $size,
            ]
        ]);

        File::delete($path);
    }


}