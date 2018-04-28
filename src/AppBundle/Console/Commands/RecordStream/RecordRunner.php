<?php

namespace AppBundle\Console\Commands\RecordStream;

use AppBundle\Entity\Camera;
//use Artisan;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class RecordRunner extends Command
{
    public function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('run:record')
            // configure an argument
            ->addArgument('camId', InputArgument::REQUIRED, 'The id of the camera.')
            // the short description shown while running "php bin/console list"
            ->setDescription('run record stream to cam');
    }

    function ping($url = NULL)
    {
        if ($url == NULL) return false;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode >= 200 && $httpcode < 300) {
            return true;
        } else {
            return false;
        }
    }

    function run_in_background($Command, $Priority = 0)
    {
        $PID = system("$Command > /var/www/html/videoCam/rtmp.log10.txt 2>&1 & echo $!");
        return $PID;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $cameraId = $input->getArgument('camId');
        $camera = Camera::where("id", "=", $cameraId)->get()->first();
        $user = $camera->user()->get()->first();

        $pathToCopyVideo = public_path('videos/' . $user->id) . "/";
        $date = new \DateTime();
        $nameFileDate = $date->getTimestamp();
        $fileExpansion = ".mp4";

        $fileWitPathWithName = $pathToCopyVideo . $nameFileDate . $fileExpansion;

        if ($this->ping($camera->link) == true) {
            $output->writeln('<info>запись началась</info>');
            shell_exec("/usr/bin/ffmpeg -i '" . $camera->link . "' -t 00:00:05 " . $fileWitPathWithName . "  > /var/www/html/videoCam/rtmp.log10.txt 2>&1");
            $output->writeln('<info>запись кочилась</info>');

            $output->writeln('<info>загрузка началась</info>');
            $command = "/usr/bin/php " . base_path() . "/artisan dropbox:upload " . $fileWitPathWithName . " --camera " . $cameraId . " --user " . $user->id;
            $this->run_in_background($command);
            $output->writeln('<info>загрузка закончилась</info>');

            $this->run_in_background('/usr/bin/php ' . base_path() . '/artisan run:record ' . $camera->id);


        } else {
            $output->writeln('<error>что то пошло не так и запись не удалась</error>');
        }
    }
}

