<?php
declare(strict_types=1);

namespace Marketplacer\AvalaraIntegration\Plugin\Tax\Sales\Total\Quote;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
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
     * @param \Avalara\AvaTax\Model\Tax\Sales\Total\Quote\Tax $quoteTax
     * @param callable $proceed
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return \Avalara\AvaTax\Model\Tax\Sales\Total\Quote\Tax
     */
    public function aroundCollect(
        \Avalara\AvaTax\Model\Tax\Sales\Total\Quote\Tax $quoteTax,
        callable                                            $proceed,
        Quote                                               $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        $items = $shippingAssignment->getItems();
        if (!$items) {
            return $proceed($quote, $shippingAssignment, $total);
        }
        $cartItems = $this->taxRequestService->getCartItemsBySeller($shippingAssignment->getItems());

        $subtotal = $baseSubtotal = 0;
        $discountTaxCompensation = $baseDiscountTaxCompensation = 0;
        $tax = $baseTax = 0;
        $subtotalInclTax = $baseSubtotalInclTax = 0;
        $appliedTaxes = [];
        foreach ($cartItems as $sellerItems) {
            if ($sellerItems) {
                $shippingAssignment->setItems($sellerItems);
                $proceed($quote, $shippingAssignment, $total);

                $subtotal += $total->getTotalAmount('subtotal');
                $baseSubtotal += $total->getBaseTotalAmount('subtotal');
                $tax += $total->getTotalAmount('tax');
                $baseTax += $total->getBaseTotalAmount('tax');
                $discountTaxCompensation += $total->getTotalAmount('discount_tax_compensation');
                $baseDiscountTaxCompensation += $total->getBaseTotalAmount('discount_tax_compensation');

                $subtotalInclTax += $total->getSubtotalInclTax();
                $baseSubtotalInclTax += $total->getBaseSubtotalTotalInclTax();
                $appliedTaxes[] = $total->getItemsAppliedTaxes();
            }
        }
        $total->setItemsAppliedTaxes(array_merge([], ...$appliedTaxes));

        $total->setTotalAmount('subtotal', $subtotal);
        $total->setBaseTotalAmount('subtotal', $baseSubtotal);
        $total->setTotalAmount('tax', $tax);
        $total->setBaseTotalAmount('tax', $baseTax);
        $total->setTotalAmount('discount_tax_compensation', $discountTaxCompensation);
        $total->setBaseTotalAmount('discount_tax_compensation', $baseDiscountTaxCompensation);

        $total->setSubtotalInclTax($subtotalInclTax);
        $total->setBaseSubtotalTotalInclTax($baseSubtotalInclTax);
        $total->setBaseSubtotalInclTax($baseSubtotalInclTax);
        $address = $shippingAssignment->getShipping()->getAddress();
        $address->setBaseTaxAmount($baseTax);
        $address->setBaseSubtotalTotalInclTax($baseSubtotalInclTax);
        $address->setSubtotal($total->getSubtotal());
        $address->setBaseSubtotal($total->getBaseSubtotal());

        $shippingAssignment->setItems($items);
        return $quoteTax;
    }
}
