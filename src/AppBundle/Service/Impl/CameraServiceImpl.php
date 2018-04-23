<?php

namespace AppBundle\Service\Impl;

use AppBundle\Entity\Camera;
use AppBundle\Repository\CameraRepository as Repository;
use AppBundle\Service\CameraService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;


class CameraServiceImpl implements CameraService
{

	private $container;
	private $repo;
	private $manager;

	public function __construct(Repository $repo, Container $container, EntityManager $manager) {
		$this->repo = $repo;
		$this->container = $container;
		$this->manager = $manager;
	}

	function run_in_background($Command, $Priority = 0)
    {
        $PID = system("$Command > /var/www/html/videoCam/rtmp.log6.txt 2>&1 & echo $!");
        return $PID;
    }

	public function addNewCamera($data) {

		$user = $this->container->get('security.context')->getToken()->getUser();

        $camera = new Camera();
        $camera->link = $data["link"];
        $camera->name = $data["name"];
        $camera->user = $user;
        $camera->proxy_link = str_random(10);

        $command = "/usr/bin/ffmpeg -y -f mjpeg -i '" . $camera->link . "' -threads 2 -vf 'setpts=5*PTS' -f flv -r 25 -s 800x600 -an rtmp://localhost:1935/live/" . $camera->proxy_link;

        $id = $this->run_in_background($command);

        $camera->process_id = $id;

        $this->manager->persist($camera);
        $this->manager->flush();

        $this->run_in_background('/usr/bin/php '.base_path().'/artisan run:record ' . $camera->id);

        return $camera;
	}
	
	public function updateCamera($data) {
		$user = $this->container->get('security.context')->getToken()->getUser();
        $camera = $user->cameras()->where("id", "=", $data["id"])->get()->first();

        $camera->link = $data["link"];
        $camera->name = $data["name"];

        $this->run_in_background('kill ' . $camera->process_id);

        $command = "/usr/bin/ffmpeg -y -f mjpeg -i '" . $camera->link . "' -threads 2 -vf 'setpts=5*PTS' -f flv -r 25 -s 800x600 -an rtmp://localhost:1935/live/" . $camera->proxy_link;

        $id = $this->run_in_background($command);

        $camera->process_id = $id;

        $camera->update();

        return $camera;
	}
	
	public function deleteCamera(int $id) {
		$user = Auth::user();
        $camera = $user->cameras()->where("id", "=", $Id)->get()->first();

        $this->run_in_background('kill ' . $camera->process_id);

        $camera->delete();
	}
}