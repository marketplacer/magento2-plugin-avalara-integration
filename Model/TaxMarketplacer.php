<?php
declare(strict_types=1);

namespace Marketplacer\AvalaraIntegration\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Marketplacer\AvalaraIntegration\Api\Data\TaxMarketplacerInterface;

class TaxMarketplacer extends AbstractExtensibleModel implements TaxMarketplacerInterface
{

    /**
     * Get tax class name
     *
     * @return string
     */
    public function getTaxClassName(): string
    {
        return (string)$this->_getData(self::TAX_CLASS_NAME);
    }

    /**
     * Set tax class name
     *
     * @param string $taxClassName
     * @return $this
     */
    public function setTaxClassName(string $taxClassName): self
    {
        return $this->setData(self::TAX_CLASS_NAME, $taxClassName);
    }

    /**
     * Get tax code
     *
     * @return string
     */
    public function getTaxCode(): string
    {
        return (string)$this->_getData(self::TAX_CODE);
    }

    /**
     * Set tax code
     *
     * @param string $taxCode
     * @return $this
     */
    public function setTaxCode(string $taxCode): self
    {
        return $this->setData(self::TAX_CODE, $taxCode);
    }

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Marketplacer\AvalaraIntegration\Api\Data\TaxMarketplacerExtensionInterface|null
     */
    public function getExtensionAttributes()
    : ?\Marketplacer\AvalaraIntegration\Api\Data\TaxMarketplacerExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object
     *
     * @param \Marketplacer\AvalaraIntegration\Api\Data\TaxMarketplacerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Marketplacer\AvalaraIntegration\Api\Data\TaxMarketplacerExtensionInterface $extensionAttributes
    ): self {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
