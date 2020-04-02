<?php

namespace Shark\CustomerBalance\Module;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CurrencyFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Balance
 * @package Shark\CustomerBalance\Module
 */
class Balance implements \Shark\CustomerBalance\Api\BalanceInterface
{
    /**
     * UD Dollar currency code
     */
    const USD_CURRENCY_CODE = 'USD';

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Balance constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->logger = $logger;
    }

    /**
     * Apply store credit for customer
     *
     * @param string $email
     * @param string $amount
     * @return mixed|string
     */
    public function apply($email, $amount)
    {
        if (!empty($email) && !empty($amount)) {
            try {
                $customer = $this->customerRepository->get($email);
                $amount = $this->getBaseCurrencyFromUSDollar($amount);
                $customer->setCustomAttribute('balance_amount', $amount);
                $this->customerRepository->save($customer);

                return "Store Credit updated successfully!";
            } catch (\Exception $exception) {
                $this->logger->error($exception);

                return "ERROR: Customer with email '$email' doesn't exist. Please make sure that proper email was provided";
            }
        }
    }

    /**
     * Get amount in base currency from US Dollar
     *
     * @param $amount
     * @return float|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getBaseCurrencyFromUSDollar($amount)
    {
        $baseCurrency = $this->storeManager->getStore()->getBaseCurrency()->getCode();
        if (self::USD_CURRENCY_CODE !== $baseCurrency) {
            $rate = $this->currencyFactory->create()->load(self::USD_CURRENCY_CODE)->getAnyRate($baseCurrency);
            return $amount * $rate;
        }

        return $amount;
    }
}
