<?php

use Expressly\Entity\Invoice;
use Expressly\Entity\Order;
use Expressly\Entity\Route;
use Expressly\Exception\ExceptionFormatter;
use Expressly\Exception\GenericException;
use Expressly\Expressly\AbstractController;
use Expressly\Presenter\BatchCustomerPresenter;
use Expressly\Presenter\BatchInvoicePresenter;

class Expressly_Expressly_BatchController extends AbstractController
{
    public function invoiceAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $route = $this->resolver->process($_SERVER['REQUEST_URI']);

        if ($route instanceof Route) {

            $json = file_get_contents('php://input');
            $json = json_decode($json);
            $invoices = array();

            try {
                if (!property_exists($json, 'customers')) {
                    throw new GenericException('Invalid JSON input');
                }

                $orderModel = Mage::getModel('sales/order');

                foreach ($json->customers as $customer) {
                    if (!property_exists($customer, 'email')) {
                        continue;
                    }

                    $mageOrders = $orderModel
                        ->getCollection()
                        ->addFieldToFilter('customer_email', $customer->email)
                        ->setOrder('created_at', 'desc');

                    $invoice = new Invoice();
                    $invoice->setEmail($customer->email);
                    foreach ($mageOrders as $mageOrder) {
                        $total = $mageOrder->getData('base_grand_total');
                        $tax = $mageOrder->getData('base_tax_amount');

                        $order = new Order();
                        $order
                            ->setId($mageOrder->getData('increment_id'))
                            ->setDate(new \DateTime($mageOrder->getData('created_at')))
                            ->setCurrency($mageOrder->getData('base_currency_code'))
                            ->setTotal((double)$total - (double)$tax, (double)$tax)
                            ->setItemCount((int)$mageOrder->getData('total_qty_ordered'))
                            ->setCoupon($mageOrder->getData('coupon_code'));

                        $invoice->addOrder($order);
                    }

                    $invoices[] = $invoice;
                }
            } catch (\Exception $e) {
                $this->logger->error(ExceptionFormatter::format($e));
            }

            $presenter = new BatchInvoicePresenter($invoices);
            $this->getResponse()->setBody(json_encode($presenter->toArray()));
        } else {
            $this->getResponse()->setHttpResponseCode(401);
        }
    }

    public function customerAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $route = $this->resolver->process($_SERVER['REQUEST_URI']);

        if ($route instanceof Route) {
            $json = file_get_contents('php://input');
            $json = json_decode($json);
            $customers = array();

            try {
                if (!property_exists($json, 'emails')) {
                    throw new GenericException('Invalid JSON input');
                }

                $customerModel = Mage::getModel('customer/customer');
                $customerModel->setWebsiteId(Mage::app()->getWebsite()->getId());

                foreach ($json->emails as $email) {
                    $customerModel->loadByEmail($email);

                    if ($customerModel->getId()) {
                        if ($customerModel->getData('is_active')) {
                            $customers['existing'][] = $email;
                            continue;
                        }

                        $customers['pending'][] = $email;
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error(ExceptionFormatter::format($e));
            }

            $presenter = new BatchCustomerPresenter($customers);
            $this->getResponse()->setBody(json_encode($presenter->toArray()));
        } else {
            $this->getResponse()->setHttpResponseCode(401);
        }
    }
}
