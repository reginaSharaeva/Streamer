<?php

namespace AppBundle\Service\Impl;

use AppBundle\Entity\File;
use AppBundle\Repository\FileRepository as Repository;
use AppBundle\Service\FileService;
use Doctrine\ORM\EntityManager;

class FileServiceImpl implements FileService
{
	private $manager;
	private $repo;

	public function __construct(Repository $repo, EntityManager $manager) {
		$this->repo = $repo;
		$this->manager = $manager;
	}

	public function addNewFile($data) {

		$file = new File();
		$file->camera = $data['camera'];
		$file->name = $data['name'];
		$file->link = $data['link'];
		$file->size = $data['size'];

		$this->manager->persist($file);
        $this->manager->flush();
	}

}