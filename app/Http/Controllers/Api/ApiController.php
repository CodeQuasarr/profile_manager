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

    /**
     * @description Reduce fields to those of the model
     *
     * This method reduces the fields to those of the model according to the fields passed as parameters.
     * If the model has fields that are not passed as parameters, they will not be taken into account.
     *
     * @param $model
     * @param Collection $requestField
     * @return Collection
     */
    protected function getModelFields($model, Collection $requestField): Collection {
        $modelFields = $model->getFillable();
        $fields = collect();
        foreach ($modelFields as $oneKey) {
            if ($requestField->has($oneKey)) $fields->put($oneKey, $requestField->get($oneKey));
        }
        return $fields;
    }


}
