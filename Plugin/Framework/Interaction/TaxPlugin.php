<?php
declare(strict_types=1);

namespace Marketplacer\AvalaraIntegration\Plugin\Framework\Interaction;

use Avalara\AvaTax\Framework\Interaction\Tax;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Marketplacer\AvalaraIntegration\Model\Services\TaxRequestService;

class TaxPlugin
{

    /**
     * @param TaxRequestService $taxRequestService
     */
    public function __construct(private TaxRequestService $taxRequestService)
    {
    }

    /**
     *
     * @param Tax $subject
     * @param DataObject|null $request
     * @param Quote $quote
     * @param QuoteDetailsInterface $taxQuoteDetails
     * @param ShippingAssignmentInterface $shippingAssignment
     * @return null|DataObject
     */
    public function afterGetTaxRequestForQuote(
        Tax                         $subject,
        ?DataObject                 $request,
        Quote                       $quote,
        QuoteDetailsInterface       $taxQuoteDetails,
        ShippingAssignmentInterface $shippingAssignment
    ) {
        if ($request !== null) {
            $this->taxRequestService->addShippingOriginField($request, $shippingAssignment->getItems());
        }

        return $request;
    }
}
