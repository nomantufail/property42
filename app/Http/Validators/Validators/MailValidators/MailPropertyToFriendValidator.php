<?php
/**
 * Created by PhpStorm.
 * User: waqas
 * Date: 4/4/2016
 * Time: 4:15 PM
 */

namespace App\Http\Validators\Validators\MailValidators;


use App\Http\Validators\Interfaces\ValidatorsInterface;

class MailPropertyToFriendValidator extends MailValidator implements ValidatorsInterface
{
    public function __construct($request)
    {
        parent::__construct($request);
    }
    public function rules()
    {
        return[
            'to' => 'required|min:5|email|max:255',
            'message'=>'required|min:5|max:300'
        ];
    }
}

