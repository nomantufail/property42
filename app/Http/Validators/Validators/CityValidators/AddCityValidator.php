<?php
/**
 * Created by PhpStorm.
 * User: waqas
 * Date: 4/4/2016
 * Time: 4:15 PM
 */

namespace App\Http\Validators\Validators\CityValidators;


use App\Http\Validators\Interfaces\ValidatorsInterface;

class AddCityValidator extends CityValidator implements ValidatorsInterface
{
    public function __construct($request)
    {
        parent::__construct($request);
    }
    public function rules()
    {
        return[
            'country_id' => 'required',
            'name'=>'required|min:5|max:25'
        ];
    }
}

