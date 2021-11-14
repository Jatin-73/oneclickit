<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use stdClass;


class ProductController extends Controller
{
    public function productByCat(){

        $productObj1 = new stdClass();
        $productObj1->category_id = 1;
        $productObj1->category_name = 'laptop';
        $productObj1->product_id = 1;
        $productObj1->product_name = 'HP pavilion';
        $productObj1->product_cost = 25000;
    
        $productObj2 = new stdClass();
        $productObj2->category_id = 2;
        $productObj2->category_name = 'mobile';
        $productObj2->product_id = 2;
        $productObj2->product_name = 'Samsung galaxy';
        $productObj2->product_cost = 19000;
    
        $productObj3 = new stdClass();
        $productObj3->category_id = 1;
        $productObj3->category_name = 'laptop';
        $productObj3->product_id = 3;
        $productObj3->product_name = 'Acer';
        $productObj3->product_cost = 24000;
    
        $productObj4 = new stdClass();
        $productObj4->category_id = 1;
        $productObj4->category_name = 'laptop';
        $productObj4->product_id = 4;
        $productObj4->product_name = 'Dell';
        $productObj4->product_cost = 32000;
    
        $productObj5 = new stdClass();
        $productObj5->category_id = 2;
        $productObj5->category_name = 'mobile';
        $productObj5->product_id = 5;
        $productObj5->product_name = 'One Plus';
        $productObj5->product_cost = 29000;
    
        $productObj6 = new stdClass();
        $productObj6->category_id = 4;
        $productObj6->category_name = 'headphone';
        $productObj6->product_id = 6;
        $productObj6->product_name = 'Boult';
        $productObj6->product_cost = 12000;
    
        $products = [
            $productObj1, $productObj2, $productObj3, $productObj4, $productObj5, $productObj6 
        ];
    
        foreach($products as $product) {
            $ids[] = $product->category_id;
        }
        $catIds = array_unique($ids);
    
        $groupCategory = [];
        foreach ($catIds as $cat) {
            $productsData = [];
            foreach($products as $pro){
                if($pro->category_id === $cat){
                    $productsData[] = [
                        'product_id'   => $pro->product_id,
                        'product_name' => $pro->product_name,
                        'product_cost' => $pro->product_cost,
                    ];
                    $categoryName = $pro->category_name;
                }
            }
            $groupCategory[] = [
                'category_id'   => $cat,
                'category_name' => $categoryName,
                'products '     => $productsData,
            ];
        }
    
        return response()->json($groupCategory);
    }
}
