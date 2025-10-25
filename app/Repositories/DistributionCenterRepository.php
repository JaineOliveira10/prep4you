<?php

namespace App\Repositories;

use App\Models\DistributionCenter;
use App\Interfaces\DistributionCenterRepositoryInterface;

class DistributionCenterRepository implements DistributionCenterRepositoryInterface
{
    protected $model;

    public function __construct(DistributionCenter $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $distributionCenter = $this->find($id);
        $distributionCenter->update($data);
        return $distributionCenter;
    }

    public function delete($id)
    {
        $distributionCenter = $this->find($id);
        return $distributionCenter->delete();
    }
}
