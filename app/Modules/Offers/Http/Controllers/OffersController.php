<?php

namespace App\Modules\Offers\Http\Controllers;

use App\Core\MyBaseApiController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Modules\Offers\Models\Offer;
use App\Modules\Offers\Transformers\OffersListResource;
use App\Modules\Offers\Transformers\OfferWithSellerResource;
use App\Modules\Offers\Transformers\OfferWithProductsResource;
use App\Modules\Cart\Services\CartService;

class OffersController extends MyBaseApiController
{

    public function getOffers()
    {
      try{

        $offers = Offer::validOffer()->descriptionByHeaderLang()->get();
        return $this->successResponseWithData(OffersListResource::collection($offers));

        }catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }//getOffers

    public function getSellersByOfferId(Request $request)
    {
      try{

        $this->validateApiRequest(
            ['offer_id'],
            [
                'offer_id' => 'exists:offers,id',
            ]
        );

        $offer = Offer::validOffer()->descriptionByHeaderLang()->with('sellers')->find($request->offer_id);
        if(!$offer){
          return $this->errorResponse(trans('offers.api.not_valid'));
        }

        $offerTrans = new OfferWithSellerResource($offer);
        return $this->successResponseWithData($offerTrans);

        }catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }//getSellersByOfferId

    public function getSellerProductsByOfferId(Request $request)
    {
      try{

        $this->validateApiRequest(
            ['offer_id', 'store_id'],
            [
                'offer_id' => 'exists:offers,id',
                'store_id' => 'exists:admin_store,id',
            ]
        );

        $offer = Offer::validOffer()->descriptionByHeaderLang()
                      ->withProductsBySeller($request->store_id)
                      ->with('sellers')->find($request->offer_id);

        if(!$offer){
          return $this->errorResponse(trans('offers.api.not_valid'));
        }
        if(!$this->offerIsValidForSeller($offer, $request->store_id)){
          return $this->errorResponse(trans('offers.api.not_valid_for_seller'));
        }

        $offerTrans = new OfferWithProductsResource($offer);
        return $this->successResponseWithData($offerTrans);

        }catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }//getSellersByOfferId

    private function offerIsValidForSeller($offer, $store_id)
    {
      if($offer->apply_to == config('offers.contants.OFFER_APPLY_TO.SELLERS') || $offer->apply_to == config('offers.contants.OFFER_APPLY_TO.PRODUCTS')){
        return $offer->sellers->where('seller_id', $store_id)->count() > 0;
      }
      return true;
    }//offerIsValidForSeller

    public function getOfferForSeller(Request $request)
    {
      try{

        $this->validateApiRequest(
            ['store_id'],
            [
                'store_id' => 'exists:admin_store,id',
            ]
        );

        $offers = Offer::validOffer()
                        ->onlyForAllOffers()
                        ->descriptionByHeaderLang()
                        ->get();

        $offers_sellers = Offer::validOffer()
                        ->offerValidForSeller($request->store_id)
                        ->descriptionByHeaderLang()
                        ->get();

        return $this->successResponseWithData(OffersListResource::collection($offers->merge($offers_sellers)));

        }catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }//getOfferForSeller

    public function validateOffer(Request $request)
    {
      try{

        $this->validateApiRequest(
            [],
            [
                'offer_id' => 'exists:offers,code',
            ]
        );

        $offer = Offer::validOffer()->descriptionByHeaderLang()->where('code',$request->offer_id)->first();

        if(!$offer){
          return $this->errorResponse(trans('offers.api.not_valid'));
        }

        $tranferOffer = new OffersListResource($offer);
        return $this->successResponseWithData( $tranferOffer, trans('offers.api.valid_offer'));
        //return $this->successResponseWithData([]);

        }catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }//validateOffer
}
