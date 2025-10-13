<?php

namespace Illuminate\Support\Facades;

interface Auth
{
    /**
     * @return \App\User|false
     */
    public static function loginUsingId(mixed $id, bool $remember = false);

    /**
     * @return \App\User|false
     */
    public static function onceUsingId(mixed $id);

    /**
     * @return \App\User|null
     */
    public static function getUser();

    /**
     * @return \App\User
     */
    public static function authenticate();

    /**
     * @return \App\User|null
     */
    public static function user();

    /**
     * @return \App\User|null
     */
    public static function logoutOtherDevices(string $password);

    /**
     * @return \App\User
     */
    public static function getLastAttempted();
}