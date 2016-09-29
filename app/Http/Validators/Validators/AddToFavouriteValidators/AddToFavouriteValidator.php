<?php
/**
 * Created by PhpStorm.
 * User: waqas
 * Date: 4/4/2016
 * Time: 4:15 PM
 */

namespace App\Http\Validators\Validators\AddToFavouriteValidators;
use App\Http\Validators\Interfaces\ValidatorsInterface;

class AddToFavouriteValidator extends FavouriteValidator implements ValidatorsInterface
{
    public function __construct($request)
    {
        parent::__construct($request);
    }
    public function CustomValidationMessages()
    {
        return [

        ];
    }
    public function rules()
    {
        return[
            'propertyId'=>'required|unique:favourite_properties,property_id,NULL,id,user_id,' .session()->get('authUser')->id,
        ];
    }
}

