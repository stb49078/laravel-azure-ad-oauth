<?php

namespace Metrogistics\AzureSocialite;

class UserFactory
{
    protected $config;
    protected static $user_create_callback;
    protected static $user_login_callback;

    public function __construct()
    {
        $this->config = config('oauth-azure');
    }

    public function convertAzureUser($azure_user, $user)
    {
        $user_class = config('oauth-azure.user_class');
        $user_map = config('oauth-azure.user_map');
        $id_field = config('oauth-azure.user_id_field');

        if(!$user) {
            $user = new $user_class;
        }

        $user->$id_field = $azure_user->id;

        foreach($user_map as $azure_field => $user_field){
            $user->$user_field = $azure_user->$azure_field;
        }

        $callback = static::$user_create_callback;

        if($callback && is_callable($callback)){
            $callback($user, $azure_user);
        }

        $user->save();

        return $user;
    }
    
    public function userLogin($azure_user, $user) {
	    $callback = static::$user_login_callback;

	    if($callback && is_callable($callback)){
		    $callback($user, $azure_user);
	    }
    }

    public static function userCreateCallback($callback)
    {
        if(! is_callable($callback)){
            throw new \Exception("Must provide a callable.");
        }

        static::$user_create_callback = $callback;
    }

    public static function userLoginCallback($callback)
    {
        if(! is_callable($callback)){
            throw new \Exception("Must provide a callable.");
        }

        static::$user_login_callback = $callback;
    }
}
