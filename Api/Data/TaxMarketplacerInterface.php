<?php
declare(strict_types=1);

namespace Marketplacer\AvalaraIntegration\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface TaxMarketplacerInterface extends ExtensibleDataInterface
{
    public const TAX_CLASS_NAME = 'tax_class_name';
    public const TAX_CODE = 'tax_code';

    /**
     * Get tax class name
     *
     * @return string
     */
    public function getTaxClassName(): string;

    /**
     * Set tax class name
     *
     * @param string $taxClassName
     * @return $this
     */
    public function setTaxClassName(string $taxClassName): self;

    /**
     * Get tax code
     *
     * @return string
     */
    public function getTaxCode(): string;

    /**
     * Set tax code
     *
     * @param string $taxCode
     * @return $this
     */
    public function setTaxCode(string $taxCode): self;

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Marketplacer\AvalaraIntegration\Api\Data\TaxMarketplacerExtensionInterface|null
     */
    public function getExtensionAttributes()
    : ?\Marketplacer\AvalaraIntegration\Api\Data\TaxMarketplacerExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Marketplacer\AvalaraIntegration\Api\Data\TaxMarketplacerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Marketplacer\AvalaraIntegration\Api\Data\TaxMarketplacerExtensionInterface $extensionAttributes
    ): self;
}
