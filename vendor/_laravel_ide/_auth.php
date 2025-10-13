<?php

namespace Illuminate\Contracts\Auth;

interface Guard
{
    /**
     * @return \App\User|null
     */
    public function user();
}