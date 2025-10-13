<?php

namespace Illuminate\Http;

interface Request
{
    /**
     * @return \App\User|null
     */
    public function user($guard = null);
}