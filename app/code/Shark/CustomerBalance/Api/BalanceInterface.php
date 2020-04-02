<?php

namespace Shark\CustomerBalance\Api;

/**
 * Interface BalanceInterface
 * @package Shark\CustomerBalance\Api
 */
interface BalanceInterface
{
    /**
     * Apply store credit for customer
     *
     * @param string $email
     * @param string $amount
     * @return mixed
     */
    public function apply($email, $amount);
}
