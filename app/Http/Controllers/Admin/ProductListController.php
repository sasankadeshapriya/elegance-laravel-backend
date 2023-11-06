<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductList;
use App\Models\ProductDetails;
use App\Models\Category;
use App\Models\Subcategory;
use Image;

class ProductListController extends Controller
{
    public function ProductListByRemark(Request $request){

        $remark = $request->remark;
        $productlist = ProductList::where('remark',$remark)->limit(8)->get();
        return $productlist;

    } // End Method

    public function ProductListByCategory(Request $request){

        $Category = $request->category;
        $productlist = ProductList::where('category',$Category)->get();
        return $productlist;

    }// End Method


    public function ProductListBySubCategory(Request $request){

        $Category = $request->category;
        $SubCategory = $request->subcategory;
        $productlist = ProductList::where('category',$Category)->where('subcategory',$SubCategory)->get();

        return $productlist;

    }// End Method



    public function ProductBySearch(Request $request){

        $key = $request->key;
        $productlist = ProductList::where('title','LIKE',"%{$key}%")->orWhere('brand','LIKE',"%{$key}%")->get();
        return $productlist;

    }// End Method


    public function SimilarProduct(Request $request){
        $subcategory = $request->subcategory;
        $productlist = ProductList::where('subcategory',$subcategory)->orderBy('id','desc')->limit(6)->get();
        return $productlist;

    }// End Method



    public function GetAllProduct(){

        $products = ProductList::latest()->paginate(10);
        return view('backend.product.product_all',compact('products'));

    } // End Method


    public function AddProduct(){

        $category = Category::orderBy('category_name','ASC')->get();
        $subcategory = Subcategory::orderBy('subcategory_name','ASC')->get();
        return view('backend.product.product_add',compact('category','subcategory'));

    } // End Method

    public function StoreProduct(Request $request){
        $request->validate([
            'product_code' => 'required',
        ],[
            'product_code.required' => 'Input Product Code'
        ]);

        $productData = [
            'title' => $request->title,
            'price' => $request->price,
            'special_price' => $request->special_price,
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'remark' => $request->remark,
            'brand' => $request->brand,
            'product_code' => $request->product_code,
            'quantity'=> $request->quantity,
        ];

        $image = $request->file('image');
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        Image::make($image)->resize(711, 960)->save('upload/product/' . $name_gen);
        $productData['image'] = 'http://127.0.0.1:8000/upload/product/' . $name_gen;

        // Check if the product with the same product_code exists
        $existingProduct = ProductList::where('product_code', $request->product_code)->first();

        if ($existingProduct) {
            // Update the existing product
            $existingProduct->update($productData);
            $product_id = $existingProduct->id;
        } else {
            // Insert a new product
            $product_id = ProductList::insertGetId($productData);
        }

        // Insert Into Product Details Table
        $productDetailsData = [
            'product_id' => $product_id,
            'short_description' => $request->short_description,
            'color' => $request->color,
            'size' => $request->size,
            'long_description' => $request->long_description,
            'quantity'=> $request->quantity,
            'product_code' => $request->product_code,
        ];

        $imageFields = ['image_one', 'image_two', 'image_three', 'image_four'];

        foreach ($imageFields as $imageField) {
            $image = $request->file($imageField);
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            Image::make($image)->resize(711, 960)->save('upload/productdetails/' . $name_gen);
            $productDetailsData[$imageField] = 'http://127.0.0.1:8000/upload/productdetails/' . $name_gen;
        }

        // Check if product details with the same product_code exists
        $existingProductDetails = ProductDetails::where('product_code', $request->product_code)->first();

        if ($existingProductDetails) {
            // Update the existing product details
            $existingProductDetails->update($productDetailsData);
        } else {
            // Insert a new product details record
            ProductDetails::insert($productDetailsData);
        }

        $notification = [
            'message' => 'Product Inserted Successfully',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.product')->with($notification);
    }

    public function EditProduct($id){

        $category = Category::orderBy('category_name','ASC')->get();
        $subcategory = Subcategory::orderBy('subcategory_name','ASC')->get();
        $product = ProductList::findOrFail($id);
        $details = ProductDetails::where('product_id',$id)->get();
        return view('backend.product.product_edit',compact('category','subcategory','product','details'));

    } // End Method


    public function DeleteProduct($id){

        $product = ProductList::find($id);

        if (!$product) {
            // Product not found, handle this case accordingly
            $notification = array(
                'message' => 'Product not found',
                'alert-type' => 'error'
            );

            return redirect()->route('all.product')->with($notification);
        }

        // Delete the product
        $product->delete();

        // Delete related records from the ProductDetails table
        ProductDetails::where('product_id', $id)->delete();

        $notification = array(
            'message' => 'Product deleted successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.product')->with($notification);

    }
}
