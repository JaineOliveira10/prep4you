<?php

namespace App\Services;

use App\Repositories\DistributionCenterRepository;
use App\Models\DistributionCenter;

class DistributionCenterService
{
    protected $distributionCenterRepository;

    public function __construct(DistributionCenterRepository $distributionCenterRepository)
    {
        $this->distributionCenterRepository = $distributionCenterRepository;
    }

    public function getAll()
    {
        return $this->distributionCenterRepository->all();
    }

    public function findById($id)
    {
        return $this->distributionCenterRepository->find($id);
    }

    public function create(array $data)
    {
        $distributionCenter = $this->distributionCenterRepository->create([
            'acronym' => $data['acronym'],
            'name' => $data['name'],
        ]);

        return $distributionCenter;
    }

    public function update($id, array $data)
    {
        $distributionCenter = $this->distributionCenterRepository->update($id, [
            'acronym' => $data['acronym'],
            'name' => $data['name'],
        ]);

        return $distributionCenter;
    }

    public function delete($id)
    {
        return $this->distributionCenterRepository->delete($id);
    }
}
