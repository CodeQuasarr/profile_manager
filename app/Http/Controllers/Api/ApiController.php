<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ApiController
{

    /**
     * @description Fill the model with the fields
     * @param $model
     * @param Collection $fields
     * @return mixed
     */
    protected function fillModel($model, Collection $fields): mixed
    {
        $modelFields = $model->getFillable();
        foreach ($modelFields as $oneKey) {
            if ($fields->has($oneKey)) $model->{$oneKey} = $fields->get($oneKey);
        }
        return $model;
    }

}
