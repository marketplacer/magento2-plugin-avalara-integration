<?php
declare(strict_types=1);

namespace Marketplacer\AvalaraIntegration\Model\Services;

use ClassyLlama\AvaTax\Framework\Interaction\Address;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\ValidationException;
use ClassyLlama\AvaTax\Framework\Interaction\Tax;
use ClassyLlama\AvaTax\Helper\Rest\Config as RestConfig;
use ClassyLlama\AvaTax\Framework\Interaction\MetaData\MetaDataObject;
use ClassyLlama\AvaTax\Model\Logger\AvaTaxLogger;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Api\SellerRepositoryInterface;
use Marketplacer\SellerApi\Api\Data\ProductAttributeInterface;

class TaxRequestService
{
    /**
     * @var MetaDataObject
     */
    private MetaDataObject $metaDataObject;

    /**
     * @var array
     */
    private array $originAddress = [];

    /**
     * @param RestConfig $restConfig
     * @param AvaTaxLogger $avaTaxLogger
     * @param Address $address
     * @param MetaDataObjectFactory $metaDataObjectFactory
     * @param SellerRepositoryInterface $sellerRepository
     * @param SourceRepositoryInterface $sourceRepository
     */
    public function __construct(
        private RestConfig $restConfig,
        private AvaTaxLogger $avaTaxLogger,
        private Address $address,
        MetaDataObjectFactory $metaDataObjectFactory,
        private SellerRepositoryInterface $sellerRepository,
        private SourceRepositoryInterface $sourceRepository
    ) {
        $this->metaDataObject = $metaDataObjectFactory->create(['metaDataProperties' => Tax::$validFields]);
    }

    /**
     *  Add the "Shipping Origin" field to the request
     *
     * @param DataObject $request
     * @param \Magento\Quote\Model\Quote\Item[] $items
     * @return void
     * @throws ValidationException
     * @throws LocalizedException
     */
    public function addShippingOriginField(
        DataObject $request,
        array $items
    ): void {
        if (($seller = $this->getSellerFromProducts($items)) && $seller->getSourceCode()) {
            $originAddress = $this->address->getAddress($this->getOriginAddress($seller));
            $addresses = ($request->hasAddresses()) ? $request->getAddresses() : [];
            $addresses[ $this->restConfig->getAddrTypeFrom() ] = $originAddress;
            $request->setAddresses($addresses);

            try {
                $validatedData = $this->metaDataObject->validateData($request->getData());
                $request->setData($validatedData);
            } catch (ValidationException $e) {
                $this->avaTaxLogger->error('Error validating data: ' . $e->getMessage(), [
                    'data' => var_export($request->getData(), true)
                ]);
            }
        }
    }

    /**
     * To get the seller's address of origin
     *
     * @param SellerInterface $seller
     * @return array
     * @throws NoSuchEntityException
     */
    public function getOriginAddress(SellerInterface $seller): array
    {

        $source = $this->sourceRepository->get($seller->getSourceCode());

        if (!isset($this->originAddress[$source->getSourceCode()])) {
            $data = [
                'line_1' => $source->getStreet(),
                'line_2' => '',
                'city' => $source->getCity(),
                'region_id' => $source->getRegionId(),
                'postal_code' => $source->getPostcode(),
                'country' => $source->getCountryId(),
            ];

            $this->originAddress[$source->getSourceCode()] = $data;
        }

        return $this->originAddress[$source->getSourceCode()];
    }

    /**
     * Get the seller who owns the goods
     *
     * @param \Magento\Quote\Model\Quote\Item[] $items
     * @return ?SellerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSellerFromProducts(array $items): ?SellerInterface
    {
        $sellers = [];
        foreach ($items as $item) {

            $sellerAttribute = $item->getProduct()
                ->getCustomAttribute(ProductAttributeInterface::SELLER_ATTRIBUTE_CODE);
            if ($sellerAttribute !== null) {
                $sellers[] = $sellerAttribute->getValue();
            }
        }
        if ($sellers) {
            $sellers = array_unique($sellers);

            if (count($sellers) > 1) {
                throw new LocalizedException(__('Tax calculation is possible only for one seller.'));
            }

            return $this->sellerRepository->getById($sellers[0]);
        }

        return null;
    }

    /**
     * Get the cart item separated by the sellers they belong to
     *
     * @param \Magento\Quote\Model\Quote\Item[] $items
     * @return array[]
     */
    public function getCartItemsBySeller(array $items): array
    {
        $result = [
            'default' => []
        ];
        foreach ($items as $item) {
            $sellerAttribute = $item->getProduct()
                ->getCustomAttribute(ProductAttributeInterface::SELLER_ATTRIBUTE_CODE);
            if ($sellerAttribute !== null) {
                $result[$sellerAttribute->getValue()][] = $item;
            } else {
                $result['default'][] = $item;
            }
        }
        return $result;
    }
}
