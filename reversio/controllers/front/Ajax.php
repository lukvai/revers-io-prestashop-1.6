<?php
/**
 *Copyright (c) 2019 Revers.io
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author revers.io
 * @copyright Copyright (c) permanent, Revers.io
 * @license   Revers.io
 * @see       /LICENSE
 */

class ReversioAjaxModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        parent::postProcess();

        if (!$this->isTokenValid()) {
            die();
        }

        /** @var \ReversIO\Services\Orders\OrderImportService $orderImportService */
        /** @var \ReversIO\Repository\OrderRepository $orderRepository */
        /** @var \ReversIO\Services\APIConnect\ReversIOApi $reversIoApiConnect */
        $orderImportService = $this->module->getContainer()->get('orderImportService');
        $orderRepository = $this->module->getContainer()->get('orderRepository');
        $reversIoApiConnect = $this->module->getContainer()->get('reversIoApiConnect');

        $orderRepository->deleteUnsuccessfullyOrders();
        $orderId = Tools::getValue('orderId');
        $orderReference = $orderRepository->getOrderReferenceById($orderId);

        try {
            $reversIoOrderImportResponse = $orderImportService->importOrder($orderId);
            if ($reversIoOrderImportResponse->isSuccess()) {
                $reversIoApiConnect->retrieveOrderUrl($orderReference);
                $reversIoOrderImportResponse->setRedirectUrl($orderRepository->getOrderUrlById($orderId));
            }
            die(json_encode($reversIoOrderImportResponse));
        } catch (Exception $e) {
            die(json_encode($reversIoOrderImportResponse));
        }
    }
}
