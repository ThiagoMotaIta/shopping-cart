<?php
namespace App\Http\Controllers;

use App\Http\Requests\CartDiscountRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Money\Money;
use Money\MoneyFormatter;
use Money\MoneyParser;

class CartsController extends Controller
{
    public function calculateDiscount(
        CartDiscountRequest $request,
        MoneyParser $moneyParser,
        MoneyFormatter $moneyFormatter
    ): JsonResponse {
        // Your logic goes here, use the code below just as a guidance.
        // You can do whatever you want with this code, even delete it.
        // Think about responsibilities, testing and code clarity.

        $subtotal = Money::BRL(0);
        $unitePricesArray = [];
        $discountArray = [];
        $checkQuantityArray = [];
        $check3_pay_2 = false;
        $strategyDiscountArray = [];
        $strategy = 'none';

        foreach ($request->get('products') as $product) {
            $unitPrice = $moneyParser->parse($product['unitPrice'], 'BRL');
            $amount = $unitPrice->multiply($product['quantity']);
            $subtotal = $subtotal->add($amount);
            array_push($unitePricesArray, $product['unitPrice']);

            // Check if there are 3 same products or multiples of 3
            // Example: 3 pay 2, 6 pay 4, 9 pay 6...
            for ($i = 3; $i <= $product['quantity']; $i++) {
                $rest = $i % 3;
                if (0 == $rest) {
                    array_push($checkQuantityArray, $product['unitPrice']);
                    $check3_pay_2 = true;
                }
            }
        }

        $discount = Money::BRL(0);

        $discount1 = 0.00;
        $discount2 = 0.00;
        $discount3 = 0.00;
        $discount4 = 0.00;
        $discount5 = 0.00;

        // if amount is greater than 2999.99 we give 15% OFF
        if ($moneyFormatter->format($subtotal) >= 3000.00) {
            $discount1 = ($moneyFormatter->format($subtotal) * 15) / 100;
            array_push($discountArray, $discount1);
            array_push($strategyDiscountArray, ['strategy' => '15%_discount_above_3000', 'discount' => $discount1]);
        }

        // If quantity is 3 or multeples of 3 we give discont to the third ones
        if ($check3_pay_2) {
            $discount2 = array_sum($checkQuantityArray);
            array_push($discountArray, $discount2);
            array_push($strategyDiscountArray, ['strategy' => 'discount_take_3_pay_2_multiple_times', 'discount' => $discount2]);
        }

        // Give 40% OFF to the lowest unit price
        $discount3 = (min($unitePricesArray) * 40) / 100;
        array_push($discountArray, $discount3);
        array_push($strategyDiscountArray, ['strategy' => '40%_discount_for_same_price', 'discount' => $discount3]);

        // Checking user API
        $response = Http::get('http://localhost/api/v1/user/'.$request->get('userEmail'));
        $jsonData = $response->json();

        if ('Success.' == $jsonData->data->message) {
            // If user is Employee we give 20% OFF
            if ($jsonData->data->isEmployee) {
                $discount4 = ($moneyFormatter->format($subtotal) * 20) / 100;
                array_push($discountArray, $discount4);
                array_push($strategyDiscountArray, ['strategy' => '20%_discount_for_employee', 'discount' => $discount4]);
            }
        } else {
            // If is NEW USER we give $25,00 OFF in case that amount is greater than $50,00
            if ($moneyFormatter->format($subtotal) > 50.00) {
                $discount5 = 0.25;
                array_push($discountArray, $discount5);
                array_push($strategyDiscountArray, ['strategy' => '$20.00_discount_for_new_customer', 'discount' => $discount5]);
            }
        }

        // Pick the biggest discount if we got more than 1 discount included
        $discount = Money::BRL((int)max($discountArray) * 100);

        // Pick the strategy of biggest discount
        foreach ($strategyDiscountArray as $strategyDisc) {
            if (Money::BRL((int)$strategyDisc['discount'] * 100) == $discount) {
                $strategy = $strategyDisc['strategy'];
            }
        }

        $total = $subtotal->subtract($discount);

        return new JsonResponse(
            [
                'message' => 'Success.',
                'data' => [
                    'subtotal' => $moneyFormatter->format($subtotal),
                    'discount' => $moneyFormatter->format($discount),
                    'total' => $moneyFormatter->format($total),
                    'strategy' => $strategy,
                ],
            ]
        );
    }
}
