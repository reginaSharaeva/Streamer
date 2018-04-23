<?php

namespace AppBundle\Service;

use AppBundle\Entity\Camera;

interface CameraService
{
	public function addNewCamera($data);
	public function updateCamera($data);
	public function deleteCamera(int $id);
}