<?php
declare(strict_types=1);

namespace Marketplacer\AvalaraIntegration\Plugin\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Tax\Api\Data\TaxClassInterfaceFactory;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\ResourceModel\TaxClass;
use Marketplacer\AvalaraIntegration\Api\Data\TaxMarketplacerInterface;

class Repository
{
    /**
     * @param TaxClassInterfaceFactory $taxClassDataObjectFactory
     * @param TaxClassRepositoryInterface $taxClassRepository
     * @param TaxClass $taxClassResource
     */
    public function __construct(
        private \Magento\Tax\Api\Data\TaxClassInterfaceFactory $taxClassDataObjectFactory,
        private \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository,
        private TaxClass $taxClassResource
    ) {
    }

    /**
     *
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $product
     * @param bool $saveOptions
     * @return array
     * @throws InputException
     * @throws LocalizedException
     */
    public function beforeSave(
        ProductRepositoryInterface $subject,
        ProductInterface $product,
        $saveOptions = false
    ): array {
        $extensionAttributes = $product->getExtensionAttributes();

        /** @var TaxMarketplacerInterface $taxMarketplacer */
        if ($extensionAttributes !== null && $taxMarketplacer = $extensionAttributes->getTaxMarketplacer()) {
            $taxCode = trim($taxMarketplacer->getTaxCode());
            if ($taxCode) {
                $className = trim($taxMarketplacer->getTaxClassName()) ?: $taxCode;

                $taxClass = $this->taxClassDataObjectFactory->create();

                $this->taxClassResource->load($taxClass, $taxCode, 'avatax_code');

                if (!$taxClass->getClassId()) {
                    $taxClass
                        ->setClassType(ClassModel::TAX_CLASS_TYPE_PRODUCT)
                        ->setAvataxCode($taxCode);
                }

                $taxClass->setClassName($className);

                $this->taxClassRepository->save($taxClass);

                $product->setTaxClassId($taxClass->getClassId());
            }
        }

        return [$product, $saveOptions];
    }
}
